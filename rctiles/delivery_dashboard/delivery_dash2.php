<?php
// --- POST processing logic must come first to allow header() redirect ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_delivered'])) {
  include_once '../db_connect.php';
  $deliveryId = (int)$_POST['mark_delivered'];
  $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
  $amount = isset($_POST['collect_amount']) ? floatval($_POST['collect_amount']) : 0;
  if ($amount > 0 && $orderId > 0) {
    $stmt = $mysqli->prepare("INSERT INTO delivery_payments (delivery_id, amount_paid, remarks) VALUES (?, ?, ?)");
    $empty = '';
    $stmt->bind_param('ids', $deliveryId, $amount, $empty);
    $stmt->execute();
    $mysqli->query("UPDATE delivery_orders SET amount_paid = amount_paid + $amount, amount_remaining = GREATEST(amount_remaining - $amount, 0), status = IF(amount_remaining - $amount <= 0, status, 'Partially Paid') WHERE delivery_id = $deliveryId");
  }
  $row = $mysqli->query("SELECT amount_remaining FROM delivery_orders WHERE delivery_id = $deliveryId")->fetch_assoc();
  if ($row && floatval($row['amount_remaining']) <= 0) {
    $mysqli->query("UPDATE delivery_orders SET item_delivered = 1, status = 'Completed' WHERE delivery_id = $deliveryId");
  } else {
    $mysqli->query("UPDATE delivery_orders SET item_delivered = 1 WHERE delivery_id = $deliveryId");
  }
  header("Location: " . $_SERVER['REQUEST_URI']);
  exit;
}

include "delivery_header.php"; 
include "../db_connect.php";

$pendingOrders = $mysqli->query("SELECT o.order_id, c.name, c.phone_no, c.address, o.final_amount AS total_amount, o.rent_amount, do.delivery_id, do.amount_remaining, do.amount_paid FROM delivery_orders do JOIN orders o ON do.order_id = o.order_id JOIN customers c ON o.customer_id = c.customer_id WHERE (do.status = 'Assigned' OR do.status = 'Partially Paid') AND do.item_delivered = 0");
$deliveredOrders = $mysqli->query("SELECT o.order_id, c.name, c.phone_no, c.address FROM delivery_orders do JOIN orders o ON do.order_id = o.order_id JOIN customers c ON o.customer_id = c.customer_id WHERE  do.item_delivered = 1");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Delivery Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    


    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .status-btn {
    width: 100%;
    min-width: 250px;
    height: 120px;
    font-size: 1.4rem;
    padding: 1.5rem 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    border-radius: 12px !important;
    position: relative;
    margin: 0 auto;
    margin-right: 45px;
}
        .btn-count {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }
        .btn-pending {
            background-color:rgb(247, 227, 162);
            color: #664d03;
            border-color: #ffecb5;
        }
        .btn-delivered {
            background-color: #d1e7dd;
            color: #0a3622;
            border-color: #badbcc;
        }
        .badge-pending {
            background-color: #fff3cd;
            color: #664d03;
        }
        .badge-delivered {
            background-color: #d1e7dd;
            color: #0a3622;
        }
        .dashboard-title {
            text-align: center;
            margin-bottom: 10px;
        }
        .dashboard-subtitle {
            text-align: center;
            color: #6c757d;
            margin-bottom: 30px;
        }
        .dashboard-actions {
            margin-bottom: 30px;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .no-orders {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .modal-body .table-responsive {
    max-height: 60vh;
    overflow-y: auto;
    overflow-x: auto;
}
    </style>
</head>
<body>
<div class="dashboard-container">
    <div class="dashboard-header mb-4">
        <h2 class="dashboard-title">Delivery Dashboard</h2>
        <p class="dashboard-subtitle">Manage your delivery operations efficiently</p>
    </div>
    
    <div class="dashboard-actions">
        <div class="row g-3 justify-content-center">
            <div class="col-md-5">
                <button class="btn btn-pending status-btn w-100" data-bs-toggle="modal" data-bs-target="#pendingModal">
                    <i class="fas fa-clock"></i>
                    <span>PENDING DELIVERIES</span>
                    <div class="btn-count"><?php echo $pendingOrders ? $pendingOrders->num_rows : 0; ?></div>
                </button>
            </div>
            <div class="col-md-5">
                <button class="btn btn-delivered status-btn w-100" data-bs-toggle="modal" data-bs-target="#deliveredModal">
                    <i class="fas fa-check-circle"></i>
                    <span>DELIVERED ORDERS</span>
                    <div class="btn-count"><?php echo $deliveredOrders ? $deliveredOrders->num_rows : 0; ?></div>
                </button>
            </div>
        </div>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['collect_amount'])) {
      include_once '../db_connect.php';
      $successMsg = $errorMsg = '';
      $deliveryIds = [];
      $res = $mysqli->query("SELECT delivery_id, order_id FROM delivery_orders WHERE status = 'Assigned' OR status = 'Partially Paid'");
      while ($drow = $res->fetch_assoc()) {
        $deliveryIds[$drow['order_id']] = $drow['delivery_id'];
      }
      // foreach ($_POST['collect_amount'] as $orderId => $amount) {
      //   $amount = floatval($amount);
      //   if ($amount > 0 && isset($deliveryIds[$orderId])) {
      //     $deliveryId = $deliveryIds[$orderId];
      //     // Insert payment
      //     $stmt = $mysqli->prepare("INSERT INTO delivery_payments (delivery_id, amount_paid, remarks) VALUES (?, ?, ?)");
      //     $empty = '';
      //     $stmt->bind_param('ids', $deliveryId, $amount, $empty);
      //     $stmt->execute();
      //     // Update delivery_orders: Only update amount_paid, amount_remaining, and status to 'Partially Paid' if not fully paid
      //     $mysqli->query("UPDATE delivery_orders SET amount_paid = amount_paid + $amount, amount_remaining = GREATEST(amount_remaining - $amount, 0), status = IF(amount_remaining - $amount <= 0, status, 'Partially Paid') WHERE delivery_id = $deliveryId");
      //     $successMsg = 'Collection updated successfully.';
      //   }
      // }
      if ($successMsg) {
        echo '<div class="alert alert-success">'.$successMsg.'</div>';
        // Removed auto reload
        // echo '<script>setTimeout(function(){ window.location.reload(); }, 1200);</script>';
      }
      if ($errorMsg) echo '<div class="alert alert-danger">'.$errorMsg.'</div>';
    }
    
    ?>
