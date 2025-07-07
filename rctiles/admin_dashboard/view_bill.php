<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
require '../db_connect.php';

$order_id = intval($_GET['order_id'] ?? 0);
if (!$order_id) die("Invalid Order ID");

// Fetch customer and order
$stmt = $mysqli->prepare("
    SELECT c.name, c.phone_no, c.address, c.city,
                 o.final_amount,  o.rent_amount AS transport_rent
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
                        <th colspan="5" class="text-end">Final Amount Paid:</th>
                        <th>₹<?= number_format($order['final_amount'],2) ?></th>
                    </tr>
                    <tr>
                        <th colspan="5" class="text-end">Freight:</th>
                        <th>₹<?= number_format($order['transport_rent'],2) ?></th>
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
    const billData = {
        customerName: <?= json_encode($order['name']) ?>,
        customerPhone: <?= json_encode($order['phone_no']) ?>,
        customerAddress: <?= json_encode($order['address'] . ', ' . $order['city']) ?>,
        finalAmount: <?= json_encode($order['final_amount']) ?>,
        rentAmount: <?= json_encode($order['transport_rent']) ?>,
        grandTotal: <?= json_encode($total) ?>
    };

   const logoBase64 = "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAYGBgYH..."; // your logo

function numberToWords(num) {
    if (typeof num !== "number" || isNaN(num)) return "Zero";
    const a = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
    const b = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];
    const g = ["", "Thousand", "Lakh", "Crore"];
    if (num === 0) return "Zero";
    let str = "";
    let i = 0;
    function inWords(n) {
        let s = "";
        if (n > 19) {
            s += b[Math.floor(n / 10)] + (n % 10 ? " " + a[n % 10] : "");
        } else if (n > 0) {
            s += a[n];
        }
        return s;
    }
    function group(n) {
        let out = [];
        out.push(n % 1000); n = Math.floor(n / 1000);
        out.push(n % 100); n = Math.floor(n / 100);
        out.push(n % 100); n = Math.floor(n / 100);
        out.push(n);
        return out;
    }
    let parts = num.toFixed(2).split(".");
    let n = parseInt(parts[0]);
    let dec = parseInt(parts[1]);
    let grps = group(n);
    for (let j = grps.length - 1; j >= 0; j--) {
        if (grps[j]) {
            if (str !== "") str += " ";
            str += inWords(grps[j]) + (g[j] ? " " + g[j] : "");
        }
    }
    if (str === "") str = "Zero";
    if (dec > 0) {
        str += ` and ${inWords(dec)} Paise`;
    }
    return str + " Only";
}

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: "p", unit: "mm", format: "a4" });

    // Header background and logo
    doc.setFillColor(220, 38, 38);
    doc.rect(0, 0, 210, 30, 'F');
    const pageWidth = doc.internal.pageSize.getWidth();
    const logoWidth = 30;
    const logoHeight = 20;
    const logoX = (pageWidth - logoWidth) / 2;
    const logoY = 5;
