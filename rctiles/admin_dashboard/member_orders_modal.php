<?php
/* returns a modal body listing THIS rider’s open orders */
session_start();
include "../db_connect.php";

$uid = (int)($_POST['user_id'] ?? 0);

$q = $mysqli->prepare(
   "SELECT d.delivery_id, d.order_id, o.order_date,
           c.name customer, d.status,
           d.amount_paid, d.amount_remaining
    FROM   delivery_orders d
    JOIN   orders      o ON o.order_id=d.order_id
    JOIN   customers   c ON c.customer_id=o.customer_id
    WHERE  d.delivery_user_id = ?
    ORDER  BY d.delivery_id DESC");
$q->bind_param("i",$uid);
$q->execute();
$res = $q->get_result();
$orders = $res->fetch_all(MYSQLI_ASSOC);
?>
<!-- Modal Header -->
<div class="modal-header bg-primary text-white">
    <h5 class="modal-title">Rider Orders Details</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<!-- Modal Body -->
<div class="modal-body">
    <div class="table-responsive">
      <?php if(empty($orders)): ?>
        <div class="alert alert-info mb-0">No orders found for this rider.</div>
      <?php else: ?>
      <table class="table table-striped table-bordered">
          <thead class="table-dark">
              <tr>
                 
                  <th>Delivery ID</th>
                  <th>Order ID</th>
                  <th>Date</th>
                  <th>Customer</th>
                  <th>Amount Paid (₹)</th>
                  <th>Due (₹)</th>
                  <th>Status</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach($orders as $i=>$order): ?>
              <tr>
                 
                  <td><?= htmlspecialchars($order['delivery_id']) ?></td>
                  <td><?= htmlspecialchars($order['order_id']) ?></td>
                  <td><?= date('d-m-Y', strtotime($order['order_date'])) ?></td>
                  <td><?= htmlspecialchars($order['customer']) ?></td>
                  <td><?= number_format($order['amount_paid'],2) ?></td>
                  <td><?= number_format($order['amount_remaining'],2) ?></td>
                  <td>
                      <span class="badge bg-<?= $order['status']=='Completed'?'success':'warning' ?>">
                          <?= htmlspecialchars($order['status']) ?>
                      </span>
                  </td>
              </tr>
              <?php endforeach; ?>
          </tbody>
      </table>
      <?php endif; ?>
    </div>
</div>
