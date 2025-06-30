<?php  
require '../db_connect.php';

if (!isset($_GET['order_id'])) {
    die("Invalid Order ID.");
}

$order_id = intval($_GET['order_id']);

// Fetch Order and Customer Details
$query = "SELECT po.order_id, c.name AS customer_name, c.phone_no, c.address, c.city, 
                 o.final_amount, o.rent_amount AS transport_rent
          FROM pending_orders po
          JOIN customers c ON po.customer_id = c.customer_id
          JOIN orders o ON po.order_id = o.order_id
          WHERE po.order_id = ?
          GROUP BY po.order_id, c.name, c.phone_no, c.address, c.city, o.total_amount";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// Fetch Ordered Products (Including Multiplier)
$product_query = "SELECT p.product_name, po.quantity, po.original_price, po.custom_price, po.multiplier 
                  FROM pending_orders po
                  JOIN products p ON po.product_id = p.product_id
                  WHERE po.order_id = ?";
$stmt = $mysqli->prepare($product_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$product_result = $stmt->get_result();
$products = $product_result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_bill'])) {
    $orderId    = (int)$_POST['order_id'];
    $rent       = (float)$_POST['rent'];
    $grandTotal = (float)$_POST['grand_total'];
    $discounted = (float)$_POST['discounted_amount'];

    $stmt = $mysqli->prepare(
        "UPDATE orders SET transport_rent = ?, discounted_amount = ? WHERE order_id = ?"
    );
    $stmt->bind_param('ddi', $rent, $discounted, $orderId);

    if (!$stmt->execute()) {
        die('UPDATE failed: ' . $stmt->error);
    }

    header("Location: generate_bill.php?order_id=$orderId&saved=1");
    exit;
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>RC Industries Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <link href="https://use.fontawesome.com/releases/v6.3.0/css/all.css" rel="stylesheet">
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
        .table th, .table td {
            vertical-align: middle !important;
            font-size: 1.08rem;
        }
        .table tfoot th {
            background:rgb(244, 241, 241);
            font-weight: 600;
            font-size: 1.08rem;
        }
        .table-dark th {
            background: #0d6efd !important;
            color: #fff !important;
            font-size: 1.09rem;
        }
        .btn-success {
            min-width: 160px;
            font-size: 1.08rem;
            padding: 0.6rem 1.5rem;
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
    <link href="https://use.fontawesome.com/releases/v6.3.0/css/all.css" rel="stylesheet">
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
            min-width: 160px;
            font-size: 1.08rem;
            padding: 0.6rem 1.5rem;
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
            <div class="text-muted fs-6">Bill Generation</div>
        </div>

        <div class="customer-info-box row mb-4">
            <div class="col-md-4 col-12 mb-2"><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
            <div class="col-md-4 col-12 mb-2"><strong>Phone:</strong> <?= htmlspecialchars($order['phone_no']) ?></div>
            <div class="col-md-4 col-12"><strong>Address:</strong> <?= htmlspecialchars($order['address']) . ', ' . htmlspecialchars($order['city']) ?></div>
        </div>

        <form id="billForm" method="post">
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <label class="fw-bold">Final Amount Paid:</label>
                    <input type="number" class="form-control" id="finalAmountPaid" value="<?= $order['final_amount']; ?>" oninput="calculateTotals()" disabled>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="fw-bold">Rent:</label>
                    <input type="number" class="form-control" id="rentAmount" value="<?= $order['transport_rent']; ?>" oninput="calculateTotals()"  disabled >
                </div>
                <div class="col-md-4 mb-2">
                    <label class="fw-bold">Date:</label>
                    <input type="text" class="form-control" value="<?= date('Y-m-d'); ?>" disabled>
                </div>
            </div>

            <?php if(isset($_GET['saved'])): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                Bill details saved!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle shadow-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Original Price ₹</th>
                            <th>Discounted Price ₹</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody id="billTable">
                        <?php
                            $index  = 1;
                            foreach ($products as $product):
                                $qty        = (int)$product['quantity'];
                                $origUnit   = (float)$product['original_price'];
                                $discounted = $qty ? $product['custom_price'] / $qty : 0;
                                $total      = $product['custom_price'];
                        ?>
                        <tr class="product-row   " style="background-color: #fff; color: #000;"
                            data-unit="<?= $discounted ?>"
                            data-orig='<?= $origUnit ?>'
                            data-qty="<?= $qty ?>">
                            <td><?= $index++; ?></td>
                            <td><?= htmlspecialchars($product['product_name']); ?></td>
                            <td><?= $qty; ?></td>
                            <td>₹<?= number_format($origUnit,   2); ?></td>
                            <td>₹<?= number_format($discounted, 2); ?></td>
                            <td>₹<?= number_format($total,      2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><th colspan="5" class="text-end">Item&nbsp;Total:</th><th id="itemTotal">₹0.00</th></tr>
                        <tr><th colspan="5" class="text-end">Rent:</th><th id="rentDisplay">₹0.00</th></tr>
                        <tr><th colspan="5" class="text-end">Discount:</th><th id="discountDisplay">₹0.00</th></tr>
                        <tr class="table-dark"><th colspan="5" class="text-end fw-bold">Grand&nbsp;Total:</th><th id="grandTotal" class="fw-bold">₹0.00</th></tr>
                    </tfoot>
                    <input type="hidden" name="order_id"     value="<?= $order_id ?>">
                    <input type="hidden" name="rent"         id="rentInput">
                    <input type="hidden" name="grand_total"  id="grandTotalInput">
                    <input type="hidden" name="discounted_amount" id="discountedAmountInput">
                </table>
            </div>

            <div class="text-center mt-4">
                <button class="btn btn-success shadow me-2" type="button" onclick="downloadPDF()">
                    <i class="fas fa-download me-2"></i>Download PDF
                </button>
                <button type="submit" class="btn btn-success shadow" name="save_bill" onclick="pushTotalsToHidden()">
                    <i class="fas fa-save"></i> Save Bill
                </button>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(applyFinalPrice, 100);
        });

        function applyFinalPrice() {
            let finalAmount = parseFloat(document.getElementById("finalAmountPaid").value) || 0;
            let totalAmount = Array.from(document.querySelectorAll(".final-price")).reduce((sum, row) => 
                sum + (parseFloat(row.getAttribute("data-original-price")) || 0) * 
                      (parseFloat(row.getAttribute("data-quantity")) || 0), 0);

            if (finalAmount > totalAmount || finalAmount <= 0) {
                console.warn("Invalid final amount. Discount not applied.");
                return;
            }

            let discount = totalAmount - finalAmount;
            let allProducts = document.querySelectorAll(".final-price");
            let totalOriginalAmount = Array.from(allProducts).reduce((sum, row) => 
                sum + (parseFloat(row.getAttribute("data-original-price")) || 0) * 
                      (parseFloat(row.getAttribute("data-quantity")) || 0), 0);

            let remainingDiscount = discount;
            let lastIndex = allProducts.length - 1;

            allProducts.forEach((row, index) => {
                let originalPrice = parseFloat(row.getAttribute("data-original-price")) || 0;
                let quantity = parseFloat(row.getAttribute("data-quantity")) || 0;
                let discountShare = (originalPrice * quantity / totalOriginalAmount) * discount;

                if (index === lastIndex) discountShare = remainingDiscount;
                let newPrice = Math.max(originalPrice - (discountShare / quantity), 0);
                
                row.innerText = `₹${newPrice.toFixed(2)}`;
                let totalPriceElement = row.closest("tr").querySelector(".total-price");
                totalPriceElement.innerText = `₹${(newPrice * quantity).toFixed(2)}`;

                remainingDiscount -= discountShare;
            });
        }

        function calculateTotals() {
            const finalPaid = +document.getElementById("finalAmountPaid").value || 0;
            const rent      = +document.getElementById("rentAmount").value     || 0;

            /* ---- gather rows ---- */
            const rows = document.querySelectorAll("tr.product-row");

            const itemTotal = [...rows].reduce(           // discounted (custom) total
                (sum, tr) => sum + (+tr.dataset.unit || 0) * (+tr.dataset.qty || 0), 0);

            const origTotal = [...rows].reduce(           // original-MRP total
                (sum, tr) => sum + (+tr.dataset.orig || 0) * (+tr.dataset.qty || 0), 0);

            /* ---- discount wanted: (origTotal  –  finalPaid) ---- */
            const discountAmt = Math.max(origTotal - finalPaid, 0);

            /* ---- grand total: what customer actually pays ---- */
            const grand = finalPaid + rent;

            /* ---- write to UI ---- */
            document.getElementById("itemTotal").textContent     = `₹${itemTotal.toFixed(2)}`;
            document.getElementById("rentDisplay").textContent   = `₹${rent.toFixed(2)}`;
            document.getElementById("discountDisplay").textContent = `₹${discountAmt.toFixed(2)}`;
            document.getElementById("grandTotal").textContent    = `₹${grand.toFixed(2)}`;
        }

const logoBase64 = "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAYGBgYHBgcICAcKCwoLCg8ODAwODxYQERAREBYiFRkVFRkVIh4kHhweJB42KiYmKjY+NDI0PkxERExfWl98fKcBBgYGBgcGBwgIBwoLCgsKDw4MDA4PFhAREBEQFiIVGRUVGRUiHiQeHB4kHjYqJiYqNj40MjQ+TERETF9aX3x8p//CABEIAfQB9AMBIgACEQEDEQH/xAAxAAEAAwEBAQAAAAAAAAAAAAAABAUGAwIBAQEAAwEBAAAAAAAAAAAAAAAAAgMFBAH/2gAMAwEAAhADEAAAAtUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABU2yNmX83Wfo1rDrMn2cdD50ER5ysMhYeWX4uzgAAAAAAAAAAAAAAAAAAAAAAAAAAIma0ua59bQz4E+3PCdOYj+/nJv61n+F2bp0KbZyB7EAAAAAAAAAAAAAAAAAAAAAAAACJmtLmufWt5sP3Kn3VxVfWta7WSp+U9yuz66xEQlAAAAAAAAAAAAAAAAAAAAAAAAACJmtLmufW0M+BPtz8/X67J1aPXU4/VSp7C7OAIM7yYewAAAAAAAAAAAAAAAAAAAAAAAFdGfTOSotGvf2OXtLOO0rrFPkx8mxpefZ1/3J2FvDeRqmD57L0XDvOgJ84AAAAAAAAAAAAAAAAAAAAAAD59ePL0S8vQD2Lj2ee0MfTK+uiueiVITpAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//xAAC/9oADAMBAAIAAwAAACEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABLnAEUAAAAAAAAAAAAAAAAAAAAAAAAAAAAIICgoEAAAAAAAAAAAAAAAAAAAAAAAAAAKoNsEMAAAAAAAAAAAAAAAAAAAAAAAAAAIKUkAAAAAAAAAAAAAAAAAAAAAAAAAAAKm1MUQOAAAAAAAAAAAAAAAAAAAAAAAAAPDAAMMMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD/xAAC/9oADAMBAAIAAwAAABDzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz3QZ9DzzzzzzzzzzzzzzzzzzzzzzzzzzzxRf3vDXzzzzzzzzzzzzzzzzzzzzzzzzzxQj2Zx7zzzzzzzzzzzzzzzzzzzzzzzzzxRfzDzzTzzzzzzzzzzzzzzzzzzzzzzzzvXi6Zq1bzzzzzzzzzzzzzzzzzzzzzzzz847y16/wA888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888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";

        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: "p", unit: "mm", format: "a4" });

            // Add company logo
            // Note: You'll need to replace this with your actual logo path
          // Add company logo (uncomment and adjust path if needed)
