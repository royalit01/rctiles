<?php
session_start();
include "../db_connect.php";

/* admin only */
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php"); exit;
}

/* ---------- 1.  Handle collection POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['collect_now'])) {
    $uid   = (int)$_POST['user_id'];                 // rider ID
    $take  = round((float)$_POST['collect_amt'],2);  // amount admin takes

    /* current due for this rider */
    $collected = $mysqli->query(
    "SELECT SUM(amount_paid)
     FROM   delivery_orders
     WHERE  delivery_user_id = $uid")->fetch_column() ?? 0;

    $adminTaken = $mysqli->query(
        "SELECT SUM(amount)
        FROM   admin_cash_collection
        WHERE  user_id = $uid")->fetch_column() ?? 0;

    $due = $collected - $adminTaken;

    if ($take > 0 && $take <= $due) {
        $stmt = $mysqli->prepare(
          "INSERT INTO admin_cash_collection (user_id, amount, notes)
           VALUES (?,?,?)");
        $note = "Cash collected by admin";
        $stmt->bind_param("ids", $uid, $take, $note);
        $stmt->execute();
    }
    header("Location: member_ledger.php");
    exit;
}

/* ---------- 2.  Summary per rider ---------- */
$riders = $mysqli->query("
 SELECT  u.user_id,
         u.name                                            AS rider,
         SUM(d.amount_paid)                                AS collected,
         COALESCE(ac.total_admin,0)                        AS admin_collected,
         SUM(d.amount_paid) - COALESCE(ac.total_admin,0)   AS due
 FROM    users u
 JOIN    delivery_orders d       ON d.delivery_user_id = u.user_id
 LEFT JOIN (
        SELECT user_id, SUM(amount) AS total_admin
        FROM   admin_cash_collection
        GROUP  BY user_id
 ) ac ON ac.user_id = u.user_id
 GROUP  BY u.user_id
 ORDER  BY u.name");
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Member Ledger</title>

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
<center><h2 class="mb-4">Delivery Riders – Cash Ledger</h2></center>

<div class="table-responsive">
<table class="table table-bordered align-middle" id="riderTable">
 <thead class="table-dark">
  <tr><th>#</th><th>Rider</th>
      <th class="text-end">Collected&nbsp;(₹)</th>
      <th class="text-end">Admin&nbsp;collected&nbsp;(₹)</th>
      <th class="text-end">Due&nbsp;(₹)</th>
      <th class="text-center">Collect</th></tr>
 </thead>
 <tbody>
 <?php $i=1; while($r=$riders->fetch_assoc()): ?>
   <tr class="rider-row" data-user="<?= $r['user_id'] ?>">
     <td><?= $i++ ?></td>
     <td><?= htmlspecialchars($r['rider']) ?></td>
     <td class="text-end"><?= number_format($r['collected'],2) ?></td>
     <td class="text-end"><?= number_format($r['admin_collected'],2) ?></td>
     <td class="text-end">
         <?php if($r['due']>0): ?>
             <span class="badge bg-warning text-dark">
                 <?= number_format($r['due'],2) ?>
             </span>
         <?php else: ?>
             <span class="badge bg-success">0.00</span>
         <?php endif; ?>
     </td>
     <td class="text-center collect-cell">
        <form method="post" class="d-inline w-100">
          <input type="hidden" name="user_id" value="<?= $r['user_id'] ?>">
          <div class="input-group input-group-sm">
            <input type="number" step="0.01" name="collect_amt"
                   class="form-control" placeholder="₹"
                   max="<?= $r['due'] ?>" min="0"
                   style="max-width:90px"
                   <?= $r['due']<=0?'disabled':'' ?>>
            <button class="btn btn-primary"
                    name="collect_now"
                    <?= $r['due']<=0?'disabled':'' ?>>Take</button>
          </div>
        </form>
     </td>
   </tr>
 <?php endwhile; ?>
 </tbody>
</table>
</div>

<!-- Modal for rider's open orders -->
<div class="modal fade" id="ordersModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
   <div class="modal-content"></div>
 </div>
</div>

</main>
</div><!-- /layout -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/*  open modal when clicking row, except on the collect form */
$(document).on('click','.rider-row',function(e){
   if ($(e.target).closest('.collect-cell').length) return;  // ignore clicks on form
   const uid = $(this).data('user');
   $('#ordersModal .modal-content').load(
       'member_orders_modal.php',
       { user_id : uid },
       () => new bootstrap.Modal('#ordersModal').show()
   );
});

</script>
</body>
</html>
