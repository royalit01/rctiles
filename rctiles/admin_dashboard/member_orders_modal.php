<?php
/* returns a modal body listing THIS rider’s open orders */
session_start();
include "../db_connect.php";

$uid = (int)($_POST['user_id'] ?? 0);

$q = $mysqli->prepare(
   "SELECT d.delivery_id, d.order_id,
           c.name customer, d.status,
           d.amount_paid, d.amount_remaining
    FROM   delivery_orders d
    JOIN   orders      o ON o.order_id=d.order_id
    JOIN   customers   c ON c.customer_id=o.customer_id
    WHERE  d.delivery_user_id = ?
    ORDER  BY d.delivery_id DESC");
$q->bind_param("i",$uid);
$q->execute();
$res=$q->get_result();
?>
<div class="modal-header">
   <h5 class="modal-title">Orders for rider #<?= $uid ?></h5>
   <button class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body p-0">
 <div class="table-responsive">
  <table class="table table-sm table-bordered mb-0">
   <thead class="table-light">
     <tr>
      <th>Delivery&nbsp;ID</th><th>Order&nbsp;ID</th><th>Customer</th>
      <th>Status</th><th class="text-end">Paid</th><th class="text-end">Due</th>
     </tr>
   </thead>
   <tbody>
   <?php while($o=$res->fetch_assoc()): ?>
     <tr>
       <td><?= $o['delivery_id'] ?></td>
       <td><?= $o['order_id'] ?></td>
       <td><?= htmlspecialchars($o['customer']) ?></td>
       <td><?= $o['status'] ?></td>
       <td class="text-end"><?= number_format($o['amount_paid'],2) ?></td>
       <td class="text-end"><?= number_format($o['amount_remaining'],2) ?></td>
     </tr>
   <?php endwhile; ?>
   </tbody>
  </table>
 </div>
</div>
