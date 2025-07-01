<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
require '../db_connect.php';

// Pagination setup
$itemsPerPage = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $itemsPerPage;

$where  = ["po.approved = 1", "o.stock_done = 0"];
$params = [];
$types  = '';

if (!empty($_GET['search'])) {
    $where[] = "c.name LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
    $types   .= 's';
}
if (!empty($_GET['from'])) {
    $where[] = "DATE(o.order_date) >= ?";
    $params[] = $_GET['from'];
    $types   .= 's';
}
if (!empty($_GET['to'])) {
    $where[] = "DATE(o.order_date) <= ?";
    $params[] = $_GET['to'];
    $types   .= 's';
}

// Count total for pagination
$count_sql = "SELECT COUNT(DISTINCT o.order_id) as total FROM orders o JOIN customers c ON c.customer_id = o.customer_id JOIN pending_orders po ON po.order_id = o.order_id WHERE " . implode(' AND ', $where);
$count_stmt = $mysqli->prepare($count_sql);
if ($types) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_items = $count_result ? $count_result->fetch_assoc()['total'] : 0;
$total_pages = $total_items > 0 ? ceil($total_items / $itemsPerPage) : 1;
$count_stmt->close();

$sql = "SELECT o.order_id,
               c.name AS customer,
               c.phone_no,
               SUM(po.custom_price) AS total_price,
               o.stock_done,
               SUM(po.quantity) AS total_ordered,
               SUM(COALESCE(di.qty_delivered, 0)) AS total_delivered,
               SUM(COALESCE(ms.quantity_subtracted, 0)) AS total_subtracted
        FROM orders o
        JOIN customers c ON c.customer_id = o.customer_id
        JOIN pending_orders po ON po.order_id = o.order_id
        LEFT JOIN delivery_orders do2 ON do2.order_id = o.order_id
        LEFT JOIN delivery_items di ON di.delivery_id = do2.delivery_id AND di.product_id = po.product_id
        LEFT JOIN minus_stock ms ON ms.order_id = o.order_id AND ms.product_id = po.product_id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY o.order_id
        ORDER BY o.order_date DESC
        LIMIT $itemsPerPage OFFSET $offset";

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
         <style>
        /* Base font slightly larger for readability */
        body{font-size:1.05rem;}
@media (max-width: 768px) {
    .table td, .table th {
        font-size: 0.85rem;
        max-width: 100px;
    }

    .table .btn {
        font-size: 0.7rem;
        padding: 0.25rem 0.4rem;
    }

    .table td a {
        word-break: break-all;
    }
}
        @media (max-width: 575.98px){
            /* card‑style rows on extra‑small screens */
            table.table thead{display:none} /* Hide table headings on mobile */
            .mobile-row-heading {
        /* display: block; */
        background: #0d6efd;
        color: #fff;
        color: #000;
        font-weight: bold;
        border-radius: .5rem .5rem 0 0;
        padding: 0.5rem 0.9rem 0.5rem 0.9rem;
        margin-bottom: 0.2rem;
        font-size: 1.05em;
        letter-spacing: 0.5px;
        text-align: left;
    }
    table.table tbody tr td {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: .55rem .9rem;
        font-size: 1rem;
        border: none;
        border-bottom: 1px solid #e9ecef;
        background: none;
        text-align: right;
        position: relative;
    }
    table.table tbody tr td:last-child {
        border-bottom: none;
    }
    table.table tbody tr td[data-label]::before {
        content: attr(data-label);
        display: block;
        font-weight: 600;
        color: #0d6efd;
        margin-bottom: 0;
        font-size: 0.97em;
        text-align: left;
        flex: 1 1 auto;
    }
    table.table tbody tr td > * {
        margin-left: auto;
        text-align: right;
    }
    /* Ensure anchor and button content does not overlap label */
    table.table tbody tr td[data-label] > * {
        display: inline-block;
        vertical-align: middle;
    }
}
        .table {
    table-layout: auto !important;
    width: 100% !important;
}
.table td, .table th {
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    max-width: 100%;
}
.modal .table-responsive {
  max-width: 100%;
  overflow-x: auto;
}
.modal .table {
  min-width: 1000px; /* Adjust as needed for your columns */
}

    </style>
    </head>
    <body class="sb-nav-fixed">
        <?php include "admin_header.php";  ?>
            <div id="layoutSidenav_content">
                
                <main>
                    <div class="card border-0 shadow my-3 rounded-3 p-4 bg-white mx-auto" style="max-width: 900px;">
                    <br>
            <h2 class="mb-4"><center>Orders Waiting for Stock Deduction</center></h2>

            <!-- filter form -->
            <form class="row row-cols-lg-auto g-2 align-items-end mb-4" method="get">
                <div class="col-12 col-md">
                    <label for="from" class="form-label small mb-1">From</label>
                    <input type="date" id="from" name="from" class="form-control"
                           value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
                </div>
                <div class="col-12 col-md">
                    <label for="to" class="form-label small mb-1">To</label>
                    <input type="date" id="to" name="to" class="form-control"
                           value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
                </div>
                <div class="col-12 col-md flex-grow-1">
                    <label for="search" class="form-label small mb-1">Customer</label>
                    <input type="text" id="search" name="search" class="form-control" placeholder="Customer name"
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-12 col-md-auto">
                    <button class="btn btn-primary w-100"><i class="fa fa-filter me-1"></i>Filter</button>
                </div>
            </form>

            <!-- orders table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Total Price</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1 + $offset; while ($row = $result->fetch_assoc()): ?>
