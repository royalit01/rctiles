<?php
require '../db_connect.php';

$order_id = intval($_GET['order_id'] ?? 0);
if (!$order_id) die("Invalid Order ID");

// Fetch customer and order
$stmt = $mysqli->prepare("
    SELECT c.name, c.phone_no, c.address, c.city,
           o.discounted_amount AS final_amount, o.transport_rent
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
    <style>
        body {
            background: #e9ecef;
        }
        .bill-container {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.10);
            padding: 2.5rem 3rem 2rem 3rem;
            max-width: 1100px;
            margin: 60px auto 40px auto;
        }
        .bill-header {
            border-bottom: 3px solid #0d6efd;
            margin-bottom: 2rem;
            padding-bottom: 1.2rem;
        }
        .bill-header h2 {
            letter-spacing: 1px;
            font-weight: 700;
        }
        .table th, .table td {
            vertical-align: middle !important;
            font-size: 1.08rem;
        }
        .table tfoot th {
            background: #f1f3f4;
            font-weight: 600;
            font-size: 1.08rem;
        }
        .table-dark th {
            background: #0d6efd !important;
            color: #fff !important;
            font-size: 1.09rem;
        }
        .btn-success {
            min-width: 180px;
            font-size: 1.08rem;
            padding: 0.6rem 1.5rem;
        }
        .customer-info-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.1rem 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 8px rgba(0,0,0,0.04);
        }
        .customer-info-box strong {
            color: #0d6efd;
        }
        @media (max-width: 900px) {
            .bill-container {
                padding: 1.2rem 0.5rem;
                max-width: 99vw;
            }
        }
        @media (max-width: 600px) {
            .bill-header {
                padding-bottom: 0.5rem;
            }
            .customer-info-box {
                padding: 0.7rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="bill-header text-center">
            <h2 class="mb-1">RC Mall – Customer Bill</h2>
            <div class="text-muted fs-6">Read Only</div>
        </div>

        <div class="customer-info-box row mb-4">
            <div class="col-md-4 col-12 mb-2"><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></div>
            <div class="col-md-4 col-12 mb-2"><strong>Phone:</strong> <?= htmlspecialchars($order['phone_no']) ?></div>
            <div class="col-md-4 col-12"><strong>Address:</strong> <?= htmlspecialchars($order['address']) . ', ' . htmlspecialchars($order['city']) ?></div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Original ₹</th>
                        <th>Custom ₹</th>
                        <th>Total ₹</th>
                    </tr>
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
                    <tr>
                        <th colspan="5" class="text-end">Rent:</th>
                        <th>₹<?= number_format($order['transport_rent'],2) ?></th>
                    </tr>
                    <tr>
                        <th colspan="5" class="text-end">Final Amount Paid:</th>
                        <th>₹<?= number_format($order['final_amount'],2) ?></th>
                    </tr>
                    <tr class="table-dark">
                        <th colspan="5" class="text-end">Grand Total:</th>
                        <th>₹<?= number_format($total,2) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="text-center mt-4">
            <button class="btn btn-success shadow" onclick="downloadPDF()">
                <i class="fas fa-download me-2"></i>Download PDF
            </button>
        </div>
    </div>

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
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
        // Helper for PHP's number_format in JS
        function number_format(number, decimals) {
            return parseFloat(number).toLocaleString('en-IN', {minimumFractionDigits: decimals, maximumFractionDigits: decimals});
        }
    </script>
</body>
</html>
