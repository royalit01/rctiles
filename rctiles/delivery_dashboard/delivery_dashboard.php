<?php
/* -----------------------------------------------------------
   delivery_dashboard.php   –   rider-only view
----------------------------------------------------------- */

session_start();
include "../db_connect.php";

/* 1.  allow only role_id = 4 */
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    header("Location: ../login.php");
    exit;
}
$uid = (int)$_SESSION['user_id'];

/* 2.  rider’s own orders */
$sql = "SELECT d.delivery_id, d.order_id,
               c.name  AS customer, c.phone_no,
               d.status, d.rent, d.amount_paid, d.amount_remaining,
               DATE(d.assigned_at) AS assigned_on
        FROM   delivery_orders d
        JOIN   orders o      ON o.order_id     = d.order_id
        JOIN   customers c   ON c.customer_id  = o.customer_id
        WHERE  d.delivery_user_id = ?
        ORDER  BY d.status, d.assigned_at DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$list = $stmt->get_result();

/* 3.  cash card */
$bal = $mysqli->query(
        "SELECT SUM(amount_paid)      AS collected,
                SUM(amount_remaining) AS pending
         FROM   delivery_orders
         WHERE  delivery_user_id = $uid")->fetch_assoc();
$collected = $bal['collected'] ?? 0;          //  ← NEW
$cash      = $collected;                      //  big number = collected
$pending   = $bal['pending']   ?? 0;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Delivery Dashboard</title>

<link href="../css/styles.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">

<?php include "delivery_header.php"; ?>

<div id="layoutSidenav_content">
<main class="container-fluid px-4 py-4">

    <!-- cash card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-bg-primary">
                <div class="card-body">
                    <h5 class="card-title mb-1">Total collected</h5>      <!-- label change -->
                    <h2 class="mb-0">₹ <?= number_format($cash, 2) ?></h2>
                    <small>
                        Pending&nbsp;dues: ₹<?= number_format($pending, 2) ?>  <!-- label change -->
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- order list -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-truck me-1"></i>Your Deliveries</div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered align-middle">
                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Customer / Phone</th>
                    <th>Total&nbsp;(₹)</th>
                    <th>Rent&nbsp;(₹)</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php $i=1; while($row=$list->fetch_assoc()): ?>
                    <?php $grand = $row['rent'] + $row['amount_remaining'] + $row['amount_paid']; ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['customer']) ?></strong><br>
                            <a href="tel:<?= $row['phone_no'] ?>" class="small text-muted">
                                <?= htmlspecialchars($row['phone_no']) ?>
                            </a>
                        </td>
                        <td><?= number_format($grand,2) ?></td>
                        <td><?= number_format($row['rent'],2) ?></td>
                        <td>
                            <?php if($row['amount_remaining']<=0): ?>
                                <span class="badge bg-success">Completed</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><?= $row['status'] ?></span><br>
                                <small class="text-muted">Due ₹<?= number_format($row['amount_remaining'],2) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap text-center">
                            <button class="btn btn-sm btn-outline-primary open-delivery"
                                    data-delivery="<?= $row['delivery_id'] ?>">
                                <i class="fa fa-edit"></i> Update
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- modal shell -->
    <div class="modal fade" id="deliveryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
            <div class="modal-content"></div>
        </div>
    </div>

</main>
</div><!-- /layoutSidenav_content -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script src="../js/datatables-simple-demo.js"></script>

<script>
/* open the same modal used in assign_delivery.php */
$(document).on('click','.open-delivery',function(){
    const id = $(this).data('delivery');
    $('#deliveryModal .modal-content').load(
        'delivery_detail.php',
        { delivery_id : id },
        () => new bootstrap.Modal('#deliveryModal').show()
    );
});
</script>
</body>
</html>