<tr>
    <td colspan="5" class="mobile-row-heading" style="display:none;"># | Customer | Phone | Total Price | Actions</td>
    <td data-label="#"><?= $i++ ?></td>
    <td data-label="Customer"><?= htmlspecialchars($row['customer']) ?></td>
    <td data-label="Phone">
        <a href="tel:<?= $row['phone_no'] ?>" class="text-decoration-none">
            <?= htmlspecialchars($row['phone_no']) ?>
        </a>
    </td>
    <td data-label="Total Price">₹<?= number_format($row['total_price'], 2) ?></td>
    <td data-label="Actions" class="text-nowrap">
        <?php if ($row['stock_done']): ?>
            <span class="badge bg-success mb-1">Stock Updated</span>
            <button type="button"
                    class="btn btn-sm btn-outline-danger ms-1 delete-order mb-1"
                    data-order="<?= $row['order_id'] ?>"
                    title="Remove record">
                <i class="fa fa-trash"></i>
            </button>
        <?php else: ?>
            <button type="button"
                    class="btn btn-sm btn-danger minus-stock-btn mb-1"
                    data-order="<?= $row['order_id'] ?>">
                <i class="fa fa-minus me-1"></i>Minus Stock
            </button>
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
                </table>
                <!-- Pagination controls -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-3">
                        <?php 
                        $queryString = $_GET;
                        unset($queryString['page']);
                        $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
                        $queryStr = http_build_query($queryString);
                        $pageUrl = $baseUrl . ($queryStr ? '?' . $queryStr . '&' : '?') . 'page=';
                        ?>
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $pageUrl . ($page - 1) ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo; Prev</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $pageUrl . $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $pageUrl . ($page + 1) ?>" aria-label="Next">
                                    <span aria-hidden="true">Next &raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <!-- End Pagination controls -->
            </div>

            <!-- fullscreen‑on‑mobile modal -->
            <div class="modal fade" id="minusModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-fullscreen-sm-down">
                    <div class="modal-content"></div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// open minus‑stock modal
$(document).on('click', '.minus-stock-btn', function () {
    const orderId = $(this).data('order');
    $('#minusModal .modal-content').load(
        'fetch_minus_stock_modal.php',
        {order_id: orderId},
        () => new bootstrap.Modal('#minusModal').show()
    );
});

// delete stock record
$(document).on('click', '.delete-order', function () {
    if (!confirm('Delete this stock-record entry?')) return;
    const id = $(this).data('order');
    $.post('delete_stock_done.php', {order_id: id}, () => location.reload());
});
</script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script>
new simpleDatatables.DataTable(document.querySelector('table.table'), {
    searchable: false,
    fixedHeight: true
});
</script>
 <script src="../js/scripts.js"></script>
</body>
</html>
