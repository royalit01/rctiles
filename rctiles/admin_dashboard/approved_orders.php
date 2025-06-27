<?php
include '../db_connect.php';

// Fetch approved orders with total amount and custom price
$sql = "SELECT po.order_id, c.name AS customer_name, c.phone_no, 
                               (SELECT SUM(custom_price) FROM pending_orders WHERE order_id = o.order_id)  AS 
 total_amount, SUM(po.custom_price) AS custom_total
        FROM pending_orders po
        JOIN customers c ON po.customer_id = c.customer_id
        JOIN orders o ON po.order_id = o.order_id
        WHERE po.approved = 1
        GROUP BY po.order_id";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Approved Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 56px; /* Space for fixed navbar */
        }
        .container-box {
            width: 100%;
            max-width: 1200px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            min-width: 600px; /* Ensures table doesn't get too narrow */
        }
        .mobile-card {
            display: none;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
        }
        .mobile-card .card-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .mobile-card .card-label {
            font-weight: bold;
            color: #6c757d;
        }
        @media (max-width: 768px) {
            .table-container {
                display: none;
            }
            .mobile-card {
                display: block;
            }
            .container-box {
                padding: 15px;
                margin: 10px;
            }
        }
        @media (max-width: 576px) {
            .btn-sm-block {
                display: block;
                width: 100%;
                margin-top: 5px;
            }
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include "admin_header.php"; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-box">
                <h2 class="text-center mb-3">Approved Orders</h2>
                
                <!-- Desktop Table View -->
                <div class="table-responsive table-container">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Total Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone_no']); ?></td>
                                    <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm btn-sm-block" onclick="generateBill(<?php echo $row['order_id']; ?>)">
                                            Generate Bill
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Card View -->
                <?php 
                // Reset pointer to loop through results again
                $result->data_seek(0); 
                while ($row = $result->fetch_assoc()): ?>
                    <div class="mobile-card">
                        <div class="card-row">
                            <span class="card-label">Customer:</span>
                            <span><?php echo htmlspecialchars($row['customer_name']); ?></span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">Phone:</span>
                            <span><?php echo htmlspecialchars($row['phone_no']); ?></span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">Total Price:</span>
                            <span>₹<?php echo number_format($row['total_amount'], 2); ?></span>
                        </div>
                        <div class="card-row">
                            <button class="btn btn-primary btn-sm btn-sm-block" onclick="generateBill(<?php echo $row['order_id']; ?>)">
                                Generate Bill
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between small">
                    <div class="text-muted mb-2 mb-md-0">Copyright &copy; Your Website <?php echo date('Y'); ?></div>
                    <div>
                        <a href="#">Privacy Policy</a>
                        &middot;
                        <a href="#">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <script>
        function generateBill(orderId) {
            window.location.href = `generate_bill.php?order_id=${orderId}`;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="../js/datatables-simple-demo.js"></script>
</body>
</html>