<?php

require '../db_connect.php';

// Get search parameters if any
$search = $_GET['search'] ?? '';
$from_date = $_GET['from'] ?? '';
$to_date = $_GET['to'] ?? '';

// Build the base query
$sql = "SELECT 
            ms.id,
            ms.order_id,
            o.order_date,
            c.name AS customer_name,
            p.product_name,
            sa.storage_area_name,
            ms.quantity_subtracted,
            u.name AS user_name,
            ms.subtracted_at
        FROM minus_stock ms
        JOIN orders o ON ms.order_id = o.order_id
        JOIN customers c ON o.customer_id = c.customer_id
        JOIN products p ON ms.product_id = p.product_id
        JOIN storage_areas sa ON ms.storage_area_id = sa.storage_area_id
        JOIN users u ON ms.subtracted_by = u.user_id";

// Add where conditions if search parameters exist
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(c.name LIKE ? OR p.product_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if (!empty($from_date)) {
    $where[] = "DATE(ms.subtracted_at) >= ?";
    $params[] = $from_date;
    $types .= 's';
}

if (!empty($to_date)) {
    $where[] = "DATE(ms.subtracted_at) <= ?";
    $params[] = $to_date;
    $types .= 's';
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

// Order by subtraction date descending
$sql .= " ORDER BY ms.subtracted_at DESC";

// Prepare and execute the query
$stmt = $mysqli->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

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
    <style>
        body { font-size: 1.05rem; }
        @media (max-width: 575.98px) {
            table.table thead { display: none; }
            table.table tbody tr { 
                display: block; 
                margin-bottom: 1rem; 
                border: 1px solid #dee2e6; 
                border-radius: .5rem; 
            }
            table.table tbody tr td { 
                display: flex; 
                justify-content: space-between; 
                padding: .55rem .9rem; 
                font-size: 1rem; 
            }
            table.table tbody tr td:first-child { font-weight: 600; }
        }
    </style>
    </head>
    <body class="sb-nav-fixed">
        <?php include "admin_header.php";  ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid">
                        <br>
                        <center>
                        <h2 class="mb-4"><b>Stock Subtraction Log</b></h2>
                        </center>

                        <!-- Filter form -->
                        <form class="row row-cols-lg-auto g-2 align-items-end mb-4" method="get">
                            <div class="col-12 col-md">
                                <label for="from" class="form-label small mb-1">From</label>
                                <input type="date" id="from" name="from" class="form-control"
                                    value="<?= htmlspecialchars($from_date) ?>">
                            </div>
                            <div class="col-12 col-md">
                                <label for="to" class="form-label small mb-1">To</label>
                                <input type="date" id="to" name="to" class="form-control"
                                    value="<?= htmlspecialchars($to_date) ?>">
                            </div>
                            <div class="col-12 col-md flex-grow-1">
                                <label for="search" class="form-label small mb-1">Search</label>
                                <input type="text" id="search" name="search" class="form-control" 
                                    placeholder="Customer or Product" value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-12 col-md-auto">
                                <button class="btn btn-primary w-100"><i class="fa fa-filter me-1"></i>Filter</button>
                            </div>
                        </form>

                        <!-- Stock subtraction log table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Order ID</th>
                                        <th>Order Date</th>
                                        <th>Customer</th>
                                        <th>Product</th>
                                        <th>Storage Area</th>
                                        <th>Qty Subtracted</th>
                                        <th>Subtracted By</th>
                                        <th>Subtracted At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= $row['order_id'] ?></td>
                                            <td><?= date('d M Y', strtotime($row['order_date'])) ?></td>
                                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                                            <td><?= htmlspecialchars($row['storage_area_name']) ?></td>
                                            <td><?= $row['quantity_subtracted'] ?></td>
                                            <td><?= htmlspecialchars($row['user_name']) ?></td>
                                            <td><?= date('d M Y H:i', strtotime($row['subtracted_at'])) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; Your Website 2023</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
        <script>
        new simpleDatatables.DataTable(document.querySelector('table.table'), {
            searchable: false,
            fixedHeight: true
        });
    </script>
    </body>
</html>
