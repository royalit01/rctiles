<?php
session_start();
include "../db_connect.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php"); exit;
}

/* -------- read date filters -------- */
$from = $_GET['from'] ?? '';
$to   = $_GET['to']   ?? '';
$search = $_GET['search'] ?? '';

$where = [];
if ($from) $where[] = "o.order_date >= '$from'";
if ($to)   $where[] = "o.order_date <= '$to'";
if ($search) $where[] = "c.name LIKE '%$search%'";
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
<main >
<title>Customer Ledger</title>

<link href="../css/styles.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.3.0/css/all.css">
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
<style>
/* ---------- mobile card layout (< 576 px) ---------- */
@media (max-width:575.98px){
  body {
    background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
  }
  .card {
    border-radius: 1.1rem !important;
    box-shadow: 0 8px 32px rgba(13,110,253,0.10), 0 1.5px 8px rgba(0,0,0,0.04);
    padding: 1.1rem 0.1rem !important;
    margin: 0 !important;
    max-width: 100vw !important;
    width: 100vw !important;
  }
  h2 {
    text-align: center;
    font-size: 1.35rem;
    font-weight: 700;
    color: #2563eb;
    margin-bottom: 1.2rem;
    letter-spacing: 0.5px;
  }
  .table-responsive { box-shadow: none !important; }
  table { border: 0; width: 100%; background: #fff; }
  table thead { display: none; }
  table tfoot { display: none; }
  table tbody tr {
    display: block;
    margin-bottom: 1.1rem;
    border: 1px solid #e0e7ff;
    border-radius: .8rem;
    background: #fff;
    box-shadow: 0 2px 12px rgba(13,110,253,0.10);
    padding: 0.7rem 0.7rem 0.7rem 0.9rem;
    position: relative;
    overflow: hidden;
  }
  table tbody tr::before {
    content: '';
    display: block;
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 5px;
    background: linear-gradient(180deg,#2563eb 60%,#60a5fa 100%);
    border-radius: .8rem 0 0 .8rem;
  }
  table tbody td {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-start;
    padding: .55rem 0.2rem .55rem .7rem;
    font-size: 1.07rem;
    border: none !important;
    border-bottom: 1px solid #f1f3f4 !important;
    background: #fff;
    width: 100%;
    margin-bottom: 0.08rem;
    position: relative;
    min-width: 0;
    word-break: break-word;
  }
  table tbody td:last-child { border-bottom: none !important; }
  table tbody td:first-child { border-top: 0; }
  table tbody td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #2563eb;
    min-width: 120px;
    display: inline-block;
    font-size: 1.01rem;
    letter-spacing: 0.2px;
    font-family: 'Segoe UI',sans-serif;
    margin-right: 0.5rem;
    flex-shrink: 0;
  }
  /* Bold and blue for counting */
  table tbody td[data-label="#"] {
    font-weight: 700 !important;
    color: #2563eb !important;
    font-size: 1.15rem !important;
    letter-spacing: 1px;
    text-align: center;
    justify-content: center;
    align-items: center;
    background: #f1f5ff;
    border-radius: 0.7rem;
    margin-bottom: 0.5rem;
  }
  /* Customer cell center and bold */
  td[data-label="Customer"] {
    justify-content: center !important;
    align-items: center !important;
    text-align: center !important;
    font-weight: 600;
    color: #22223b;
    flex-direction: column;
    padding-top: 0.7rem;
    padding-bottom: 0.7rem;
  }
  /* Value styling for clarity */
  table tbody td span.value,
  table tbody td strong,
  table tbody td .value {
    font-weight: 600;
    color: #22223b;
    font-size: 1.07rem;
    margin-bottom: 0.1rem;
  }
  .badge {
    font-size: 1.01rem;
    padding: 0.4em 0.7em;
    border-radius: 0.5em;
  }
  .alert {
    font-size: 1.05rem;
    border-radius: 0.7em;
    margin-left: 0.2em;
    margin-right: 0.2em;
  }
  /* Delivery Person(s) label blue, value below */
  td[data-label="Delivery Person(s)"] {
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }
  td[data-label="Delivery Person(s)"]::before {
    margin-bottom: 0.2rem;}
  }
/* Desktop table - original styling */
@media (min-width: 768px) {
  .mobile-view { display: none; }
  .desktop-view { display: block; }
}

/* Mobile responsive - cards layout */
@media (max-width: 767.98px) {
  .desktop-view { display: none; }
  .mobile-view { display: block; }
  
  .container-fluid {
    padding: 15px;
  }
  
  /* Search and filter section */
  .filter-mobile {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
  }
  
  .filter-mobile .form-control {
    margin-bottom: 10px;
  }
  
  /* Customer cards for mobile */
  .customer-card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 15px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  
  .customer-name {
    font-weight: bold;
    font-size: 1.1rem;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
  }
  
  .amount-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
  }
  
  .amount-row:last-child {
    border-bottom: none;
  }
  
  .amount-label {
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
  }
  
  .amount-value {
    font-weight: bold;
    text-align: right;
  }
  
  .delivery-persons {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
  }
  
  .delivery-label {
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
    margin-bottom: 5px;
  }
  
  .delivery-names {
    font-size: 0.9rem;
    color: #666;
    background: #f8f9fa;
    padding: 8px;
    border-radius: 4px;
  }
  
  /* Summary section for mobile */
  .mobile-summary {
    background: #e7f3ff;
    border: 1px solid #b3d9ff;
    border-radius: 8px;
    padding: 15px;
    margin-top: 20px;
  }
  
  .mobile-summary h5 {
    margin-bottom: 15px;
    color: #0066cc;
  }
  
  .summary-row {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
  }
  
  .summary-row strong {
    font-weight: bold;
  }
}