// doc.addImage('rctiles/images/bill logo.jpg', 'JPEG', 15, 10, 30, 15);

// ---------- Top Header Section with Dark Gray Background ----------
doc.setFillColor(220, 38, 38); // Dark gray background
doc.rect(0, 0, 210, 30, 'F');

// Left side content (gray bar)
doc.setFontSize(10).setFont("helvetica", "bold");
doc.setTextColor(255, 255, 255); // White text

// RC Industries name (left side)
doc.text("RC INDUSTRIES", 15, 12);

// GST and State (left side)
doc.setFontSize(8).setFont("helvetica", "normal");
doc.text("GSTIN: 23AAKFRB273NTZJ", 15, 17);
doc.text("State: 23-Madhya Pradesh", 15, 22);

// Remove the RC TILES text and center the logo
const pageWidth = doc.internal.pageSize.getWidth(); // Get full page width (210mm for A4)
const logoWidth = 30; // Your logo width in mm
const logoHeight = 20; // Your logo height in mm
const logoX = (pageWidth - logoWidth) / 2; // Calculate center position
const logoY = 5;

doc.addImage(logoBase64, 'JPEG', logoX, logoY, logoWidth, logoHeight);

// Right-aligned contact information (raised to match left side)
doc.setFontSize(10).setFont("helvetica", "normal");
doc.setTextColor(255, 255, 255); // White text
const rightMargin = 195;
doc.text("Phone: 1234567890", rightMargin, 12, { align: "right" });
doc.text("Email: rc@gmail.com", rightMargin, 17, { align: "right" });
doc.text("Address: 7671 MAXI ROAD LIDYOSPUR", rightMargin, 22, { align: "right" });


            // ---------- Document Header Sections ----------
