<?php
session_start();
include '../db_connect.php';

// ── Fetch list of users for the “User” dropdown ──
$users = [];
$userRes = $mysqli->query("SELECT user_id, name FROM users ORDER BY name");
while ($u = $userRes->fetch_assoc()) {
    $users[] = $u;
}

// ── Filter variables ──
$from_date   = $_GET['from_date']   ?? '';
$to_date     = $_GET['to_date']     ?? '';
$search_text = trim($_GET['search'] ?? '');
$filter_user = $_GET['user_id']     ?? '';
$limit       = 10;
$page        = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset      = ($page - 1) * $limit;

// ── Build base query with LEFT JOINs ──
$query = "
  SELECT 
    t.transaction_id,
    u.name             AS user_name,
    u.user_image,
    p.product_name,
    c.category_name,
    s.storage_area_name,
    t.transaction_type,
    t.quantity_changed,
    t.transaction_date,
    t.description,
    IFNULL(ps.pieces_per_packet,1) AS pieces_per_packet
  FROM transactions t
  JOIN users u   ON t.user_id = u.user_id
  LEFT JOIN products p  ON t.product_id = p.product_id
  LEFT JOIN category c  ON p.category_id = c.category_id
  LEFT JOIN storage_areas s ON t.storage_area_id = s.storage_area_id
  LEFT JOIN product_stock ps 
    ON t.product_id = ps.product_id 
   AND t.storage_area_id = ps.storage_area_id
";

// ── Dynamic WHERE clauses ──
$conditions = [];
$params     = [];
$types      = '';

if ($from_date) {
    $conditions[] = "DATE(t.transaction_date) >= ?";
    $params[]     = $from_date;
    $types       .= 's';
}
if ($to_date) {
    $conditions[] = "DATE(t.transaction_date) <= ?";
    $params[]     = $to_date;
    $types       .= 's';
}
if ($filter_user) {
    $conditions[] = "t.user_id = ?";
    $params[]     = $filter_user;
    $types       .= 'i';
}
if ($search_text !== '') {
    // search across product_name, description, user_name
    $conditions[] = "(p.product_name LIKE ? OR t.description LIKE ? OR u.name LIKE ?)";
    $like         = "%{$search_text}%";
    $params[]     = $like;
    $params[]     = $like;
    $params[]     = $like;
    $types       .= 'sss';
}

if ($conditions) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

// For pagination: get total count
$count_query = "SELECT COUNT(*) as total FROM transactions t ";
if ($conditions) {
    $count_query .= ' WHERE ' . implode(' AND ', $conditions);
}
$count_stmt = $mysqli->prepare($count_query);
if ($count_stmt && $params) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_items = $count_result ? $count_result->fetch_assoc()['total'] : 0;
$total_pages = $total_items > 0 ? ceil($total_items / $limit) : 1;
$count_stmt->close();

$query .= " ORDER BY t.transaction_date DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types   .= 'ii';

// ── Prepare & execute ──
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    die("Prepare failed: ({$mysqli->errno}) {$mysqli->error}");
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard - SB Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
 
    </head>

<body class="sb-nav-fixed">
  <?php include 'navbar.php'; ?>
  <div id="layoutSidenav_content">
    <main class="container-fluid">
      <h2 class="mt-3 mb-4 text-center">Transaction Records</h2>

      <!-- Filters Form -->
      <form method="GET" class="mb-4">
        <div class="row gx-3">
          <div class="col-md-2">
            <label class="form-label">From Date</label>
            <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($from_date) ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">To Date</label>
            <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($to_date) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">By User</label>
            <select name="user_id" class="form-select">
              <option value="">All Users</option>
              <?php foreach ($users as $u): ?>
                <option value="<?= $u['user_id'] ?>"
                  <?= $filter_user == $u['user_id'] ? 'selected':'' ?>>
                  <?= htmlspecialchars($u['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Product, desc, user..." value="<?= htmlspecialchars($search_text) ?>">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">Filter</button>
            <a href="?" class="btn btn-secondary ms-2">Clear</a>
          </div>
        </div>
      </form>

      <!-- Transactions Table -->
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Image</th>
              <th>Product & Details</th>
              <th>Stock</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows): ?>
              <?php while ($row = $result->fetch_assoc()):
                // Icon logic
                if ($row['transaction_type']==='Add')        $ic='fa-arrow-up text-success';
                elseif($row['transaction_type']==='Subtract') $ic='fa-arrow-down text-danger';
                elseif ($row['transaction_type'] === 'Edit') $ic = 'fa-edit text-warning';
                elseif($row['transaction_type']==='Delete')   $ic='fa-trash text-muted';
                elseif(strpos($row['description'],'New product')===0) $ic='fa-plus text-primary';
                else                                           $ic='fa-question-circle text-secondary';
                // Qty
                $boxes=intdiv($row['quantity_changed'],$row['pieces_per_packet']);
                $pcs  =$row['quantity_changed']%$row['pieces_per_packet'];
              ?>
              <tr>
                <td>
                  <img src="../uploads/<?=htmlspecialchars($row['user_image'])?>"
                       class="rounded-circle" style="width:50px;height:50px;">
                </td>
                <td>
                  <!-- Delete vs. others -->
                  <?php if($row['transaction_type']==='Delete'): ?>
                    <strong><?=htmlspecialchars($row['description'])?></strong>
                  <?php else: ?>
                    <strong><?=htmlspecialchars($row['product_name'])?></strong><br>
                    <small><?=htmlspecialchars($row['category_name'])?></small>
                  <?php endif; ?>
                  <br>
                  <i class="fas <?=$ic?>"></i>
                  <?=htmlspecialchars($row['user_name'])?>
                  <small class="text-muted"><?=htmlspecialchars($row['transaction_date'])?></small>
                  <br>
                  <?php if(!empty($row['storage_area_name'])): ?>
                    <small class="text-primary">
                      Storage: <?=htmlspecialchars($row['storage_area_name'])?>
                    </small> ·
                  <?php endif; ?>
                  <small class="fst-italic text-secondary">
                    <?=htmlspecialchars($row['description'])?>
                  </small>
                </td>
                <td>
                  <span class="badge bg-primary"><?=$boxes?> Box</span><br>
                  <span class="badge bg-secondary"><?=$pcs?> Pc</span>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="3" class="text-center">No transactions found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
        <!-- Pagination controls -->
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center mt-3">
            <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="<?= htmlspecialchars(preg_replace('/([&?])page=\\d+/', '$1', $_SERVER['REQUEST_URI'])) . (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') . 'page=' . ($page - 1) ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo; Prev</span>
                </a>
              </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= htmlspecialchars(preg_replace('/([&?])page=\\d+/', '$1', $_SERVER['REQUEST_URI'])) . (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') . 'page=' . $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
              <li class="page-item">
                <a class="page-link" href="<?= htmlspecialchars(preg_replace('/([&?])page=\\d+/', '$1', $_SERVER['REQUEST_URI'])) . (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') . 'page=' . ($page + 1) ?>" aria-label="Next">
                  <span aria-hidden="true">Next &raquo;</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
        <!-- End Pagination controls -->
      </div>
    </main>
  </div>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>

</body>
</html>
