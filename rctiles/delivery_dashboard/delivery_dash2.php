<?php


include "delivery_header.php"; 

include "../db_connect.php";

$pendingOrders = $mysqli->query("SELECT o.order_id, c.name, c.phone_no, c.address, o.final_amount AS total_amount, o.rent_amount, do.delivery_id FROM delivery_orders do JOIN orders o ON do.order_id = o.order_id JOIN customers c ON o.customer_id = c.customer_id WHERE (do.status = 'Assigned' OR do.status = 'Partially Paid') AND do.item_delivered = 0");
$deliveredOrders = $mysqli->query("SELECT o.order_id, c.name, c.phone_no, c.address FROM delivery_orders do JOIN orders o ON do.order_id = o.order_id JOIN customers c ON o.customer_id = c.customer_id WHERE do.status = 'Completed'");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Delivery Dashboard</title>
    <!-- Bootstrap & FontAwesome included in header -->
       <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <style>
          
        </style>

</head>
<body class="sb-nav-fixed">
<div id="layoutSidenav_content">
<main class="main-content ">
  <div class="card border-0 shadow rounded-3 p-4 bg-white mx-auto" style="min-width: 100%;">
    <div class="dashboard-header mb-4">
      <h2 class="dashboard-title">Delivery Dashboard</h2>
      <p class="dashboard-subtitle">Manage your delivery operations efficiently</p>
    </div>
    <div class="dashboard-actions">
      <div class="row g-3">
        <div class="col-md-6">
          <button class="btn btn-pending status-btn w-100" id="pendingBtn" type="button">
            <i class="fas fa-clock"></i>
            <span>PENDING</span>
            <div class="btn-count"><?php echo $pendingOrders ? $pendingOrders->num_rows : 0; ?></div>
          </button>
        </div>
        <div class="col-md-6">
          <button class="btn btn-delivered status-btn w-100" id="deliveredBtn" type="button">
            <i class="fas fa-check-circle"></i>
            <span>DELIVERED</span>
            <div class="btn-count"><?php echo $deliveredOrders ? $deliveredOrders->num_rows : 0; ?></div>
          </button>
        </div>
      </div>
    </div>

  </div>
  <!-- Show Pending Orders Table -->
  <div class="mt-4" id="pendingSection">
    <h4 class="mb-3">Pending Deliveries</h4>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['collect_amount'])) {
      include_once '../db_connect.php';
      $successMsg = $errorMsg = '';
      $deliveryIds = [];
      $res = $mysqli->query("SELECT delivery_id, order_id FROM delivery_orders WHERE status = 'Assigned' OR status = 'Partially Paid'");
      while ($drow = $res->fetch_assoc()) {
        $deliveryIds[$drow['order_id']] = $drow['delivery_id'];
      }
      foreach ($_POST['collect_amount'] as $orderId => $amount) {
        $amount = floatval($amount);
        if ($amount > 0 && isset($deliveryIds[$orderId])) {
          $deliveryId = $deliveryIds[$orderId];
          // Insert payment
          $stmt = $mysqli->prepare("INSERT INTO delivery_payments (delivery_id, amount_paid, remarks) VALUES (?, ?, ?)");
          $empty = '';
          $stmt->bind_param('ids', $deliveryId, $amount, $empty);
          $stmt->execute();
          // Update delivery_orders: Only update amount_paid, amount_remaining, and status to 'Partially Paid' if not fully paid
          $mysqli->query("UPDATE delivery_orders SET amount_paid = amount_paid + $amount, amount_remaining = GREATEST(amount_remaining - $amount, 0), status = IF(amount_remaining - $amount <= 0, status, 'Partially Paid') WHERE delivery_id = $deliveryId");
          $successMsg = 'Collection updated successfully.';
        }
      }
      if ($successMsg) {
        echo '<div class="alert alert-success">'.$successMsg.'</div>';
        // Add this to reload the page after a short delay
        echo '<script>setTimeout(function(){ window.location.reload(); }, 1200);</script>';
      }
      if ($errorMsg) echo '<div class="alert alert-danger">'.$errorMsg.'</div>';
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_delivered'])) {
      $deliveryId = (int)$_POST['mark_delivered'];
      // Check if payment is complete
      $row = $mysqli->query("SELECT amount_remaining FROM delivery_orders WHERE delivery_id = $deliveryId")->fetch_assoc();
      if ($row && floatval($row['amount_remaining']) <= 0) {
        // Set both item_delivered=1 and status='Completed'
        $mysqli->query("UPDATE delivery_orders SET item_delivered = 1, status = 'Completed' WHERE delivery_id = $deliveryId");
      } else {
        // Only set item_delivered=1, keep status as is
        $mysqli->query("UPDATE delivery_orders SET item_delivered = 1 WHERE delivery_id = $deliveryId");
      }
      echo '<div class="alert alert-success">Marked as delivered.</div>';
      echo '<script>setTimeout(function(){ window.location.reload(); }, 1200);</script>';
    }
    if ($pendingOrders && $pendingOrders->num_rows > 0) {
      echo '<form id="collectAmountForm" method="post"><div class="table-responsive"><table class="table table-bordered align-middle mb-0">';
      echo '<thead class="table-primary"><tr><th>Order ID</th><th>Name</th><th>Mobile</th><th>Address</th><th>Storage Area ID</th><th>Products</th><th>Total Amount</th><th>Rent</th><th>Status</th><th>Collect Amount</th><th>Item Delivered</th></tr></thead><tbody>';
      while ($row = $pendingOrders->fetch_assoc()) {
        // Fetch latest paid and remaining for this delivery
        $deliveryIdRes = $mysqli->query("SELECT delivery_id, amount_paid, amount_remaining FROM delivery_orders WHERE order_id = ".(int)$row['order_id']." AND status = 'Assigned' LIMIT 1");
        $deliveryData = $deliveryIdRes ? $deliveryIdRes->fetch_assoc() : null;
        $paid = $deliveryData ? $deliveryData['amount_paid'] : 0;
        $remaining = $deliveryData ? $deliveryData['amount_remaining'] : ($row['total_amount'] + $row['rent_amount']);
        $grand = (float)$row['total_amount'] + (float)$row['rent_amount'];
        $storageAreaRes = $mysqli->query("SELECT storage_area_id, product_id FROM minus_stock WHERE order_id = ".(int)$row['order_id']);
        $storageAreaIds = [];
        $productInfo = [];
        if ($storageAreaRes && $storageAreaRes->num_rows > 0) {
          while ($saRow = $storageAreaRes->fetch_assoc()) {
            $storageAreaIds[] = $saRow['storage_area_id'];
            $pid = (int)$saRow['product_id'];
            $pname = '-';
            $qtyOrdered = '-';
            // Fetch product name
            $productRes = $mysqli->query("SELECT product_name FROM products WHERE product_id = $pid LIMIT 1");
            if ($productRes && $productRes->num_rows > 0) {
              $pname = $productRes->fetch_assoc()['product_name'];
            }
            // Fetch qty_ordered from delivery_items
            if (isset($deliveryData['delivery_id'])) {
              $did = (int)$deliveryData['delivery_id'];
              $qtyRes = $mysqli->query("SELECT qty_ordered FROM delivery_items WHERE delivery_id = $did AND product_id = $pid LIMIT 1");
              if ($qtyRes && $qtyRes->num_rows > 0) {
                $qtyOrdered = $qtyRes->fetch_assoc()['qty_ordered'];
              }
            }
            $productInfo[] = htmlspecialchars($pname) . ' (Qty: ' . htmlspecialchars($qtyOrdered) . ')';
          }
        }
        $storageAreaIdDisplay = $storageAreaIds ? htmlspecialchars(implode(', ', $storageAreaIds)) : '-';
        $productInfoDisplay = $productInfo ? htmlspecialchars(implode(', ', $productInfo)) : '-';
        echo '<tr>'
          .'<td>'.htmlspecialchars($row['order_id']).'</td>'
          .'<td>'.htmlspecialchars($row['name']).'</td>'
          .'<td>'.htmlspecialchars($row['phone_no']).'</td>'
          .'<td>'.htmlspecialchars($row['address']).'</td>'
          .'<td>'.$storageAreaIdDisplay.'</td>'
          .'<td>'.$productInfoDisplay.'</td>'
          .'<td>'
            .'<div class="border p-2 rounded">'
            .'<strong>Grand Total:</strong> ₹'.number_format($grand, 2).'<br>'
            .'<span class="text-success">Paid: ₹'.number_format($paid, 2).'</span><br>'
            .'<span class="'.($remaining > 0 ? 'text-danger' : 'text-muted').'">Remaining: ₹'.number_format($remaining, 2).'</span>'
            .'</div>'
          .'</td>'
          .'<td>₹'.number_format((float)$row['rent_amount'],2).'</td>'
          .'<td><span class="badge badge-pending">Pending</span></td>'
          .'<td><input type="number" min="0" max="'.htmlspecialchars($remaining).'" step="0.01" name="collect_amount['.htmlspecialchars($row['order_id']).']" class="form-control form-control-sm" placeholder="Enter amount"></td>'
          .'<td>';
        echo '<button type="submit" name="mark_delivered" value="'.htmlspecialchars($row['delivery_id']).'" class="btn btn-success btn-sm">Mark Delivered</button>';
        echo '</td>';
        echo '</tr>';
      }
      echo '</tbody></table></div>';
      echo '<div class="mt-3 text-end"><button type="submit" class="btn btn-success">Submit Collection</button></div></form>';
    } else {
      echo '<div class="list-group-item">No pending deliveries.</div>';
    }
    ?>
  </div>
  <!-- Show Delivered Orders Table -->
  <div class="mt-5" id="deliveredSection" style="display:none;">
    <h4 class="mb-3">Delivered Orders</h4>
    <?php
    if ($deliveredOrders && $deliveredOrders->num_rows > 0) {
      echo '<div class="table-responsive"><table class="table table-bordered align-middle mb-0">';
      echo '<thead class="table-success"><tr><th>Order ID</th><th>Name</th><th>Mobile</th><th>Address</th><th>Grand Total</th><th>Paid</th><th>Remaining</th><th>Status</th></tr></thead><tbody>';
      while ($row = $deliveredOrders->fetch_assoc()) {
        // Fetch latest paid and remaining for this delivery
        $deliveryIdRes = $mysqli->query("SELECT delivery_id, amount_paid, amount_remaining, rent FROM delivery_orders WHERE order_id = ".(int)$row['order_id']." AND status = 'Completed' LIMIT 1");
        $deliveryData = $deliveryIdRes ? $deliveryIdRes->fetch_assoc() : null;
        $paid = $deliveryData ? $deliveryData['amount_paid'] : 0;
        $rent = $deliveryData ? $deliveryData['rent'] : 0;
        // You may want to fetch total_amount from orders table as well
        $orderTotalRes = $mysqli->query("SELECT final_amount FROM orders WHERE order_id = ".(int)$row['order_id']);
        $orderTotalData = $orderTotalRes ? $orderTotalRes->fetch_assoc() : null;
        $totalAmount = $orderTotalData ? $orderTotalData['final_amount'] : 0;
        $grand = (float)$totalAmount + (float)$rent;
        $remaining = $deliveryData ? $deliveryData['amount_remaining'] : 0;
        echo '<tr>'
          .'<td>'.htmlspecialchars($row['order_id']).'</td>'
          .'<td>'.htmlspecialchars($row['name']).'</td>'
          .'<td>'.htmlspecialchars($row['phone_no']).'</td>'
          .'<td>'.htmlspecialchars($row['address']).'</td>'
          .'<td>₹'.number_format($grand, 2).'</td>'
          .'<td>₹'.number_format($paid, 2).'</td>'
          .'<td>₹'.number_format($remaining, 2).'</td>'
          .'<td><span class="badge badge-delivered">Delivered</span></td>'
          .'</tr>';
      }
      echo '</tbody></table></div>';
    } else {
      echo '<div class="list-group-item">No delivered orders.</div>';
    }
    ?>
  </div>
