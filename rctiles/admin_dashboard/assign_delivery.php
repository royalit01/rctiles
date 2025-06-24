<?php
require_once '../db_connect.php';

// Handle assignment POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_order'])) {
    $orderId = (int)$_POST['order_id'];
    $userId  = (int)$_POST['delivery_user_id'];

    // Validate order exists and is approved
    $valid = $mysqli->query("SELECT 1 FROM orders o JOIN pending_orders po ON po.order_id=o.order_id AND po.approved=1 WHERE o.order_id=$orderId LIMIT 1")->num_rows;
    $role  = $mysqli->query("SELECT role_id FROM users WHERE user_id=$userId AND role_id=(SELECT role_id FROM roles WHERE role_name='Delivery' LIMIT 1)")->num_rows;
    $exists = $mysqli->query("SELECT 1 FROM delivery_orders WHERE order_id=$orderId")->num_rows;

    if (!$valid || !$role) {
        $error = "Invalid order or user.";
    } elseif ($exists) {
        $error = "This order has already been assigned for delivery.";
    } else {
        // Get rent and discounted amount (fallback to total_amount if discounted is NULL)
        $ord = $mysqli->query("SELECT 
                                  COALESCE(discounted_amount, total_amount) AS discounted_amount, 
                                  COALESCE(transport_rent, 0) AS transport_rent 
                               FROM orders 
                               WHERE order_id = $orderId")->fetch_assoc();

        $rent = (float)$ord['transport_rent'];
        $amt  = (float)$ord['discounted_amount'];
        $remaining = $amt + $rent;

        // Insert delivery record
        $stmt = $mysqli->prepare("INSERT INTO delivery_orders (order_id, delivery_user_id, rent, amount_remaining) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('iidd', $orderId, $userId, $rent, $remaining);
        $stmt->execute();

        $deliveryId = $mysqli->insert_id;

        // Insert delivery items from pending_orders
        $items = $mysqli->query("SELECT product_id, quantity FROM pending_orders WHERE order_id = $orderId");
        while ($row = $items->fetch_assoc()) {
            $productId = (int)$row['product_id'];
            $qty       = (int)$row['quantity'];
            $mysqli->query("INSERT INTO delivery_items (delivery_id, product_id, qty_ordered) VALUES ($deliveryId, $productId, $qty)");
        }

        $success = "Order #$orderId assigned to delivery user $userId.";
    }
}

// Fetch delivery users
$driversRes = $mysqli->query("SELECT user_id, name FROM users WHERE role_id=(SELECT role_id FROM roles WHERE role_name='Delivery' LIMIT 1) ORDER BY name");
$drivers = $driversRes->fetch_all(MYSQLI_ASSOC);

// Filters
$where = ["po.approved = 1"];
$params = [];
$types  = '';
if (!empty($_GET['search'])) {
    $where[] = "c.name LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
    $types   .= 's';
}

$sql = "SELECT o.order_id, c.name customer, c.phone_no, o.discounted_amount, o.transport_rent,
               IFNULL(d.delivery_id,0) AS delivery_id,
               d.delivery_user_id, d.status, d.amount_paid, d.amount_remaining
        FROM orders o
        JOIN customers c ON c.customer_id = o.customer_id
        JOIN pending_orders po ON po.order_id = o.order_id
        LEFT JOIN delivery_orders d ON d.order_id = o.order_id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY o.order_id
        ORDER BY o.order_date DESC";

    $stmt = $mysqli->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Admin Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <style>
        /* Base font slightly larger for readability */
        body{font-size:1.05rem;}

        @media (max-width: 575.98px){
            /* card‑style rows on extra‑small screens */
            table.table thead{display:none}
            table.table tbody tr{display:block;margin-bottom:1rem;border:1px solid #dee2e6;border-radius:.5rem}
            table.table tbody tr td{display:flex;justify-content:space-between;padding:.55rem .9rem;font-size:1rem;}
            table.table tbody tr td:first-child{font-weight:600}
        }
    </style>
    </head>
    <body class="sb-nav-fixed">
        <?php include "admin_header.php";  ?>
            <div id="layoutSidenav_content">
                <main>
 

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
                <th>Rent (₹)</th>
                <th>Delivery Person</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php $i=1; while($row=$result->fetch_assoc()):
                $grand = (float)$row['discounted_amount'] + (float)$row['transport_rent'];
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
                        <?php if(!$row['delivery_id']): ?>
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
                        <?php else: ?>
                            <?= htmlspecialchars($mysqli->query("SELECT name FROM users WHERE user_id=".$row['delivery_user_id'])->fetch_column()) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!$row['delivery_id']): ?>
                            <span class="badge bg-secondary">Pending Assignment</span>
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

    <!-- Delivery modal -->
    <div class="modal fade" id="deliveryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
            <div class="modal-content"></div>
        </div>
    </div>
</main>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
 <script src="../js/scripts.js"></script>
<script>
$(document).on('click','.open-delivery',function(){
    const id = $(this).data('delivery');
    $('#deliveryModal .modal-content').load('delivery_detail.php',{delivery_id:id}, ()=>new bootstrap.Modal('#deliveryModal').show());
});
</script>
</body>
</html>
