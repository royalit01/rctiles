<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}

require_once "../db_connect.php";

if (!isset($_SESSION['user_id'])  ){header("Location: ../login.php");exit;}
$uid = (int)$_SESSION['user_id'];

$res = $mysqli->query("
   SELECT ri.payment_date, ri.amount, ri.remarks,
          d.order_id
   FROM   rider_income ri
   LEFT JOIN delivery_orders d ON d.delivery_id = ri.delivery_id
   WHERE  ri.user_id = $uid
   ORDER  BY ri.payment_date DESC");

$total = 0;
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Income</title>
<link href="../css/styles.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="sb-nav-fixed">
<?php include "delivery_header.php"; ?>
<div id="layoutSidenav_content"><main class="container-fluid px-4 py-4">
<h2 class="mb-4">Income Received</h2>
<table class="table table-bordered"><thead class="table-dark">
 <tr><th>Date</th><th>Order</th><th>Amount&nbsp;(₹)</th><th>Remarks</th></tr></thead><tbody>
<?php while($r=$res->fetch_assoc()): $total+=$r['amount']; ?>
 <tr>
   <td><?= date('d-M-Y H:i', strtotime($r['payment_date'])) ?></td>
   <td><?= $r['order_id'] ?? '-' ?></td>
   <td class="text-end"><?= number_format($r['amount'],2) ?></td>
   <td><?= htmlspecialchars($r['remarks']) ?></td>
 </tr>
<?php endwhile; ?>
</tbody>
<tfoot><tr class="fw-semibold table-light">
 <td colspan="2" class="text-end">TOTAL</td>
 <td class="text-end"><?= number_format($total,2) ?></td><td></td>
</tr></tfoot>
</table>
</main></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
