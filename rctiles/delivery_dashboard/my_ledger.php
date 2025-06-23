<?php
/* -----------------------------------------------------------
   my_ledger.php  –  Delivery user sees their own cash ledger
----------------------------------------------------------- */

session_start();
include "../db_connect.php";

/* 1.  allow only role_id = 4  (delivery user) */
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    header("Location: ../login.php");
    exit;
}
$uid = (int)$_SESSION['user_id'];

/* ---------- summary figures ---------- */
$collected = $mysqli->query(
    "SELECT SUM(amount_paid)
     FROM   delivery_orders
     WHERE  delivery_user_id = $uid")->fetch_column() ?? 0;

$adminTaken = $mysqli->query(
    "SELECT SUM(amount)
     FROM   admin_cash_collection
     WHERE  user_id = $uid")->fetch_column() ?? 0;

$due = $collected - $adminTaken;

/* ---------- history of admin collections ---------- */
$history = $mysqli->query(
   "SELECT amount, collected_at, notes
    FROM   admin_cash_collection
    WHERE  user_id = $uid
    ORDER  BY collected_at DESC");
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Ledger</title>

<link href="../css/styles.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
@media (max-width:575.98px){
 table thead{display:none}
 table tbody tr{display:block;margin-bottom:1rem;border:1px solid #dee2e6;border-radius:.5rem}
 table tbody td{display:flex;justify-content:space-between;padding:.55rem .9rem;font-size:1rem}
 table tbody td:first-child{font-weight:600}
}
</style>
</head>
<body class="sb-nav-fixed">
<?php include "delivery_header.php"; ?>

<div id="layoutSidenav_content">
<main class="container-fluid px-4 py-4">

<h2 class="mb-4">My Cash Ledger</h2>

<div class="table-responsive">
<table class="table table-bordered align-middle">
 <thead class="table-dark">
  <tr>
    <th>Total&nbsp;collected&nbsp;(₹)</th>
    <th>Handed&nbsp;to&nbsp;admin&nbsp;(₹)</th>
    <th>Still&nbsp;with&nbsp;me&nbsp;(₹)</th>
  </tr>
 </thead>
 <tbody>
  <tr>
    <td><?= number_format($collected, 2) ?></td>
    <td><?= number_format($adminTaken, 2) ?></td>
    <td>
        <?php if ($due>0): ?>
            <span class="badge bg-warning text-dark"><?= number_format($due,2) ?></span>
        <?php else: ?>
            <span class="badge bg-success"><?= number_format($due,2) ?></span>
        <?php endif; ?>
    </td>
  </tr>
 </tbody>
</table>
</div>

<h4 class="mt-5 mb-3">Admin Collections&nbsp;History</h4>

<?php if ($history->num_rows): ?>
<div class="table-responsive">
<table class="table table-sm table-striped">
 <thead class="table-light">
  <tr>
    <th>#</th><th>Date / Time</th><th class="text-end">Amount&nbsp;(₹)</th><th>Notes</th>
  </tr>
 </thead>
 <tbody>
  <?php $i=1; while($h=$history->fetch_assoc()): ?>
    <tr>
      <td><?= $i++ ?></td>
      <td><?= date('d-M-Y H:i', strtotime($h['collected_at'])) ?></td>
      <td class="text-end"><?= number_format($h['amount'],2) ?></td>
      <td><?= htmlspecialchars($h['notes']) ?></td>
    </tr>
  <?php endwhile; ?>
 </tbody>
</table>
</div>
<?php else: ?>
   <p class="text-muted">No cash has been collected by the admin yet.</p>
<?php endif; ?>

</main>
</div><!-- /layout -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