doc.addImage(logoBase64, 'JPEG', logoX, logoY, logoWidth, logoHeight);
    doc.setFontSize(10).setFont("helvetica", "bold");
    doc.setTextColor(255, 255, 255);
    doc.text("RC INDUSTRIES", 15, 12);
    doc.setFontSize(8).setFont("helvetica", "normal");
    doc.text("GSTIN: 23AAKFRB273NTZJ", 15, 17);
    doc.text("State: 23-Madhya Pradesh", 15, 22);
    doc.setFontSize(10);
    doc.text("Phone: 1234567890", 195, 12, { align: "right" });
    doc.text("Email: rc@gmail.com", 195, 17, { align: "right" });
    doc.text("Address: 7671 MAXI ROAD LIDYOSPUR", 195, 22, { align: "right" });

    // Bill To section
    const startY = 40;
    doc.setFontSize(10).setFont("helvetica", "bold");
    doc.setTextColor(220, 38, 38);
    doc.text("Bill To", 14, startY);
    doc.setFontSize(15).setFont("helvetica", "bold");
    doc.setTextColor(0, 0, 0);
    let customerName = document.querySelector(".customer-info-box .col-md-4:nth-child(1)")?.innerText.replace('Name:', '').trim() || "";
    let customerPhone = document.querySelector(".customer-info-box .col-md-4:nth-child(2)")?.innerText.replace('Phone:', '').trim() || "";
    let customerAddress = document.querySelector(".customer-info-box .col-md-4:nth-child(3)")?.innerText.replace('Address:', '').trim() || "";
    doc.text(customerName, 14, startY + 7);
    doc.setFontSize(10).setFont("helvetica", "normal");
    doc.text(customerPhone, 14, startY + 13);
    const addressLines = doc.splitTextToSize(customerAddress, 80);
    let addressY = startY + 19;
    addressLines.forEach(line => {
        doc.text(line, 14, addressY);
        addressY += 6;
    });
    doc.setFont("helvetica", "bold");
    doc.setTextColor(10, 10, 10);
    doc.text("GSTIN Number: ", 14, addressY + 1);
    doc.text("State: ", 14, addressY + 8);
    doc.setFont("helvetica", "normal");
    const gstinX = 14 + doc.getTextWidth("GSTIN Number: ");
    const stateX = 14 + doc.getTextWidth("State: ");
    doc.text(" 23AAYCAG150A1ZV", gstinX, addressY + 1);
    doc.text(" 23-Madhya Pradesh", stateX, addressY + 8);

    // Tax Invoice section
    const invoiceY = startY + 5;
    doc.setFontSize(10).setFont("helvetica", "bold");
    doc.setTextColor(220, 38, 38);
    doc.text("Tax Invoice", 140, invoiceY);
    doc.setFontSize(10).setFont("helvetica", "normal");
    doc.setTextColor(0, 0, 0);
    doc.text("Invoice No.: " + <?= (int)$order_id ?>, 140, invoiceY + 7);
    doc.text("Date: " + new Date().toLocaleDateString(), 140, invoiceY + 14);
    const separatorY = startY + 37;
    doc.setLineWidth(0.2).line(14, separatorY, 196, separatorY);

    // Product Table
    const tableBody = [];
    document.querySelectorAll("table tbody tr").forEach((tr) => {
        const cells = tr.querySelectorAll("td");
        if (cells.length === 6) {
            tableBody.push([
                cells[0].innerText,
                cells[1].innerText,
                cells[2].innerText,
                "Box",
                cells[4].innerText.replace('₹', ''),
                cells[5].innerText.replace('₹', '')
            ]);
        }
    });
    doc.autoTable({
        head: [["#", "Item Name", "Quantity", "Unit", "Price/ Unit", "Amount"]],
        body: tableBody,
        startY: 80,
        margin: { left: 14 },
        styles: {
            fontSize: 9,
            cellPadding: 3,
            valign: 'middle',
            lineColor: [0, 0, 0],
            lineWidth: 0.1,
            textColor: [0, 0, 0]
        },
        headStyles: {
            fillColor: [220, 38, 38],
            textColor: 255,
            fontStyle: 'bold',
            cellPadding: {top: 5, right: 2, bottom: 5, left: 2},
            halign: 'center'
        },
        columnStyles: {
            0: { cellWidth: 8, halign: 'center' },
            1: { cellWidth: 'auto' },
            2: { cellWidth: 17, halign: 'center' },
            3: { cellWidth: 15, halign: 'center' },
            4: { cellWidth: 20, halign: 'right' },
            5: { cellWidth: 25, halign: 'right' }
        }
    });

    // Tax Summary Table
    const tfootRows = document.querySelectorAll("table tfoot tr");
    let subTotal = 0, rentAmount = 0, grandTotal = 0;
    if (tfootRows.length >= 3) {
        subTotal = parseFloat(tfootRows[0].querySelector('th:last-child')?.innerText.replace('₹','')) || 0;
        rentAmount = parseFloat(tfootRows[1].querySelector('th:last-child')?.innerText.replace('₹','')) || 0;
        grandTotal = parseFloat(tfootRows[2].querySelector('th:last-child')?.innerText.replace('₹','')) || 0;
    }
    const taxSummaryBody = [
       ["Final Amount Paid", `₹${parseFloat(billData.finalAmount).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`],
        ["Rent", `₹${parseFloat(billData.rentAmount).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`],
        ["Grand Total", `₹${parseFloat(billData.grandTotal).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`]
  ];
    const taxSummaryY = doc.autoTable.previous.finalY + 3;
    doc.autoTable({
        head: [["Description", "Amount"]],
        body: taxSummaryBody,
        startY: taxSummaryY,
        margin: { left: 130 },
        styles: {
            fontSize: 9,
            cellPadding: 3,
            valign: 'middle',
            lineColor: [0, 0, 0],
            lineWidth: 0.1,
            textColor: [0, 0, 0]
        },
        headStyles: {
            fillColor: [220, 38, 38],
            textColor: 255,
            fontStyle: 'bold'
        },
        columnStyles: {
            0: { cellWidth: 28, halign: 'left' },
        }
    });

    // Pay To section
    const footerY = 190;
    doc.setFontSize(10).setFont("helvetica", "bold");
    doc.setTextColor(220, 38, 38);
    doc.text("Pay To:", 14, footerY);
    doc.setFont("helvetica", "normal");
    doc.setTextColor(0, 0, 0);
    doc.text("Bank Name: Uco Bank, Subzi Mandi - Ujjain", 14, footerY + 5);
    doc.text("Bank Account No.: 06860510000335", 14, footerY + 10);
    doc.text("Bank IFSC code: UCBA0000686", 14, footerY + 15);
    doc.text("Account Holder's Name: RC Industries", 14, footerY + 20);

    // Description section
    const descY = footerY + 29;
    doc.setFont("helvetica", "bold");
    doc.setTextColor(220, 38, 38);
    doc.text("Description", 14, descY);
    doc.setFont("helvetica", "normal");
    doc.setTextColor(0, 0, 0);
    const descriptionLines = [
        "Alankar Speciality Cables Pvt Ltd",
        "Shipping address: Plot no. 69 DMIC Vikram Udyogpuri",
        "Near Village Narwar, Ujjain M.P. 456664"
    ];
    descriptionLines.forEach((line, index) => {
        doc.text(line, 14, descY + 5 + (index * 5));
    });

    // Amount in words
    const amountWordsY = descY + (descriptionLines.length * 5) + 10;
    doc.setFont("helvetica", "bold");
    doc.setTextColor(220, 38, 38);
    doc.text("Invoice Amount In Words", 14, amountWordsY);
    doc.setFont("helvetica", "normal");
    doc.setTextColor(0, 0, 0);
let amountInWords = numberToWords(parseFloat(billData.grandTotal));
let amountLines = doc.splitTextToSize(amountInWords, 120);
doc.text(amountLines, 14, amountWordsY + 5);

    // Signature section
    const signY = amountWordsY + 15;
    doc.setFillColor("white");
    doc.roundedRect(14, signY, 60, 30, 2, 2, 'F');
    doc.setDrawColor(220, 38, 38);
    doc.setLineWidth(0.5);
    doc.roundedRect(14, signY, 60, 30, 2, 2, 'S');
    doc.setFontSize(10).setFont("helvetica", "normal");
    doc.text("For: RC Industries", 19, signY + 10);
    doc.text("Authorized Signatory", 19, signY + 20);
    doc.setDrawColor(220, 38, 38);
    doc.circle(80, signY + 15, 10);

    // Save the PDF
    doc.save(`RC_Industries_Invoice_${new Date().toISOString().slice(0,10)}.pdf`);
}
        // Helper for PHP's number_format in JS
        function number_format(number, decimals) {
            return parseFloat(number).toLocaleString('en-IN', {minimumFractionDigits: decimals, maximumFractionDigits: decimals});
        }
    </script>
</body>
</html>
