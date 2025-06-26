<?php 
include "admin_header.php";
include '../db_connect.php';

// Initialize filter variables
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$limit = 500;

// Base SQL query
$sql = "SELECT DISTINCT o.order_id, c.name AS customer_name, c.phone_no, o.total_amount, 
                (SELECT SUM(custom_price) FROM pending_orders WHERE order_id = o.order_id) AS custom_total,
                po.approved, o.order_date
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        JOIN pending_orders po ON o.order_id = po.order_id
        WHERE 1=1";

// Add filters
if ($status_filter !== 'all') {
    $sql .= " AND po.approved = " . ($status_filter === 'approved' ? 1 : ($status_filter === 'rejected' ? -1 : 0));
}
if (!empty($date_filter)) {
    $sql .= " AND DATE(o.order_date) = '$date_filter'";
}

$sql .= " ORDER BY o.order_date DESC LIMIT $limit";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Orders</title>
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        #layoutSidenav_content {
            padding: 20px;
        }
        .container-box {
            width: 100%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table {
            min-width: 600px;
        }
        .mobile-card {
            display: none;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            background: white;
        }
        .mobile-card .card-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .mobile-card .card-label {
            font-weight: bold;
            color: #6c757d;
            width: 120px;
        }
        .mobile-card .card-value {
            flex: 1;
            text-align: right;
        }
        .mobile-card .card-actions {
            margin-top: 15px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .filter-section .row > div {
            margin-bottom: 15px;
        }
        .badge {
            font-size: 0.85rem;
            padding: 5px 8px;
        }
        @media (max-width: 768px) {
            #layoutSidenav_content {
                padding: 15px;
            }
            .table-container {
                display: none;
            }
            .mobile-card {
                display: block;
            }
            .container-box {
                padding: 15px;
            }
            .filter-section .d-flex {
                justify-content: flex-start !important;
            }
        }
        @media (max-width: 576px) {
            #layoutSidenav_content {
                padding: 10px;
            }
            .btn-sm-block {
                display: block;
                width: 100%;
                margin-top: 8px;
            }
            .filter-section .col-md-4 {
                width: 100%;
            }
            .mobile-card {
                padding: 12px;
            }
            .mobile-card .card-label {
                width: 100px;
                font-size: 0.9rem;
            }
            .mobile-card .card-value {
                font-size: 0.9rem;
            }
        }
        .modal-body {
            max-height: 400px;
            overflow-y: auto;
        }
        #productModal .table th, #productModal .table td {
            vertical-align: middle;
            text-align: center;
        }
        #productModal .modal-body {
            padding: 1.5rem 1.5rem 1rem 1.5rem;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid">
                <div class="container-box">
                    <h2 class="text-center mb-4">Orders</h2>

                    <!-- Filter Section -->
                    <div class="filter-section mb-4">
                        <form method="GET" action="">
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <label for="status" class="form-label">Filter by Status:</label>
                                    <select class="form-select" name="status" id="status">
                                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Orders</option>
                                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                                        <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label for="date" class="form-label">Filter by Date:</label>
                                    <input type="date" class="form-control" name="date" id="date" value="<?= $date_filter ?>">
                                </div>
                                <div class="col-md-4 col-12 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2 flex-grow-1">Apply Filters</button>
                                    <a href="?" class="btn btn-secondary flex-grow-1">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="table-responsive table-container">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Original Price</th>
                                    <th>Custom Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr id="orderRow-<?= $row['order_id'] ?>">
                                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($row['phone_no']) ?></td>
                                        <td>₹<?= number_format($row['custom_total'], 2) ?></td>
                                        <td>₹<?= number_format($row['total_amount'], 2) ?></td>
                                        <td>
                                            <?php if ($row['approved'] == 0): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php elseif ($row['approved'] == 1): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php elseif ($row['approved'] == -1): ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button class="btn btn-info btn-sm" onclick="viewProducts(<?= $row['order_id'] ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <?php if ($row['approved'] == 0): ?>
                                                    <button class="btn btn-success btn-sm approve-btn" onclick="approveOrder(<?= $row['order_id'] ?>, this)">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button class="btn btn-danger btn-sm reject-btn" onclick="rejectOrder(<?= $row['order_id'] ?>, this)">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <?php 
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()): ?>
                        <div class="mobile-card">
                            <div class="card-row">
                                <span class="card-label">Customer:</span>
                                <span class="card-value"><?= htmlspecialchars($row['customer_name']) ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">Phone:</span>
                                <span class="card-value"><?= htmlspecialchars($row['phone_no']) ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">Original Price:</span>
                                <span class="card-value">₹<?= number_format($row['custom_total'], 2) ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">Custom Price:</span>
                                <span class="card-value">₹<?= number_format($row['total_amount'], 2) ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">Status:</span>
                                <span class="card-value">
                                    <?php if ($row['approved'] == 0): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php elseif ($row['approved'] == 1): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php elseif ($row['approved'] == -1): ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-info btn-sm btn-sm-block" onclick="viewProducts(<?= $row['order_id'] ?>)">
                                    <i class="fas fa-eye"></i> View Products
                                </button>
                                <?php if ($row['approved'] == 0): ?>
                                    <button class="btn btn-success btn-sm btn-sm-block" onclick="approveOrder(<?= $row['order_id'] ?>, this)">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-sm-block" onclick="rejectOrder(<?= $row['order_id'] ?>, this)">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal for Viewing Products -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="productModalLabel">Order Products</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th>Original Price (₹)</th>
                                    <th>Custom Price (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="productDetails">
                                <!-- Products will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewProducts(orderId) {
            $.ajax({
                url: 'fetch_order_products.php',
                type: 'GET',
                data: { order_id: orderId },
                success: function(response) {
                    $('#productDetails').html(response);
                    $('#productModal').modal('show');
                }
            });
        }

        function approveOrder(orderId, button) {
            if (!confirm("Are you sure you want to approve this order?")) return;
            $.ajax({
                url: 'update_order_status.php',
                type: 'POST',
                data: { order_id: orderId, action: 'approve' },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert("Error: " + response.message);
                    }
                }
            });
        }

        function rejectOrder(orderId, button) {
            if (!confirm("Are you sure you want to reject this order?")) return;
            $.ajax({
                url: 'update_order_status.php',
                type: 'POST',
                data: { order_id: orderId, action: 'reject' },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert("Error: " + response.message);
                    }
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="../js/datatables-simple-demo.js"></script>
</body>
</html>