const startY = 40; // Added top margin by increasing initial Y position
// Left Side: Bill To Section
doc.setFontSize(10).setFont("helvetica", "bold");
doc.setTextColor(150, 0, 0); // Reddish header
doc.text("Bill To", 14, startY);

// Dynamic Customer Details
try {
    // Customer Name (larger font)
    doc.setFontSize(15).setFont("helvetica", "bold"); // Increased from 10 to 12
    doc.setTextColor(0, 0, 0);
    const customerName = document.querySelector(".col-md-4.col-12.mb-2 strong").nextSibling.nodeValue.trim();
    doc.text(customerName, 14, startY + 7); // Adjusted y-position
    
    // Customer Address (dynamic)
    doc.setFontSize(10).setFont("helvetica", "normal"); // Reset to normal for address
    const customerInfo = document.querySelectorAll(".customer-info-box div");
    const customerAddress = customerInfo[2]?.querySelector("strong")?.nextSibling?.nodeValue?.trim() || "Address Not Available";
    const addressLines = doc.splitTextToSize(customerAddress, 80);
    
    let addressY = startY + 13; // Adjusted spacing
    addressLines.forEach(line => {
        doc.text(line, 14, addressY);
        addressY += 6;
    });
    
 // GSTIN and State (semi-bold labels with normal values)
doc.setFont("helvetica", "bold");
 doc.setTextColor(10, 10, 10); // Semi-bold for labels
doc.text("GSTIN Number: ", 14, addressY + 1);
doc.text("State: ", 14, addressY + 8);

// Switch to normal for values
doc.setFont("helvetica", "normal");
const gstinX = 14 + doc.getTextWidth("GSTIN Number: ");
const stateX = 14 + doc.getTextWidth("State: ");

doc.text(" 23AAYCAG150A1ZV", gstinX, addressY + 1);
doc.text(" 23-Madhya Pradesh", stateX, addressY + 8);

} catch (e) {
    console.error("Error loading customer data:", e);
    doc.setFontSize(12).setFont("helvetica", "semibold");
    doc.text("Customer Data Loading Error", 14, startY + 7);
}

