<?php
session_start();
include "delivery_header.php"; 

include "../db_connect.php";

$pendingOrders = $mysqli->query("SELECT o.order_id, c.name, c.phone_no, c.address, o.final_amount AS total_amount, o.rent_amount FROM delivery_orders do JOIN orders o ON do.order_id = o.order_id JOIN customers c ON o.customer_id = c.customer_id WHERE do.status = 'Assigned'");
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

</head>
<body class="sb-nav-fixed">
<div id="layoutSidenav_content">
<main class="main-content ">
                        <div class="card border-0 shadow rounded-3 p-4 bg-white mx-auto " style="max-width: 800px;">

 
          <div class="dashboard-header mb-4">
            <h2 class="dashboard-title">Delivery Dashboard</h2>
            <p class="dashboard-subtitle">Manage your delivery operations efficiently</p>
          </div>
          <div class="dashboard-actions">
            <div class="row g-3">
              <div class="col-md-6">
                <button class="btn btn-pending status-btn w-100" id="pendingBtn" data-status="pending">
                  <i class="fas fa-clock"></i>
                  <span>PENDING</span>
                  <div class="btn-count"><?php echo $pendingOrders ? $pendingOrders->num_rows : 0; ?></div>
                </button>
              </div>
              <div class="col-md-6">
                <button class="btn btn-delivered status-btn w-100" id="deliveredBtn" data-status="delivered">
                  <i class="fas fa-check-circle"></i>
                  <span>DELIVERED</span>
                  <div class="btn-count"><?php echo $deliveredOrders ? $deliveredOrders->num_rows : 0; ?></div>
                </button>
              </div>
            </div>
          </div>

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
        <button type="button" class="btn btn-primary" id="actionBtn">Take Action</button>
      </div>
    </div>
  </div>
</div>



<!-- Hidden dynamic order lists -->
<div id="pendingOrdersList" style="display:none;">
  <?php
  if ($pendingOrders && $pendingOrders->num_rows > 0) {
    echo '<form id="collectAmountForm"><div class="table-responsive"><table class="table table-bordered align-middle mb-0">';
    echo '<thead class="table-primary"><tr><th>Order ID</th><th>Name</th><th>Mobile</th><th>Address</th><th>Total Amount</th><th>Rent</th><th>Status</th><th>Collect Amount</th></tr></thead><tbody>';
    while ($row = $pendingOrders->fetch_assoc()) {
      echo '<tr>'
        .'<td>'.htmlspecialchars($row['order_id']).'</td>'
        .'<td>'.htmlspecialchars($row['name']).'</td>'
        .'<td>'.htmlspecialchars($row['phone_no']).'</td>'
        .'<td>'.htmlspecialchars($row['address']).'</td>'
        .'<td>₹'.number_format((float)$row['total_amount'],2).'</td>'
        .'<td>₹'.number_format((float)$row['rent_amount'],2).'</td>'
        .'<td><span class="badge badge-pending">Pending</span></td>'
        .'<td><input type="number" min="0" step="0.01" name="collect_amount['.htmlspecialchars($row['order_id']).']" class="form-control form-control-sm" placeholder="Enter amount"></td>'
        .'</tr>';
    }
    echo '</tbody></table></div>';
    echo '<div class="mt-3 text-end"><button type="submit" class="btn btn-success">Submit Collection</button></div></form>';
  } else {
    echo '<div class="list-group-item">No pending deliveries.</div>';
  }
  ?>
</div>
<div id="deliveredOrdersList" style="display:none;">
  <?php
  if ($deliveredOrders && $deliveredOrders->num_rows > 0) {
    echo '<div class="table-responsive"><table class="table table-bordered align-middle mb-0">';
    echo '<thead class="table-success"><tr><th>Order ID</th><th>Name</th><th>Mobile</th><th>Address</th><th>Status</th></tr></thead><tbody>';
    while ($row = $deliveredOrders->fetch_assoc()) {
      echo '<tr>'
        .'<td>'.htmlspecialchars($row['order_id']).'</td>'
        .'<td>'.htmlspecialchars($row['name']).'</td>'
        .'<td>'.htmlspecialchars($row['phone_no']).'</td>'
        .'<td>'.htmlspecialchars($row['address']).'</td>'
        .'<td><span class="badge badge-delivered">Delivered</span></td>'
        .'</tr>';
    }
    echo '</tbody></table></div>';
  } else {
    echo '<div class="list-group-item">No delivered orders.</div>';
  }
  ?>
</div>

<!-- Styles for dashboard page -->
<style>
  html, body {
    overflow: hidden;
    height: 100%;
  }
  body {
    background-color: #f8f9fa;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  }
  .main-content {
    margin-top: 70px !important;
    /* padding-top: 0; */
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
    /* Remove fixed positioning for scrollable content */
    position: fixed;
    margin-top: 40px;
    background-color: #212529;
    padding: 10px 20px;
    border-top: 1px solid #444;
    font-size: 0.875rem;
    color: #adb5bd;
    /* z-index: 1050; */
  }
  .footer a {
    color: #adb5bd;
    text-decoration: none;
  }
  .footer a:hover {
    color: #fff;
  }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('pendingBtn').addEventListener('click', () => showStatusModal('pending'));
  document.getElementById('deliveredBtn').addEventListener('click', () => showStatusModal('delivered'));

  function showStatusModal(status) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    const modalIcon = document.querySelector('.modal-icon');
    const actionBtn = document.getElementById('actionBtn');

    if (status === 'pending') {
      modalTitle.textContent = 'Pending Deliveries';
      modalIcon.className = 'fas fa-clock modal-icon text-primary';
      modalContent.innerHTML = document.getElementById('pendingOrdersList').innerHTML;
      actionBtn.textContent = 'Process Orders';
      actionBtn.className = 'btn btn-primary';
    } else {
      modalTitle.textContent = 'Delivered Orders';
      modalIcon.className = 'fas fa-check-circle modal-icon text-success';
      modalContent.innerHTML = document.getElementById('deliveredOrdersList').innerHTML;
      actionBtn.textContent = 'View Reports';
      actionBtn.className = 'btn btn-success';
    }

    modal.show();
  }

  document.getElementById('actionBtn').addEventListener('click', () => {
  const modalTitle = document.getElementById('modalTitle').textContent;
  if (modalTitle.includes('Pending')) {
    window.location.href = "../admin_dashboard/assign_delivery.php";
  } else {
    window.location.href = "../admin_dashboard/assign_delivery.php";
  }
});
</script>
</body>
</html>