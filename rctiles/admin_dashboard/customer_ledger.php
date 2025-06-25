<?php
/* customer_ledger.php – Admin view */

session_start();
include "../db_connect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php"); exit;
}

/* -------- read date filters -------- */
$from = $_GET['from'] ?? '';
$to   = $_GET['to']   ?? '';

$where = [];
if ($from) $where[] = "o.order_date >= '$from'";
if ($to)   $where[] = "o.order_date <= '$to'";
$whereSQL = $where ? 'AND '.implode(' AND ', $where) : '';

/* -------- per-customer ledger -------- */
$sql = "
 SELECT c.customer_id,
        c.name                                       AS customer,
        SUM(d.amount_paid + d.amount_remaining)      AS total_due,
        SUM(d.amount_paid)                           AS paid,
        SUM(d.amount_remaining)                      AS pending,
        GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ', ') AS riders
 FROM   customers        c
 JOIN   orders           o ON o.customer_id = c.customer_id
 JOIN   delivery_orders  d ON d.order_id    = o.order_id
 JOIN   users            u ON u.user_id     = d.delivery_user_id
 WHERE  1 $whereSQL
 GROUP  BY c.customer_id
 ORDER  BY c.name";
$res = $mysqli->query($sql);

/* aggregate totals */
$sumTotal = $sumPaid = $sumPend = 0;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Customer Ledger</title>

<link href="../css/styles.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* ---------- mobile card layout (< 576 px) ---------- */
@media (max-width:575.98px){
  table{border:0;}
  table thead{display:none;}
  table tbody tr{display:block;margin-bottom:1rem;
                 border:1px solid #dee2e6;border-radius:.5rem;
                 background:#fff;box-shadow:0 .25rem .5rem rgba(0,0,0,.05);}
  table tbody td{display:flex;justify-content:space-between;
                 padding:.65rem 1rem;font-size:.95rem;border-top:1px solid #dee2e6}
  table tbody td:first-child{border-top:0;}
  table tbody td::before{
        content:attr(data-label);
        font-weight:600;color:#6c757d;margin-right:.5rem;
  }
}
</style>
</head>
<body class="sb-nav-fixed">
<?php include "admin_header.php"; ?>

<div id="layoutSidenav_content">
<main class="card border-0 shadow rounded-3 p-4 bg-white mx-auto" style="max-width:100%;width:100%;">

<h2>Customer Cash Ledger</h2>

<!-- date filter -->
<form class="row g-2 mb-4" method="get">
  <div class="col-auto">
      <input type="date" name="from" class="form-control"
             value="<?= htmlspecialchars($from) ?>">
  </div>
  <div class="col-auto">
      <input type="date" name="to" class="form-control"
             value="<?= htmlspecialchars($to) ?>">
  </div>
  <div class="col-auto">
      <button class="btn btn-outline-secondary">Filter</button>
  </div>
</form>

<div class="table-responsive">
<table class="table table-bordered align-middle">
 <thead class="table-dark">
   <tr>
     <th>#</th>
     <th>Customer</th>
     <th class="text-end">Total&nbsp;(₹)</th>
     <th class="text-end">Paid&nbsp;(₹)</th>
     <th class="text-end">Pending&nbsp;(₹)</th>
     <th>Delivery&nbsp;Person(s)</th>
   </tr>
 </thead>
 <tbody>
 <?php $i=1; while($c=$res->fetch_assoc()):
        $sumTotal += $c['total_due'];
        $sumPaid  += $c['paid'];
        $sumPend  += $c['pending']; ?>
   <tr>
     <td data-label="#"><?= $i++ ?></td>

     <td data-label="Customer"><?= htmlspecialchars($c['customer']) ?></td>

     <td data-label="Total (₹)"  class="text-end">
        <?= number_format($c['total_due'],2) ?>
     </td>

     <td data-label="Paid (₹)"   class="text-end">
        <?= number_format($c['paid'],2) ?>
     </td>

     <td data-label="Pending (₹)" class="text-end">
        <?php if ($c['pending']>0): ?>
           <span class="badge bg-warning text-dark">
               <?= number_format($c['pending'],2) ?>
           </span>
        <?php else: ?>
           <span class="badge bg-success">0.00</span>
        <?php endif; ?>
     </td>

     <td data-label="Delivery Person(s)">
        <?= htmlspecialchars($c['riders']) ?>
     </td>
   </tr>
 <?php endwhile; ?>
 </tbody>

 <!-- summary row -->
 <tfoot>
   <tr class="fw-semibold table-light">
     <td colspan="2" class="text-end">TOTAL</td>
     <td class="text-end"><?= number_format($sumTotal,2) ?></td>
     <td class="text-end"><?= number_format($sumPaid,2) ?></td>
     <td class="text-end"><?= number_format($sumPend,2) ?></td>
     <td></td>
   </tr>
 </tfoot>
</table>
</div>

<!-- total income banner -->
<div class="alert alert-primary mt-4" role="alert">
  <strong>Total income:</strong> ₹ <?= number_format($sumPaid,2) ?>
  <?php if ($sumPend>0): ?>
       | <strong>Still pending:</strong> ₹ <?= number_format($sumPend,2) ?>
  <?php endif; ?>
</div>

</main>
</div><!-- /layout -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
