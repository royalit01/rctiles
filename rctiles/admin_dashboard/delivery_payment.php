<?php
/* delivery_payment.php  – Admin enters transport -or- incentive
   payments that go *to* delivery riders (“rider income”).         */

session_start();
include "../db_connect.php";

/* 1. Admin-only gate */
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}

/* ---------- 2. Handle POST: record a payment to the rider ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_rider'])) {
    $deliveryId = (int)$_POST['delivery_id'];
    $riderId    = (int)$_POST['rider_id'];
    $amt        = round((float)$_POST['pay_amt'], 2);
    $note       = trim($_POST['remarks']);

    if ($amt > 0 && $riderId) {
        $stmt = $mysqli->prepare(
            "INSERT INTO rider_income (user_id, delivery_id, amount, remarks)
             VALUES (?,?,?,?)"
        );
        $stmt->bind_param("iids", $riderId, $deliveryId, $amt, $note);
        $stmt->execute();
    }
    header("Location: delivery_payment.php");   // simple PRG
    exit;
}

/* ---------- 3. Fetch deliveries with balance + rider ---------- */
$q = "
  SELECT d.delivery_id,
         d.order_id,
         c.name                        AS customer,
         c.phone_no,
         d.amount_paid,
         d.amount_remaining,
         d.rent,
         u.user_id,
         u.name                        AS rider,
         COALESCE(SUM(ri.amount),0)    AS rider_paid          -- already paid
  FROM       delivery_orders   d
  JOIN       orders            o ON o.order_id      = d.order_id
  JOIN       customers         c ON c.customer_id   = o.customer_id
  JOIN       users             u ON u.user_id       = d.delivery_user_id
  LEFT JOIN  rider_income      ri ON ri.delivery_id = d.delivery_id
  GROUP BY   d.delivery_id
  ORDER BY   d.delivery_id DESC
";
$rows = $mysqli->query($q);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport"  content="width=device-width,initial-scale=1">

<title>Pay Delivery Charges</title>

<link href="../css/styles.css" rel="stylesheet">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.3.0/css/all.css">
 <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"/>                

<style>
/* Mobile responsive table */
@media (max-width: 575.98px) {
  .card {
    padding: 0.7rem 0.2rem !important;
    border-radius: 1rem !important;
    box-shadow: 0 4px 18px rgba(13,110,253,0.13), 0 1.5px 8px rgba(0,0,0,0.06);
  }
  .table-responsive { box-shadow: none !important; }
  table { border: 0; width: 100%; }
  table thead { display: none; }
  table tbody tr {
    display: block;
    margin-bottom: 1.2rem;
    border: none;
    border-radius: 1.1rem;
    background: #fff;
    box-shadow: 0 4px 18px rgba(13,110,253,0.10);
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
    border-radius: 1.1rem 0 0 1.1rem;
  }
  table tbody td {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: .55rem 0.2rem .55rem .7rem;
    font-size: 1.08rem;
    border: none !important;
    border-bottom: 1px solid #f1f3f4 !important;
    background: #fff;
    width: 100%;
    margin-bottom: 0.18rem;
    position: relative;
  }
  table tbody td:last-child { border-bottom: none !important; }
  table tbody td:first-child { border-top: 0; }
  table tbody td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #2563eb;
    margin-bottom: .18rem;
    min-width: 120px;
    display: block;
    font-size: 1.01rem;
    letter-spacing: 0.2px;
    font-family: 'Segoe UI',sans-serif;
    margin-right: 0;
  }
  table tbody td strong,
  table tbody td .value {
    font-weight: 600;
    color: #22223b;
    font-size: 1.09rem;
    margin-bottom: 0.1rem;
  }
  table tbody td a {
    color: #64748b;
    font-size: 0.97rem;
    text-decoration: none;
    word-break: break-all;
  }
  .input-group.input-group-sm {
    flex-direction: column;
    align-items: stretch;
    width: 100%;
    gap: 0.3rem;
    margin-top: 0.2rem;
  }
  .input-group.input-group-sm input,
  .input-group.input-group-sm button {
    width: 100% !important;
    margin-bottom: 0 !important;
    font-size: 1.09rem;
    border-radius: 0.5rem !important;
  }
  .form-control.form-control-sm.mt-1 {
    margin-top: 0.3rem !important;
    font-size: 1.04rem;
    border-radius: 0.5rem !important;
  }
  .text-end, .text-center {
    text-align: left !important;
    width: 100%;
  }
  .btn-primary, .btn {
    font-size: 1.09rem !important;
    padding: 0.5rem 1.1rem !important;
    border-radius: 0.7rem !important;
    box-shadow: 0 2px 8px rgba(13,110,253,0.10);
    font-weight: 600;
    letter-spacing: 0.5px;
  }
  .table-responsive {
    overflow-x: visible !important;
  }
  .table tbody tr + tr { margin-top: 1.2rem; }
}
</style>
</head>
<body class="sb-nav-fixed">
<?php include "admin_header.php"; ?>
<div id="layoutSidenav_content">
<main class="container-fluid px-2 px-md-4 py-3">
<main class="card border-0 shadow rounded-3 p-4 bg-white mx-auto" style="max-width: 1200px;">
<center><h2 class="mb-4">Delivery Charge / Incentive Payments</h2></center>