// ---------- Tax Invoice Box with White Text ----------
// Red "Tax Invoice" heading (unchanged)
// Tax Invoice Section (shifted down by 5 units)
const invoiceY = startY + 5;  // Shift down from original startY

// Tax Invoice Heading (red)
doc.setFontSize(10).setFont("helvetica", "bold");
doc.setTextColor(150, 0, 0);
doc.text("Tax Invoice", 140, invoiceY);

// Invoice Details (black)
doc.setFontSize(10).setFont("helvetica", "normal");
doc.setTextColor(0, 0, 0);
doc.text("Invoice No.: 20", 140, invoiceY + 7);
doc.text("Date: " + new Date().toLocaleDateString(), 140, invoiceY + 14);

// Horizontal line separator
const separatorY = startY + 32; // Slightly increased for new spacing
doc.setLineWidth(0.2).line(14, separatorY, 196, separatorY);

            // ---------- Product Table with Rounded Borders ----------
            const tableBody = [];
            document.querySelectorAll("#billTable tr").forEach((tr, i) => {
                const cells = tr.querySelectorAll("td");
                tableBody.push([
                    cells[0].innerText,  // #
                    cells[1].innerText,  // Item Name
                    cells[2].innerText,  // Quantity
                    "Box",               // Unit
                    cells[4].innerText.replace('₹', ''),  // Price/Unit
                    cells[5].innerText.replace('₹', '')   // Amount
                ]);
            });

            // Add totals row
            tableBody.push([
                "",
                "Total",
                document.querySelectorAll("#billTable tr")[0].querySelector("td:nth-child(3)").innerText, // Quantity
                "",
                "",
                document.getElementById("grandTotal").textContent.replace('₹', '')
            ]);

            // Custom table styling for rounded borders
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
                    textColor: [0, 0, 0] // Make all table text black by default
                },
                headStyles: {
                    fillColor: [220, 38, 38], // Red header
                    textColor: 255, // Black text
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
                    5: { cellWidth: 25, halign: 'right' },
                    6: { cellWidth: 25, halign: 'right' }
                },
                didDrawCell: (data) => {
                    if (data.section === 'body' && data.row.index === tableBody.length - 1) {
                        doc.setFillColor(220, 38, 38); // Red for total row
                        doc.roundedRect(data.cell.x, data.cell.y, data.cell.width, data.cell.height, 1, 1, 'F');
                        doc.setTextColor(255, 255, 255); // White text for total row
                    }
                    // Draw inner borders
                    doc.setDrawColor(200, 200, 200);
                    doc.setLineWidth(0.1);
                    doc.line(data.cell.x, data.cell.y, data.cell.x + data.cell.width, data.cell.y);
                    doc.line(data.cell.x, data.cell.y, data.cell.x, data.cell.y + data.cell.height);
                },
                willDrawCell: (data) => {
                    // Round corners for first and last cells in row
                    if (data.column.index === 0 || data.column.index === data.table.columns.length - 1) {
                        doc.setLineWidth(0.5);
                        doc.setDrawColor(150, 150, 150);
                        doc.roundedRect(data.cell.x, data.cell.y, data.cell.width, data.cell.height, 2, 2, 'S');
                    }
                }
            });

            // ---------- Tax Summary Table (Smaller and on Right) ----------
            const taxSummaryY = doc.autoTable.previous.finalY + 10;
            // Calculate dynamic tax summary