</main>
</div>
<!-- Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="statusModalLabel">
          <i class="modal-icon me-2"></i>
          <span id="modalTitle">Status Details</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="modalContent"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<!-- Styles for dashboard page -->
<style>
  html, body {
    overflow-x: auto;
    height: 100%;
  }
  body {
    background-color: #f8f9fa;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  }
  .main-content {
    margin-top: 70px !important;
    width: 100%;
    max-width: 100vw;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }
  .card {
    max-width: 100% !important;
    width: 100% !important;
  }
  .table-responsive {
    width: 100%;
    overflow-x: auto;
  }
  table.table {
    min-width: 900px;
  }
  .dashboard-header {
    text-align: center;
    border-bottom: 2px solid #f1f3f4;
    padding-bottom: 1.5rem;
  }
  .dashboard-title {
    color: #2c3e50;
    font-weight: 600;
    font-size: 2.2rem;
    margin-bottom: 0.5rem;
  }
  .dashboard-subtitle {
    font-size: 1rem;
    color: #6c757d;
  }
  .dashboard-actions {
    margin-top: 2rem;
  }
  .status-btn {
    position: relative;
    padding: 1.5rem 2rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
  }
  .status-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
  }
  .btn-count {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    border-radius: 50%;
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
  }
  .btn-pending {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    border: 1px solid #0a58ca;
  }
  .btn-pending:hover {
    background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
  }
  .btn-delivered {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: white;
    border: 1px solid #219653;
  }
  .btn-delivered:hover {
    background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
  }
  .modal-content {
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  }
  .modal-header {
    background-color: #212529;
    color: white;
  }
  .modal-icon {
    font-size: 1.2rem;
  }
  .badge-pending {
    background-color: #0d6efd;
    color: white;
  }
  .badge-delivered {
    background-color: #2ecc71;
    color: white;
  }
  .footer {
    position: fixed;
    margin-top: 40px;
    background-color: #212529;
    padding: 10px 20px;
    border-top: 1px solid #444;
    font-size: 0.875rem;
    color: #adb5bd;
  }
  .footer a {
    color: #adb5bd;
    text-decoration: none;
  }
  .footer a:hover {
    color: #fff;
  }
  /* Responsive styles */
  @media (max-width: 900px) {
    .main-content .card {
      max-width: 100% !important;
      padding: 10px !important;
    }
    .dashboard-title {
      font-size: 1.5rem;
    }
    .dashboard-header {
      padding-bottom: 1rem;
    }
  }
  @media (max-width: 600px) {
    .main-content {
      margin-top: 30px !important;
    }
    .dashboard-title {
      font-size: 1.1rem;
    }
    .dashboard-subtitle {
      font-size: 0.9rem;
    }
    .dashboard-header {
      padding-bottom: 0.5rem;
    }
    .status-btn {
      font-size: 0.9rem;
      padding: 0.7rem 0.5rem;
      gap: 0.3rem;
    }
    .btn-count {
      width: 20px;
      height: 20px;
      font-size: 0.7rem;
      top: 6px;
      right: 6px;
    }
    .table-responsive {
      overflow-x: auto;
    }
    table.table {
      font-size: 12px;
      min-width: 600px;
    }
    .modal-content {
      padding: 5px;
    }
  }
  <style>
  /* Add these styles to your existing CSS */
  @media (max-width: 600px) {
    .main-content {
      padding: 15px !important;
      margin-top: 20px !important;
    }
    
    .dashboard-header {
      text-align: center;
      padding-bottom: 1rem;
    }
    
    .dashboard-title {
      font-size: 1.5rem;
      text-align: center;
    }
    
    .dashboard-subtitle {
      text-align: center;
    }
    
    .dashboard-actions .row {
      justify-content: center;
    }
    
    .dashboard-actions .col-md-6 {
      flex: 0 0 90%;
      max-width: 90%;
      margin-bottom: 15px;
    }
    
    .status-btn {
      width: 100%;
      padding: 1rem;
      font-size: 1rem;
      flex-direction: column;
      gap: 5px;
    }
    
    .status-btn i {
      font-size: 1.5rem;
      margin-bottom: 5px;
    }
    
    .btn-count {
      position: static;
      margin-top: 5px;
      width: auto;
      height: auto;
      background: transparent;
      color: white;
      font-size: 1rem;
    }
    
    #pendingSection, #deliveredSection {
      padding: 0 10px;
    }
    
    h4 {
      text-align: center;
      margin-bottom: 1rem !important;
    }
    
    .table-responsive {
      border: 1px solid #dee2e6;
      border-radius: 5px;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    
    table.table {
      min-width: 100%;
      font-size: 14px;
    }
    
    table.table th,
    table.table td {
      padding: 8px;
      text-align: center;
    }
    
    .form-control {
      width: 100%;
      text-align: center;
    }
    
    .text-end {
      text-align: center !important;
    }
    
    .alert {
      text-align: center;
    }
  }
