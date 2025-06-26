<?php
require '../db_connect.php';

$order_id = intval($_GET['order_id'] ?? 0);
if (!$order_id) die("Invalid Order ID");

// Fetch customer and order
$stmt = $mysqli->prepare("
    SELECT c.name, c.phone_no, c.address, c.city,
                 (SELECT SUM(custom_price) FROM pending_orders WHERE order_id = o.order_id) AS final_amount, o.transport_rent
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    WHERE o.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) die("Order not found");

// Fetch products
$stmt = $mysqli->prepare("
    SELECT p.product_name, po.quantity, po.original_price, po.custom_price
    FROM pending_orders po
    JOIN products p ON po.product_id = p.product_id
    WHERE po.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = $order['final_amount'] + $order['transport_rent'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>RC Mall Bill View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="p-4">
    <h2 class="text-center mb-4">RC Mall – Customer Bill (Read Only)</h2>

    <div class="row mb-3">
        <div class="col-md-4"><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></div>
        <div class="col-md-4"><strong>Phone:</strong> <?= htmlspecialchars($order['phone_no']) ?></div>
        <div class="col-md-4"><strong>Address:</strong> <?= htmlspecialchars($order['address']) . ', ' . htmlspecialchars($order['city']) ?></div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr><th>#</th><th>Item</th><th>Qty</th><th>Original ₹</th><th>Custom ₹</th><th>Total ₹</th></tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($products as $p): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($p['product_name']) ?></td>
                <td><?= $p['quantity'] ?></td>
                <td><?= number_format($p['original_price'],2) ?></td>
                <td><?= number_format($p['custom_price']/$p['quantity'],2) ?></td>
                <td><?= number_format($p['custom_price'],2) ?></td>
            </tr>
            <?php endforeach ?>
        </tbody>
        <tfoot>
            <tr><th colspan="5" class="text-end">Rent:</th><th>₹<?= number_format($order['transport_rent'],2) ?></th></tr>
            <tr><th colspan="5" class="text-end">Final Amount Paid:</th><th>₹<?= number_format($order['final_amount'],2) ?></th></tr>
            <tr class="table-dark"><th colspan="5" class="text-end">Grand Total:</th><th>₹<?= number_format($total,2) ?></th></tr>
        </tfoot>
    </table>

    <button class="btn btn-success" onclick="downloadPDF()">Download PDF</button>

    <script>
        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text("RC Mall – Customer Bill", 105, 10, { align: "center" });
            doc.autoTable({
                head: [['#', 'Item', 'Qty', 'Original ₹', 'Custom ₹', 'Total ₹']],
                body: <?= json_encode(array_map(function($i, $p) {
                    return [$i, $p['product_name'], $p['quantity'], number_format($p['original_price'],2), number_format($p['custom_price']/$p['quantity'],2), number_format($p['custom_price'],2)];
                }, range(1, count($products)), $products)) ?>,
                startY: 20
            });
            doc.save("RCMall_Bill.pdf");
        }
    </script>
</body>
</html>