/* Tablet adjustments */
@media (min-width: 576px) and (max-width: 767.98px) {
  .customer-card {
    padding: 20px;
  }
  
  .customer-name {
    font-size: 1.2rem;
  }
  
  .filter-mobile {
    padding: 20px;
  }
}

/* Keep original desktop table styling */
@media (min-width: 768px) {
  table { border: 0; }
  table thead { background: #343a40; }
  table thead th { 
    color: white; 
    border: none;
    padding: 12px 8px;
  }
  table tbody tr:hover {
    background-color: #f8f9fa;
  }
}
</style>
</head>
<body class="sb-nav-fixed">
<?php include "admin_header.php"; ?>

<div id="layoutSidenav_content">

<main class="card border-0 my-4 shadow rounded-3 p-4 bg-white mx-auto" style="min-width: 980px;">
<center><h2>Customer Cash Ledger</h2></center>

<!-- Desktop view - original layout -->
<div class="desktop-view">
  <!-- date and search filter -->
  <form class="row g-2 mb-4" method="get">
    <div class="col-auto">
        <input type="date" name="from" class="form-control"
               value="<?= htmlspecialchars($from) ?>" placeholder="From Date">
    </div>
    <div class="col-auto">
        <input type="date" name="to" class="form-control"
               value="<?= htmlspecialchars($to) ?>" placeholder="To Date">
    </div>
    <div class="col-auto">
        <input type="text" name="search" class="form-control" 
               value="<?= htmlspecialchars($search) ?>" placeholder="Search customer...">
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
       <td><?= $i++ ?></td>
       <td><?= htmlspecialchars($c['customer']) ?></td>
       <td class="text-end"><?= number_format($c['total_due'],2) ?></td>
       <td class="text-end"><?= number_format($c['paid'],2) ?></td>
       <td class="text-end">
          <?php if ($c['pending']>0): ?>
             <span class="badge bg-warning text-dark">
                 <?= number_format($c['pending'],2) ?>
             </span>
          <?php else: ?>
             <span class="badge bg-success">0.00</span>
          <?php endif; ?>
       </td>
       <td><?= htmlspecialchars($c['riders']) ?></td>
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
</div>

<!-- Mobile view - card layout -->
<div class="mobile-view">
  <!-- Mobile filter section -->
  <div class="filter-mobile">
    <form method="get">
      <div class="row g-2">
        <div class="col-12">
          <input type="text" name="search" class="form-control" 
                 value="<?= htmlspecialchars($search) ?>" placeholder="Search customer name...">
        </div>
        <div class="col-6">
          <input type="date" name="from" class="form-control"
                 value="<?= htmlspecialchars($from) ?>" placeholder="From Date">
        </div>
        <div class="col-6">
          <input type="date" name="to" class="form-control"
                 value="<?= htmlspecialchars($to) ?>" placeholder="To Date">
        </div>
        <div class="col-12">
          <button class="btn btn-outline-secondary w-100">Apply Filter</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Mobile customer cards -->
  <?php 
  $res = $mysqli->query($sql); // Re-execute query for mobile
  $sumTotal = $sumPaid = $sumPend = 0;
  $i = 1;
  while($c = $res->fetch_assoc()): 
    $sumTotal += $c['total_due'];
    $sumPaid += $c['paid'];
    $sumPend += $c['pending'];
  ?>
    <div class="customer-card">
      <div class="customer-name">
        <?= $i++ ?>. <?= htmlspecialchars($c['customer']) ?>
      </div>
      
      <div class="amount-row">
        <span class="amount-label">Total Amount:</span>
        <span class="amount-value">₹ <?= number_format($c['total_due'], 2) ?></span>
      </div>
      
      <div class="amount-row">
        <span class="amount-label">Paid Amount:</span>
        <span class="amount-value text-success">₹ <?= number_format($c['paid'], 2) ?></span>
      </div>
      
      <div class="amount-row">
        <span class="amount-label">Pending Amount:</span>
        <span class="amount-value">
          <?php if ($c['pending'] > 0): ?>
            <span class="badge bg-warning text-dark">₹ <?= number_format($c['pending'], 2) ?></span>
          <?php else: ?>
            <span class="badge bg-success">₹ 0.00</span>
          <?php endif; ?>
        </span>
      </div>
      
      <div class="delivery-persons">
        <div class="delivery-label">Delivery Person(s):</div>
        <div class="delivery-names"><?= htmlspecialchars($c['riders']) ?></div>
      </div>
    </div>
  <?php endwhile; ?>

  <!-- Mobile summary -->
  <div class="mobile-summary">
    <h5>Summary</h5>
    <div class="summary-row">
      <span>Total Amount:</span>
      <strong>₹ <?= number_format($sumTotal, 2) ?></strong>
    </div>
    <div class="summary-row">
      <span>Total Paid:</span>
      <strong class="text-success">₹ <?= number_format($sumPaid, 2) ?></strong>
    </div>
    <div class="summary-row">
      <span>Total Pending:</span>
      <strong class="text-warning">₹ <?= number_format($sumPend, 2) ?></strong>
    </div>
  </div>

  <!-- Mobile income alert -->
  <div class="alert alert-primary mt-3" role="alert">
    <strong>Total income:</strong> ₹ <?= number_format($sumPaid,2) ?>
    <?php if ($sumPend>0): ?>
         <br><strong>Still pending:</strong> ₹ <?= number_format($sumPend,2) ?>
    <?php endif; ?>
  </div>
</div>

</main>
</div><!-- /layout -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
 <script src="../js/scripts.js"></script>
</html>