</style>
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="../js/scripts.js"></script>

<script>
  // Toggle Pending/Delivered tables so only one is visible at a time
const pendingBtn = document.getElementById('pendingBtn');
const deliveredBtn = document.getElementById('deliveredBtn');
const pendingSection = document.getElementById('pendingSection');
const deliveredSection = document.getElementById('deliveredSection');

pendingBtn.addEventListener('click', function() {
  pendingSection.style.display = '';
  deliveredSection.style.display = 'none';
});
deliveredBtn.addEventListener('click', function() {
  deliveredSection.style.display = '';
  pendingSection.style.display = 'none';
});
</script>
</body>
</html>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['collect_amount'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['collect_amount'])) {
      include_once '../db_connect.php';
      $successMsg = $errorMsg = '';
      $deliveryIds = [];
      $res = $mysqli->query("SELECT delivery_id, order_id FROM delivery_orders WHERE status = 'Assigned' OR status = 'Partially Paid'");
      while ($drow = $res->fetch_assoc()) {
        $deliveryIds[$drow['order_id']] = $drow['delivery_id'];
      }
      foreach ($_POST['collect_amount'] as $orderId => $amount) {
        $amount = floatval($amount);
        if ($amount > 0 && isset($deliveryIds[$orderId])) {
          $deliveryId = $deliveryIds[$orderId];
          // Insert payment
          $stmt = $mysqli->prepare("INSERT INTO delivery_payments (delivery_id, amount_paid, remarks) VALUES (?, ?, ?)");
          $empty = '';
          $stmt->bind_param('ids', $deliveryId, $amount, $empty);
          $stmt->execute();
          // Update delivery_orders: Only update amount_paid, amount_remaining, and status to 'Partially Paid' if not fully paid
          $mysqli->query("UPDATE delivery_orders SET amount_paid = amount_paid + $amount, amount_remaining = GREATEST(amount_remaining - $amount, 0), status = IF(amount_remaining - $amount <= 0, status, 'Partially Paid') WHERE delivery_id = $deliveryId");
          $successMsg = 'Collection updated successfully.';
        }
      }
      if ($successMsg) {
        echo '<div class="alert alert-success">'.$successMsg.'</div>';
        // Add this to reload the page after a short delay
        echo '<script>setTimeout(function(){ window.location.reload(); }, 1200);</script>';
      }
      if ($errorMsg) echo '<div class="alert alert-danger">'.$errorMsg.'</div>';
    }
  }
  if (isset($_POST['mark_delivered']) && is_numeric($_POST['mark_delivered'])) {
    $deliveryId = (int)$_POST['mark_delivered'];
    $mysqli->query("UPDATE delivery_orders SET item_delivered = 1 WHERE delivery_id = $deliveryId");
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
  }
}