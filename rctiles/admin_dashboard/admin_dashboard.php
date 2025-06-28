<?php
include '../db_connect.php';
// Get today's total custom_total and total orders
$sql = "SELECT SUM((SELECT SUM(custom_price) FROM pending_orders WHERE order_id = o.order_id)) AS total_custom_total, COUNT(DISTINCT o.order_id) AS total_orders FROM orders o WHERE DATE(o.order_date) = CURDATE()";
$result = $mysqli->query($sql);
$total_custom_total = 0;
$total_orders = 0;
if ($result && $row = $result->fetch_assoc()) {
    $total_custom_total = $row['total_custom_total'];
    if ($total_custom_total === null) {
        $total_custom_total = 0;
    }
    $total_orders = $row['total_orders'] ?? 0;
}
// Get total count of pending_orders
$count_pending_orders = 0;
$count_result = $mysqli->query("SELECT COUNT(*) AS total FROM pending_orders");
if ($count_result && $row2 = $count_result->fetch_assoc()) {
    $count_pending_orders = (int)$row2['total'];
}
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">                <style>
   body {
  background-color: #f8f9fa;
  font-family: 'Poppins', sans-serif;
}

.info-card {
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  padding: 20px;
  background: white;
  transition: 0.3s ease;
   border-left: 4px solid rgb(29, 29, 31);
}

.info-card h5 {
  font-weight: 600;
  font-size: 18px;
  margin-bottom: 20px;
}

.info-card p {
  font-size: 1.2rem;
  font-weight: 500;
}

.nav-card {
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  padding: 15px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  text-decoration: none;
  color: #333;
  font-weight: 500;
  width: 150px;
  height: 80px;
  transition: transform 0.3s ease;
}

.nav-card:hover {
  transform: scale(1.05);
  background-color: #f1f1f1;
}

.card-link {
  text-decoration: none !important;
  display: block;
  height: 100%;
}

.card-link .info-card {
  border-radius: 12px;
  background: white;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-link:hover .info-card {
  transform: scale(1.05);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  background-color: #f1f1f1;
}

.card-link h5, .card-link p {
  color:  #f8f9fa;
}
@media (max-width: 576px) {
  .info-row .col-md-3 {
    flex: 0 0 50%;
    max-width: 50%; 
  }
  .info-card p{
    font-size: 15px;
  }
  .nav-card {
    margin-left: -90px;
    display: block;
    width: 290px;
    height: 50px;
  }
}
  </style>
    </head>
    <body class="sb-nav-fixed">
        <?php include "admin_header.php";  ?>
          <div id="layoutSidenav_content">
                <main>
<div class="card border-0 shadow rounded-3 my-4 p-4 bg-white mx-4 mx-md-auto" style="max-width: 990px; min-height: 550px;">
      <h1 class="mb-2 fw-bold mt-3 text-center">Admin Dashboard </h1>
                     <div class="container py-5">
    <!-- Section 1: Info S tats -->
  <div class="row info-row text-center mb-4">
  <div class="col-md-3 mb-3">
    <a href="total_orders.php" class="card-link">
      <div class="info-card bg-primary  h-100">
        <h5>Total Orders</h5>
        <p> <?= (int)$total_orders ?></p>
      </div>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="total_amount.php" class="card-link">
      <div class="info-card bg-secondary  h-100">
        <h5>Total Sales</h5>
         <p>₹<?= number_format((float)$total_custom_total, 2) ?></p>
      </div>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="pending_orders.php" class="card-link">
      <div class="info-card bg-danger  h-100">
        <h5>Pending Orders</h5>
        <p> <?= (int)$total_orders ?></p>
      </div>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="shipped_orders.php" class="card-link">
      <div class="info-card bg-success  h-100">
        <h5>Shipped Orders</h5>
        <p>105</p>
      </div>
    </a>
  </div>
</div>


    <!-- Section 2: Navigation Tabs -->
  <div class="container mt-4">
  <div class="row justify-content-center text-center gap-4">
    <div class="col-6 col-sm-4 col-md-2 mb-3">
    <a href="transaction.php" class="nav-card">Transaction</a>
    </div>
    <div class="col-6 col-sm-4 col-md-2 mb-3">
      <a href="view_orders.php" class="nav-card">View Orders</a>
    </div>
    <div class="col-6 col-sm-4 col-md-2 mb-3">
      <a href="create_bill.php" class="nav-card">Create Bill</a>
    </div>
    <div class="col-6 col-sm-4 col-md-2 mb-3">
      <a href="low_stock.php" class="nav-card">Low Stock</a>
    </div>
    <div class="col-6 col-sm-4 col-md-2 mb-3">
      <a href="customer_leader.php" class="nav-card">Customer Leader</a>
    </div>
  </div>
</div>
  </div>
                </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
</div>
    </body>
</html>
