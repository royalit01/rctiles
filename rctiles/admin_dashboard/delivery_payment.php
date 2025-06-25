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
<?php include "admin_header.php"; ?>
<div id="layoutSidenav_content">
<main class="container-fluid px-4 py-4">
<main class="card border-0 shadow rounded-3 p-4 bg-white mx-auto" style="max-width: 1200px;">
<center><h2 class="mb-4">Delivery Charge / Incentive Payments</h2></center>

<div class="table-responsive">
<table class="table table-bordered align-middle">
 <thead class="table-dark">
  <tr>
    <th>#</th><th>Customer / Phone</th><th>Order&nbsp;ID</th>
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
    <td><?= $i++ ?></td>
    <td>
        <strong><?= htmlspecialchars($r['customer']) ?></strong><br>
        <a class="small text-muted" href="tel:<?= $r['phone_no'] ?>">
            <?= htmlspecialchars($r['phone_no']) ?>
        </a>
    </td>
    <td><?= $r['order_id'] ?></td>
    <?php $grand = $r['amount_paid'] + $r['amount_remaining']; ?>
    <td class="text-end"><?= number_format($grand, 2) ?></td>
    <td class="text-end"><?= number_format($r['amount_paid'], 2) ?></td>
    <td class="text-end"><?= number_format($r['amount_remaining'], 2) ?></td>
    <td><?= htmlspecialchars($r['rider']) ?></td>
    <td class="text-end"><?= number_format($r['rider_paid'], 2) ?></td>

    <!-- quick-pay form -->
    <td class="text-center">
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
</div><!-- /layout -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
