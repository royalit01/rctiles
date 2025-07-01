<?php
if(session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
require_once '../db_connect.php';

// Handle assignment POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_order'])) {
    $orderId = (int)$_POST['order_id'];
    $userId  = (int)$_POST['delivery_user_id'];

    // Validate order exists and is approved
    $valid = $mysqli->query("SELECT 1 FROM orders o JOIN pending_orders po ON po.order_id=o.order_id AND po.approved=1 WHERE o.order_id=$orderId LIMIT 1")->num_rows;
    $role  = $mysqli->query("SELECT role_id FROM users WHERE user_id=$userId AND role_id=(SELECT role_id FROM roles WHERE role_name='Delivery' LIMIT 1)")->num_rows;
    $exists = $mysqli->query("SELECT 1 FROM delivery_orders WHERE order_id=$orderId")->num_rows;

    if (!$valid || !$role) {
        $error = "Invalid order or user.";
    } elseif ($exists) {
        $error = "This order has already been assigned for delivery.";
    } else {
        // Get rent and discounted amount (fallback to total_amount if discounted is NULL)
        $ord = $mysqli->query("
            SELECT 
                COALESCE(o.final_amount, o.total_amount) AS discounted_amount,
                COALESCE(o.rent_amount, 0) AS transport_rent
            FROM orders o
            WHERE o.order_id = $orderId
        ")->fetch_assoc();

        $rent = (float)$ord['transport_rent'];
        $amt  = (float)$ord['discounted_amount'];
        
        $remaining = $amt + $rent;

        // Insert delivery record
        $stmt = $mysqli->prepare("INSERT INTO delivery_orders (order_id, delivery_user_id, rent, amount_remaining) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iidd', $orderId, $userId, $rent, $remaining);
        $stmt->execute();

        $deliveryId = $mysqli->insert_id;

        // Insert delivery items from pending_orders
        $items = $mysqli->query("SELECT product_id, quantity FROM pending_orders WHERE order_id = $orderId");
        while ($row = $items->fetch_assoc()) {
            $productId = (int)$row['product_id'];
            $qty       = (int)$row['quantity'];
            $mysqli->query("INSERT INTO delivery_items (delivery_id, product_id, qty_ordered) VALUES ($deliveryId, $productId, $qty)");
        }

        $success = "Order #$orderId assigned to delivery user $userId.";
    }
}

// Fetch delivery users
$driversRes = $mysqli->query("SELECT user_id, name FROM users WHERE role_id=(SELECT role_id FROM roles WHERE role_name='Delivery' LIMIT 1) ORDER BY name");
$drivers = $driversRes->fetch_all(MYSQLI_ASSOC);

// Pagination setup
$limit = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filters
$where = ["po.approved = 1"];
$params = [];
$types  = '';
if (!empty($_GET['search'])) {
    $where[] = "c.name LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
    $types   .= 's';
}

// Count total for pagination
$count_sql = "SELECT COUNT(DISTINCT o.order_id) as total FROM orders o JOIN pending_orders po ON po.order_id = o.order_id JOIN customers c ON c.customer_id = o.customer_id WHERE " . implode(' AND ', $where);
$count_stmt = $mysqli->prepare($count_sql);
if ($types) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result ? $count_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT o.order_id, c.name customer, c.phone_no,  o.rent_amount As transport_rent,
               o.final_amount AS discounted_amount,
               IFNULL(d.delivery_id,0) AS delivery_id,
               d.delivery_user_id, d.status, d.amount_paid, d.amount_remaining
                     FROM orders o
        JOIN customers c ON c.customer_id = o.customer_id
        JOIN pending_orders po ON po.order_id = o.order_id
        LEFT JOIN delivery_orders d ON d.order_id = o.order_id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY o.order_id
        ORDER BY o.order_date DESC
        LIMIT $limit OFFSET $offset";

$stmt = $mysqli->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
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
        <title>Admin Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"/>                

        <style>
        /* Base font slightly larger for readability */
        body{font-size:1.05rem;}

      @media (max-width: 575.98px) {
    /* card-style rows with headers for each data cell */
    table.table thead { display: none; }
    table.table tbody tr {
        display: block;
        margin-bottom: 1.5rem;
        border: 1px solid #dee2e6;
        border-radius: .5rem;
        padding: .75rem;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    table.table tbody tr td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: .5rem .75rem;
        font-size: 0.95rem;
        border: none;
        flex-wrap: wrap;
        text-align: right; /* Align value to right */
    }
    table.table tbody tr td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #495057;
        margin-right: 1rem;
        min-width: 120px;
        text-align: left;
        flex: 1 1 50%;
    }
    table.table tbody tr td[data-label="Actions"] {
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        text-align: left;
        flex-shrink: 2; /* Prevent shrinking of Actions column */
    }
    table.table tbody tr td[data-label="Actions"] .btn {
        width: 48%;
        min-width: 90px;
        margin-bottom: 0;
        margin-top: 0;
    }
    table.table tbody tr td[data-label="Actions"] .btn-outline-info {
        margin-left: auto;
        margin-right: 0;
    }
    table.table tbody tr td[data-label="Actions"] .btn-outline-primary {
        margin-right: auto;
        margin-left: 0;
    }
    table.table tbody tr td[data-label="Delivery Person"] form {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }
    table.table tbody tr td[data-label="Delivery Person"] select {
        width: 100% !important;
        margin-right: 0 !important;
    }
    table.table tbody tr td[data-label="Status"] {
        align-items: center;
        justify-content: space-between;
        text-align: right;
    }
    table.table tbody tr td[data-label="Status"] .badge,
    table.table tbody tr td[data-label="Status"] small {
        display: inline-block;
        margin-left: 0.5rem;
        margin-bottom: 0;
        vertical-align: middle;
    }
}
@media (min-width: 576px) {
    /* Reset mobile styles for larger screens */
    table.table thead { display: table-header-group; }
    table.table tbody tr {
        display: table-row;
        margin-bottom: 0;
        border: none;
        border-radius: 0;
        padding: 0;
        background: inherit;
        box-shadow: none;
    }
    table.table tbody tr td {
        display: table-cell;
        justify-content: unset;
        align-items: unset;
        width: unset;
        padding: .5rem;
        font-size: 1.05rem;
        border: 1px solid #dee2e6;
        flex-wrap: unset;
    }
    table.table tbody tr td[data-label="Actions"] {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
        text-align: right;
        flex-shrink: 0; /* Prevent shrinking of Actions column */
    }
    table.table tbody tr td[data-label="Actions"] .btn {
        width: auto;
        margin-top: 0;
        min-width: 90px;
    }
    table.table tbody tr td[data-label="Actions"] .btn-outline-primary {
        margin-right: auto;
        margin-left: 0;
    }
    table.table tbody tr td[data-label="Actions"] .btn-outline-info {
        margin-left: auto;
        margin-right: 0;
    }
    table.table tbody tr td[data-label="Delivery Person"] form {
        display: flex;
        flex-direction: row;
        gap: 0.5rem;
        width: auto;
    }
    table.table tbody tr td[data-label="Delivery Person"] select {
        width: auto !important;
        margin-right: .5rem !important;
    }
    table.table tbody tr td[data-label="Status"] .badge {
        display: inline-block;
        text-align: left;
        margin-bottom: 0;
        width: auto;
    }
}
.table-responsive {
    overflow-x: auto;
    width: 100%;
}
table.table {
    width: 100%;
    min-width: unset;
}
@media (max-width: 991.98px) and (min-width: 576px) {
    .table-responsive {
        overflow-x: auto;
    }
    table.table {
        width: 100%;
        min-width: 700px; /* or adjust as needed for your columns */
    }
}
    </style>
    </head>
    <body class="sb-nav-fixed">
        <?php include "admin_header.php";  ?>
            <div id="layoutSidenav_content">
                <main>
 

<main class="py-4 container-fluid">
      <div class="card border-0 shadow rounded-3 p-4 bg-white mx-auto" style="max-width: 1200px;">
     <center><h2 class="mb-4">Assign Orders to Delivery Personnel</h2></center>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif(!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form class="row g-2 mb-3" method="get">
        <div class="col-auto">
            <input type="text" class="form-control" name="search" placeholder="Customer name" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary">Search</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Total (₹)</th>
                <th>Rent (₹)</th>
                <th>Delivery Person</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php $i=1; while($row=$result->fetch_assoc()):
                $grand = (float)$row['discounted_amount'] + (float)$row['transport_rent'];
            ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td data-label="Customer">
                        <strong><?= htmlspecialchars($row['customer']) ?></strong>
                    </td>
                    <td data-label="Phone">
                        <a href="tel:<?= $row['phone_no'] ?>" class="text-decoration-none small text-muted"><?= htmlspecialchars($row['phone_no']) ?></a>
                    </td>
                    <td data-label="Total (₹)"><?= number_format((float)$row['discounted_amount'],2) ?></td>
                    <td data-label="Rent (₹)"><?= number_format($row['transport_rent'],2) ?></td>
                    <td data-label="Delivery Person">
                        <?php if(!$row['delivery_id']): ?>
                            <form class="d-flex" method="post" onsubmit="return confirm('Assign this order to selected user?');">
                                <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                <select name="delivery_user_id" class="form-select form-select-sm me-2" required>
                                    <option value="" disabled selected>Select driver</option>
                                    <?php foreach($drivers as $d): ?>
                                        <option value="<?= $d['user_id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-primary" name="assign_order">Assign</button>
                            </form>
                        <?php else: ?>
                            <?= htmlspecialchars($mysqli->query("SELECT name FROM users WHERE user_id=".$row['delivery_user_id'])->fetch_column()) ?>
                        <?php endif; ?>
                    </td>
                    <td data-label="Status">
                        <?php if(!$row['delivery_id']): ?>
                            <span class="badge bg-secondary">Pending Assignment</span>
                        <?php else: ?>
                            <?php if($row['amount_remaining']<=0): ?>
                                <span class="badge bg-success">Completed</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($row['status']) ?></span><br>
                                <small class="text-muted">Due ₹<?= number_format($row['amount_remaining'],2) ?></small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td data-label="Actions" class="text-nowrap d-flex flex-row justify-content-between align-items-center text-end">
                        <?php if($row['delivery_id']): ?>
                            <button class="btn btn-sm btn-outline-primary open-delivery me-2" data-delivery="<?= $row['delivery_id'] ?>">
                                <i class="fa fa-truck"></i> Update
                            </button>
                        <?php endif; ?>
                        <a class="btn btn-sm btn-outline-info" target="_blank" href="view_bill.php?order_id=<?= $row['order_id'] ?>"><i class="fa fa-file-invoice"></i> Bill</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

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

    <!-- Delivery modal -->
    <div class="modal fade" id="deliveryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
            <div class="modal-content"></div>
        </div>
    </div>
</main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/scripts.js"></script>
<script>
$(document).on('click','.open-delivery',function(){
    const id = $(this).data('delivery');
    $('#deliveryModal .modal-content').load('delivery_detail.php',{delivery_id:id}, ()=>new bootstrap.Modal('#deliveryModal').show());
});
</script>
</body>
</html>
