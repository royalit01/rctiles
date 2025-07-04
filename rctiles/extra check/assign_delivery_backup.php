<?php

require_once '../db_connect.php';
require_once 'admin_header.php';

// Handle assignment POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_order'])) {
    $orderId   = (int)$_POST['order_id'];
    $userId    = (int)$_POST['delivery_user_id'];

    // sanity check: order exists & approved, user is delivery role
    $valid = $mysqli->query("SELECT 1 FROM orders o JOIN pending_orders po ON po.order_id=o.order_id AND po.approved=1 WHERE o.order_id=$orderId LIMIT 1")->num_rows;
    $role  = $mysqli->query("SELECT role_id FROM users WHERE user_id=$userId AND role_id=(SELECT role_id FROM roles WHERE role_name='Delivery' LIMIT 1)")->num_rows;

    if ($valid && $role) {
        // fetch rent & total
        $ord = $mysqli->query("SELECT total_amount, transport_rent FROM orders WHERE order_id=$orderId")->fetch_assoc();
        $rent = $ord['transport_rent'];
        $amt  = $ord['total_amount'] + $rent;

        // insert delivery record (ignore if exists)
        $stmt = $mysqli->prepare("INSERT INTO delivery_orders(order_id,delivery_user_id,rent,amount_remaining)
                                  VALUES(?,?,?,?)");
        $remaining = $amt;
        $stmt->bind_param('iidd', $orderId, $userId, $rent, $remaining);
        $stmt->execute();
        $success = "Order #$orderId assigned to delivery user $userId.";
    } else {
        $error = "Invalid order or user.";
    }
}

// Fetch delivery users
$driversRes = $mysqli->query("SELECT user_id, name FROM users WHERE role_id=(SELECT role_id FROM roles WHERE role_name='Delivery' LIMIT 1) ORDER BY name");
$drivers = $driversRes->fetch_all(MYSQLI_ASSOC);

// GET filter
$where  = ["po.approved = 1"];
$params = [];
$types  = '';
if (!empty($_GET['search'])) {
    $where[] = "c.name LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
    $types   .= 's';
}
$sql = "SELECT o.order_id, c.name customer, c.phone_no, o.total_amount, o.transport_rent,
               IFNULL(d.delivery_id,0)            AS delivery_id,
               d.delivery_user_id, d.status, d.amount_paid, d.amount_remaining
        FROM orders o
        JOIN customers c  ON c.customer_id = o.customer_id
        JOIN pending_orders po ON po.order_id=o.order_id
        LEFT JOIN delivery_orders d ON d.order_id = o.order_id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY o.order_id
        ORDER BY o.order_date DESC";

$stmt = $mysqli->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assign Deliveries – RC Mall CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap & vendor styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        body{font-size:1.05rem;}
        @media (max-width:575.98px){
            table thead{display:none}
            table tbody tr{display:block;margin-bottom:1rem;border:1px solid #dee2e6;border-radius:.5rem}
            table tbody tr td{display:flex;justify-content:space-between;padding:.55rem .9rem;font-size:1rem}
            table tbody tr td:first-child{font-weight:600}
        }
    </style>
</head>
<body class="sb-nav-fixed">
<div id="layoutSidenav_content">
<main class="py-4 container-fluid">
    <h2 class="mb-4">Assign Orders to Delivery Personnel</h2>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif(!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- search -->
    <form class="row g-2 mb-3" method="get">
        <div class="col-auto">
            <input type="text" class="form-control" name="search" placeholder="Customer name" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary">Search</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Customer / Phone</th>
                <th>Total (₹)</th>
                <th>Freight (₹)</th>
                <th>Delivery Person</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php $i=1; while($row=$result->fetch_assoc()):
                $grand = $row['total_amount'] + $row['transport_rent'];
            ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['customer']) ?></strong><br>
                        <a href="tel:<?= $row['phone_no'] ?>" class="text-decoration-none small text-muted"><?= htmlspecialchars($row['phone_no']) ?></a>
                    </td>
                    <td><?= number_format($grand,2) ?></td>
                    <td><?= number_format($row['transport_rent'],2) ?></td>

                    <td>
                        <?php if(!$row['delivery_id']): // not yet assigned ?>
                            <form class="d-flex" method="post" onsubmit="return confirm('Assign this order to selected user?');">
                                <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                <select name="delivery_user_id" class="form-select form-select-sm me-2" required>
                                    <option value="" disabled selected>Select driver</option>
                                    <?php foreach($drivers as $d): ?>
                                        <option value="<?= $d['user_id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-primary" name="assign_order">Assign</button>
                            </form>
                        <?php else: // already assigned ?>
                            <?= htmlspecialchars($mysqli->query("SELECT name FROM users WHERE user_id=".$row['delivery_user_id'])->fetch_column()) ?>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if(!$row['delivery_id']): ?>
                            <span class="badge bg-secondary">Pending&nbsp;Assignment</span>
                        <?php else: ?>
                            <?php if($row['amount_remaining']<=0): ?>
                                <span class="badge bg-success">Completed</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($row['status']) ?></span><br>
                                <small class="text-muted">Due ₹<?= number_format($row['amount_remaining'],2) ?></small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>

                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-info" target="_blank" href="view_bill.php?order_id=<?= $row['order_id'] ?>"><i class="fa fa-file-invoice"></i> Bill</a>
                        <?php if($row['delivery_id']): ?>
                            <button class="btn btn-sm btn-outline-primary ms-1 open-delivery" data-delivery="<?= $row['delivery_id'] ?>">
                                <i class="fa fa-truck"></i> Update
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Delivery detail modal (payments & returns) -->
    <div class="modal fade" id="deliveryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
            <div class="modal-content"></div>
        </div>
    </div>

</main>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// lazy‑load delivery detail form
$(document).on('click','.open-delivery',function(){
    const id = $(this).data('delivery');
    $('#deliveryModal .modal-content').load('delivery_detail.php',{delivery_id:id}, ()=>new bootstrap.Modal('#deliveryModal').show());
});
</script>
</body>
</html>