<div class="table-responsive">
<table class="table table-bordered align-middle">
 <thead class="table-dark">
  <tr>
    <th></th>
    <th>Customer / Phone</th>
    <th>Order&nbsp;ID</th>
    <th class="text-end">Total&nbsp;(₹)</th>
    <th class="text-end">Paid&nbsp;(₹)</th>
    <th class="text-end">Remain&nbsp;(₹)</th>
    <th>Rider</th>
    <th class="text-end">Already&nbsp;Given&nbsp;(₹)</th>
    <th class="text-center">Pay&nbsp;Now (₹)</th>
  </tr>
 </thead>
 <tbody>
 <?php $i=1; while($r=$rows->fetch_assoc()): ?>
  <tr>
    <td data-label="" class="fw-bold" style="font-weight:700;">
      <span class="count-number"><?= $i++ ?></span>
    </td>
    <td data-label="Customer / Phone">
        <strong><?= htmlspecialchars($r['customer']) ?></strong><br>
        <a class="small text-muted" href="tel:<?= $r['phone_no'] ?>">
            <?= htmlspecialchars($r['phone_no']) ?>
        </a>
    </td>
    <td data-label="Order ID"><?= $r['order_id'] ?></td>
    <?php $grand = $r['amount_paid'] + $r['amount_remaining']; ?>
    <td data-label="Total (₹)" class="text-end"><?= number_format($grand, 2) ?></td>
    <td data-label="Paid (₹)" class="text-end"><?= number_format($r['amount_paid'], 2) ?></td>
    <td data-label="Remain (₹)" class="text-end"><?= number_format($r['amount_remaining'], 2) ?></td>
    <td data-label="Rider"><?= htmlspecialchars($r['rider']) ?></td>
    <td data-label="Already Given (₹)" class="text-end"><?= number_format($r['rider_paid'], 2) ?></td>

    <!-- quick-pay form -->
    <td data-label="Pay Now (₹)" class="text-center">
      <form class="d-inline" method="post">
        <input type="hidden" name="delivery_id" value="<?= $r['delivery_id'] ?>">
        <input type="hidden" name="rider_id"    value="<?= $r['user_id'] ?>">
        <div class="input-group input-group-sm">
            <input  type="number" step="0.01" min="0"
                    class="form-control" name="pay_amt"
                    placeholder="₹" style="max-width:80px" required>
            <button class="btn btn-primary" name="pay_rider">Pay</button>
        </div>
        <input type="text" class="form-control form-control-sm mt-1"
               name="remarks" placeholder="Remarks (opt.)">
      </form>
    </td>
  </tr>
 <?php endwhile; ?>
 </tbody>
</table>
</div>

</main>
</main>
</div><!-- /layout -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
 <script src="../js/scripts.js"></script>
</html>