</div>

<!-- Pending Deliveries Modal -->
<div class="modal fade" id="pendingModal" tabindex="-1" aria-labelledby="pendingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title" id="pendingModalLabel">
                    <i class="fas fa-clock me-2"></i>
                    Pending Deliveries
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($pendingOrders && $pendingOrders->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-warning">
    <tr>
        <th>Order ID</th>
        <th>Name</th>
        <th>Mobile</th>
        <th>Address</th>
        <th>Storage Area</th>
        <th>Products</th>
        <th>Total Amount</th>
        <th>Freight</th>
        <th>Amount Details</th>
        
        <th>Collect Amount</th>
        <th>Action</th>
    </tr>
</thead>
<tbody>
    <?php while ($row = $pendingOrders->fetch_assoc()): ?>
    <?php
    // Ensure rent_amount and total_amount are always set and numeric
    $rent_amount = isset($row['rent_amount']) ? (float)$row['rent_amount'] : 0;
    $total_amount = isset($row['total_amount']) ? (float)$row['total_amount'] : 0;
    
    $deliveryIdRes = $mysqli->query("SELECT delivery_id, amount_paid, amount_remaining FROM delivery_orders WHERE order_id = ".(int)$row['order_id']." AND status = 'Assigned' LIMIT 1");
    $deliveryData = $deliveryIdRes ? $deliveryIdRes->fetch_assoc() : null;
    $paid = $deliveryData ? $deliveryData['amount_paid'] : 0;
    $remaining = $row['amount_remaining']+ 0;
    
    $storageAreaRes = $mysqli->query("SELECT storage_area_id, product_id FROM minus_stock WHERE order_id = ".(int)$row['order_id']);
    $storageAreaIds = [];
    $productInfo = [];
    if ($storageAreaRes && $storageAreaRes->num_rows > 0) {
        while ($saRow = $storageAreaRes->fetch_assoc()) {
            $storageAreaIds[] = $saRow['storage_area_id'];
            $pid = (int)$saRow['product_id'];
            $pname = '-';
            $qtyOrdered = '-';
            $productRes = $mysqli->query("SELECT product_name FROM products WHERE product_id = $pid LIMIT 1");
            if ($productRes && $productRes->num_rows > 0) {
                $pname = $productRes->fetch_assoc()['product_name'];
            }
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
    ?>
    <tr>
        <td><?= htmlspecialchars($row['order_id']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['phone_no']) ?></td>
        <td><?= htmlspecialchars($row['address']) ?></td>
        <td><?= $storageAreaIdDisplay ?></td>
        <td><?= $productInfoDisplay ?></td>
        <td>₹<?= number_format(isset($row['total_amount']) ? (float)$row['total_amount'] : 0, 2) ?></td>
                <td>₹<?= number_format((float)$row['rent_amount'], 2) ?></td>

        <td>
            <div class="border p-2 rounded">
                <?php 
                $total_amount = isset($row['total_amount']) && is_numeric($row['total_amount']) ? (float)$row['total_amount'] : 0;
                $rent_amount = isset($row['rent_amount']) && is_numeric($row['rent_amount']) ? (float)$row['rent_amount'] : 0;
                $grand = $total_amount + $rent_amount;
                ?>
                <strong>Total: ₹<?= number_format($grand, 2) ?></strong><br>
                <span class="text-success">Paid: ₹<?= number_format($row['amount_paid'], 2) ?></span><br>
                <span class="<?= ($row['amount_remaining'] > 0 ? 'text-danger' : 'text-muted') ?>">Remaining: ₹<?= number_format($row['amount_remaining'], 2) ?></span>
            </div>
        </td>
        <td colspan="2">
            <form method="post" class="d-flex gap-2 align-items-center">
                <input type="number" min="0" max="<?= htmlspecialchars($remaining) ?>" step="0.01" 
                       name="collect_amount" 
                       class="form-control form-control-sm" placeholder="Enter amount">
                <button type="submit" name="mark_delivered" value="<?= htmlspecialchars($row['delivery_id']) ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-check"></i> Collect & Deliver
                </button>
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['order_id']) ?>">
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                        <h5>No pending deliveries</h5>
                        <p class="text-muted">All orders have been delivered</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delivered Orders Modal -->
<div class="modal fade" id="deliveredModal" tabindex="-1" aria-labelledby="deliveredModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success bg-opacity-10">
                <h5 class="modal-title" id="deliveredModalLabel">
                    <i class="fas fa-check-circle me-2"></i>
                    Delivered Orders
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($deliveredOrders && $deliveredOrders->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-success">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Address</th>
                                    <th>Grand Total</th>
                                    <th>Paid</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $deliveredOrders->fetch_assoc()): ?>
                                    <?php
                                    $deliveryIdRes = $mysqli->query("SELECT delivery_id, amount_paid, amount_remaining, rent FROM delivery_orders WHERE order_id = ".(int)$row['order_id']);
                                    $deliveryData = $deliveryIdRes->fetch_assoc();
                                    $paid = $deliveryData ? $deliveryData['amount_paid'] : 0;
                                    $rent = $deliveryData ? $deliveryData['rent'] : 0;
                                    $remaining = $deliveryData ? $deliveryData['amount_remaining'] : 0;
                                    $orderTotalRes = $mysqli->query("SELECT final_amount FROM orders WHERE order_id = ".(int)$row['order_id']);
                                    $orderTotalData = $orderTotalRes ? $orderTotalRes->fetch_assoc() : null;
                                    $totalAmount = $orderTotalData ? $orderTotalData['final_amount'] : 0;
                                    $grand = (float)$totalAmount + (float)$rent;
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['order_id']) ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['phone_no']) ?></td>
                                        <td><?= htmlspecialchars($row['address']) ?></td>
                                        <td>₹<?= number_format($grand, 2) ?></td>
                                        <td>₹<?= number_format($paid, 2) ?></td>
                                        <td>₹<?= number_format($remaining, 2) ?></td>
                                        <td><span class="badge bg-success">Delivered</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-orders">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <h5>No delivered orders</h5>
                        <p class="text-muted">No orders have been marked as delivered yet</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../js/scripts.js"></script>
    

<script>
    // Handle form submissions
    document.addEventListener('DOMContentLoaded', function() {
        // Show success message if present
        const successMsg = document.querySelector('.alert-success');
        if (successMsg) {
            setTimeout(() => {
                successMsg.remove();
            }, 3000);
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidenav = document.getElementById('layoutSidenav_nav');
    if (sidebarToggle && sidenav) {
        sidebarToggle.addEventListener('click', function() {
            sidenav.classList.toggle('active');
        });
    }
});
</script>
</body>
</html>