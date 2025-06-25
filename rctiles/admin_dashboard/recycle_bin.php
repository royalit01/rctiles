<?php include '../db_connect.php';

$sql = "SELECT o.order_id, c.name AS customer_name, o.total_amount
        FROM recycle_bin_orders o
        JOIN customers c ON o.customer_id = c.customer_id";

$result = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Manager Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <?php include "admin_header.php";  ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container my-4">
                        <div class="card border-0 shadow rounded-3 p-4 bg-white mx-auto" style="max-width: 1200px;">
                        <h3 class="text-center mb-4">Recycle Bin</h3>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer Name</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php while($row = $result->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= $row['order_id'] ?></td>
                                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                        <td>₹<?= $row['total_amount'] ?></td>
                                        <td>
                                            <form method="POST" action="recover_order.php" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                                <input type="hidden" name="action" value="recover">
                                                <button class="btn btn-success btn-sm" type="submit" onclick="return confirm('Recover this order?')">Recover</button>
                                            </form>
                                            <form method="POST" action="delete_permanently.php" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button class="btn btn-danger btn-sm" type="submit" onclick="return confirm('Permanently delete this order?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
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
    </body>
</html>