const subTotal = parseFloat(document.getElementById("itemTotal").textContent.replace('₹','')) || 0;
const grandTotal = parseFloat(document.getElementById("grandTotal").textContent.replace('₹','')) || 0;
const rentAmount = parseFloat(document.getElementById("rentAmount").value) || 0;
const discountAmt = parseFloat(document.getElementById("discountDisplay").textContent.replace('₹','')) || 0;
const paidAmount = parseFloat(document.getElementById("finalAmountPaid").value) || 0;
const total = +(subTotal + rentAmount).toFixed(2);
const remainingAmount = Math.max(grandTotal - paidAmount, 0);

const taxSummaryBody = [
    ["Sub Total", `₹${subTotal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`],
    ["Rent", `₹${rentAmount.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`],
    ["Discount", `₹${discountAmt.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`],
   ["Total", `₹${total.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`],
     ["Paid Amount", `₹${paidAmount.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`],
    ["Remaining Amount", `₹${remainingAmount.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`],
   
];

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
                    fillColor: [220, 38, 38], // Vibrant red (Tailwind Red-600)
                    textColor: 255,
                    fontStyle: 'bold'
                },
                columnStyles: {
                    0: { cellWidth: 28, halign: 'left' },
                },
                didParseCell: function (data) {
                    // Style the 'Total' row in red
                    if (data.section === 'body' && data.row.index === 3) {
                        data.cell.styles.fillColor = [220, 38, 38]; // Vibrant red for total row
                        data.cell.styles.textColor = 255; // White text
                        data.cell.styles.fontStyle = 'bold';
                    }
                    // Style the 'Sub Total', 'Rent', 'Discount', and 'Paid Amount' rows (bold only)
                    if (data.section === 'body' && (data.row.index === 0 || data.row.index === 1 || data.row.index === 2 || data.row.index === 4)) {
                        data.cell.styles.fontStyle = 'bold';
                    }
                }
            });

          // ---------- All Left-Aligned Content ----------
 const footerY = doc.autoTable.previous.finalY + 15;
    
    // 1. Pay To Section
    doc.setFontSize(10).setFont("helvetica", "bold");
    doc.setTextColor(150, 0, 0);
    doc.text("Pay To:", 14, footerY);
    
    doc.setFont("helvetica", "normal");
    doc.setTextColor(0, 0, 0);
    doc.text("Bank Name: Uco Bank, Subzi Mandi - Ujjain", 14, footerY + 5);
    doc.text("Bank Account No.: 06860510000335", 14, footerY + 10);
    doc.text("Bank IFSC code: UCBA0000686", 14, footerY + 15);
    doc.text("Account Holder's Name: RC Industries", 14, footerY + 20);
    
    // 2. Description Section (Added between Pay To and Amount in Words)
