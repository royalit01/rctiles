<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
require_once '../db_connect.php';

function json_exit($ok, $msg, $extra = []) {
    header('Content-Type: application/json');
    echo json_encode($extra + ['ok' => $ok, 'msg' => $msg]);
    exit;
}

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_payment') {
        $deliveryId = (int)$_POST['delivery_id'];
        $amt = (float)$_POST['amount'];
        $remarks = trim($_POST['remarks'] ?? '');

        if ($amt <= 0) json_exit(false, 'Amount must be >0');

        $stmt = $mysqli->prepare("INSERT INTO delivery_payments (delivery_id, amount_paid, remarks) VALUES (?, ?, ?)");
        $stmt->bind_param('ids', $deliveryId, $amt, $remarks);
        $stmt->execute();

        $mysqli->query("UPDATE delivery_orders
                        SET amount_paid = amount_paid + $amt,
                            amount_remaining = GREATEST(amount_remaining - $amt, 0),
                            status = IF(amount_remaining - $amt <= 0, 'Completed', 'Partially Paid')
                        WHERE delivery_id = $deliveryId");

        json_exit(true, 'Payment saved');
    }

    if ($action === 'update_item_qty') {
        $rowId = (int)$_POST['id'];
        $del = max(0, (int)$_POST['delivered']);
        $ret = max(0, (int)$_POST['returned']);

        $ord = $mysqli->query("SELECT qty_ordered FROM delivery_items WHERE id = $rowId")->fetch_assoc();
        if ($del + $ret > $ord['qty_ordered']) json_exit(false, 'Sum exceeds ordered');

        $stmt = $mysqli->prepare("UPDATE delivery_items SET qty_delivered = ?, qty_returned = ? WHERE id = ?");
        $stmt->bind_param('iii', $del, $ret, $rowId);
        $stmt->execute();

        json_exit(true, 'Saved');
    }

    json_exit(false, 'Unknown action');
}

// Render modal
$deliveryId = (int)($_POST['delivery_id'] ?? 0);
if (!$deliveryId) die('Bad request');

// Fetch delivery header
$head = $mysqli->query("
    SELECT d.*, o.order_id, o.rent_amount, c.name AS customer, c.phone_no
    FROM delivery_orders d
    JOIN orders o ON o.order_id = d.order_id
    JOIN customers c ON c.customer_id = o.customer_id
    WHERE d.delivery_id = $deliveryId
")->fetch_assoc();

if (!$head) die('Not found');

$orderId = $head['order_id'];

// Fetch delivery items with custom price
$items = $mysqli->query("
    SELECT di.*, p.product_name, po.custom_price
    FROM delivery_items di
    JOIN products p ON p.product_id = di.product_id
    JOIN pending_orders po ON po.order_id = $orderId AND po.product_id = di.product_id
    WHERE di.delivery_id = $deliveryId
")->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$subtotal = array_reduce($items, fn($sum, $row) => $sum + $row['custom_price'], 0);
$grand = $subtotal + $head['rent'];

// Get payments
$payments = $mysqli->query("SELECT * FROM delivery_payments WHERE delivery_id = $deliveryId ORDER BY payment_date DESC")
                   ->fetch_all(MYSQLI_ASSOC);
?>

<div class="modal-header">
    <h5 class="modal-title">Delivery #<?= $deliveryId ?> | Order #<?= $head['order_id'] ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="row g-3 mb-3">
        <div class="col-md">
            <div class="border p-2 rounded">
                <strong>Customer:</strong> <?= htmlspecialchars($head['customer']) ?><br>
                <small><a href="tel:<?= $head['phone_no'] ?>" class="text-muted"><?= $head['phone_no'] ?></a></small>
            </div>
        </div>
        <div class="col-md">
            <div class="border p-2 rounded">
                <strong>Grand Total:</strong> ₹<?= number_format($grand, 2) ?><br>
                <span class="text-success">Paid: ₹<?= number_format($head['amount_paid'], 2) ?></span><br>
                <span class="<?= $head['amount_remaining'] > 0 ? 'text-danger' : 'text-muted' ?>">
                    Remaining: ₹<?= number_format($head['amount_remaining'], 2) ?>
                </span>
            </div>
        </div>
        <div class="col-md">
            <div class="border p-2 rounded">
                <strong>Status:</strong>
                <?= $head['status'] === 'Completed' ? '<span class="badge bg-success">Completed</span>' : '<span class="badge bg-warning text-dark">'.$head['status'].'</span>' ?>
            </div>
        </div>
    </div>

    <h6>Products</h6>
    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered align-middle" id="itemsTable">
            <thead class="table-light">
                <tr><th>#</th><th>Product</th><th class="text-center">Ordered</th><th class="text-center">Delivered</th><th class="text-center">Returned</th><th></th></tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($items as $it): ?>
                <tr data-id="<?= $it['id'] ?>">
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($it['product_name']) ?></td>
                    <td class="text-center"><?= $it['qty_ordered'] ?></td>
                    <td><input type="number" min="0" max="<?= $it['qty_ordered'] ?>" class="form-control form-control-sm delivered" value="<?= $it['qty_delivered'] ?>"></td>
                    <td><input type="number" min="0" max="<?= $it['qty_ordered'] ?>" class="form-control form-control-sm returned" value="<?= $it['qty_returned'] ?>"></td>
                    <td class="text-center"><button class="btn btn-sm btn-outline-primary save-item">Save</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h6>Add Payment</h6>
    <form id="payForm" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="action" value="add_payment">
        <input type="hidden" name="delivery_id" value="<?= $deliveryId ?>">
        <div class="col-auto">
            <label class="form-label small">Amount</label>
            <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
        <div class="col-auto flex-grow-1">
            <label class="form-label small">Remarks</label>
            <input type="text" name="remarks" class="form-control" placeholder="Optional">
        </div>
        <div class="col-auto">
            <button class="btn btn-success">Add</button>
        </div>
    </form>

    <h6>Payment History</h6>
    <ul class="list-group list-group-flush small" id="payList">
        <?php if (!$payments): ?>
            <li class="list-group-item">No payments yet.</li>
        <?php else: foreach ($payments as $p): ?>
            <li class="list-group-item d-flex justify-content-between">
                <span><?= date('d M Y H:i', strtotime($p['payment_date'])) ?></span>
                <strong>₹<?= number_format($p['amount_paid'], 2) ?></strong>
            </li>
        <?php endforeach; endif; ?>
    </ul>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function postJ(d){ return $.post('delivery_detail.php', d, null, 'json'); }

$('#itemsTable').on('click', '.save-item', function() {
    const tr = $(this).closest('tr');
    postJ({
        action: 'update_item_qty',
        id: tr.data('id'),
        delivered: tr.find('.delivered').val(),
        returned: tr.find('.returned').val()
    }).done(r => {
        if (r.ok) {
            $(this).removeClass('btn-outline-primary').addClass('btn-success').text('Saved');
            setTimeout(() => $(this).attr('class','btn btn-sm btn-outline-primary save-item').text('Save'), 1500);
        } else alert(r.msg);
    }).fail(() => alert('Error'));
});

$('#payForm').on('submit', function(e) {
    e.preventDefault();
    postJ($(this).serialize()).done(r => {
        if (r.ok) $('#deliveryModal .modal-content').load('delivery_detail.php', { delivery_id: <?= $deliveryId ?> });
        else alert(r.msg);
    }).fail(() => alert('Error'));
});
</script>