const descY = footerY + 29; // Space after Pay To
doc.setFont("helvetica", "bold");
doc.setTextColor(150, 0, 0);
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

// 3. Invoice Amount in Words (Moved below Description)
const amountWordsY = descY + (descriptionLines.length * 5) + 10; // Dynamic position
doc.setFont("helvetica", "bold");
doc.setTextColor(150, 0, 0);
doc.text("Invoice Amount In Words", 14, amountWordsY);

doc.setFont("helvetica", "normal");
 doc.setTextColor(0, 0, 0);
const amount = parseFloat(document.getElementById("grandTotal").textContent.replace('₹', ''));
let amountInWords = "Twenty Five Thousand Rupees only";
if (amount !== 25000) {
    amountInWords = "Amount in words would appear here";
}
const amountLines = doc.splitTextToSize(amountInWords, 120);
doc.text(amountLines, 14, amountWordsY + 5);

// 4. Signature Section (Moved further down)
const signY = amountWordsY + 15 // Increased spacing
doc.setFillColor(240, 240, 240);
doc.roundedRect(14, signY, 60, 30, 2, 2, 'F');
doc.setDrawColor(150, 0, 0);
doc.setLineWidth(0.5);
doc.roundedRect(14, signY, 60, 30, 2, 2, 'S');

doc.setFontSize(10).setFont("helvetica", "normal");
doc.text("For: RC Industries", 19, signY + 10);
doc.text("Authorized Signatory", 19, signY + 20);

// Seal/Stamp
doc.setDrawColor(150, 0, 0);
doc.circle(80, signY + 15, 10);


            // Save the PDF
            doc.save(`RC_Industries_Invoice_${new Date().toISOString().slice(0,10)}.pdf`);
        }

        function pushTotalsToHidden() {
            document.getElementById('rentInput').value = parseFloat(
                document.getElementById('rentAmount').value
            ) || 0;

            document.getElementById('grandTotalInput').value = parseFloat(
                document.getElementById('grandTotal').textContent.replace('₹','')
            ) || 0;

            document.getElementById('discountedAmountInput').value = parseFloat(
                document.getElementById('finalAmountPaid').value
            ) || 0;
        }

        document.getElementById('billForm')
                .addEventListener('submit', pushTotalsToHidden);

        // Initialize totals calculation
        document.addEventListener("DOMContentLoaded", calculateTotals);
    </script>
</body>
</html>