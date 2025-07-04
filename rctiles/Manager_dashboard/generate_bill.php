<?php  
require '../db_connect.php';

if (!isset($_GET['order_id'])) {
    die("Invalid Order ID.");
}

$order_id = intval($_GET['order_id']);

// Fetch Order and Customer Details
$query = "SELECT po.order_id, c.name AS customer_name, c.phone_no, c.address, c.city, 
                 o.total_amount AS final_amount
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
    <title>RC Mall Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jsPDF Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

    <!-- jsPDF AutoTable Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
   
</head>

<body class="sb-nav-fixed">
     <?php include "manager_header.php"; ?>
     <div id="layoutSidenav_content">
        <main>
            <div class="container-box">
                <h2 class="text-center mt-4 fw-bold pb-3">RC Mall Bill</h2>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold">Customer Name:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['customer_name']); ?>" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Address:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['address']) . ', ' . htmlspecialchars($order['city']); ?>" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Phone:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['phone_no']); ?>" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Final Amount Paid:</label>
                        <input type="number" class="form-control" id="finalAmountPaid" value="<?php echo $order['final_amount']; ?>" oninput="calculateTotals()">
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Freight:</label>
                        <input type="number" class="form-control" id="rentAmount" value="0" oninput="calculateTotals()">
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Date:</label>
                        <input type="text" class="form-control" value="<?php echo date('Y-m-d'); ?>" disabled>
                    </div>
                    <!-- Discount & GST UI -->
                    <!-- <div class="col-md-4">
                    <label class="fw-bold">GST %:</label>
                    <input type="number" id="gstPercent" class="form-control"
                            value="0" min="0" max="28" step="0.01" oninput="calculateTotals()">
                    </div> -->

                </div>
                <?php if(isset($_GET['saved'])): ?>
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    Bill details saved!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                <form id="billForm" method="post">
                    <div class="table-responsive">
                        <!-- ⬇︎  OPEN the form just before the table (or higher, if you prefer) -->
                
                        <table class="table table-bordered">
                            <thead>
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
                                    $totalOriginalAmount = 0;

                                    foreach ($products as $product):
                                        $qty        = (int)$product['quantity'];
                                        $origUnit   = (float)$product['original_price'];
                                    
                                        /* NEW ---------------------------------------- */
                                        $discounted = $qty ? $product['custom_price'] / $qty : 0;   // per-unit
                                        $total      = $product['custom_price'];                     // line-total
                                    ?>
                                    <tr class="product-row"
                                        data-unit="<?= $discounted ?>"
                                        data-orig='<?= $origUnit ?>'   
                                        data-qty="<?= $qty ?>">
                                        <td><?= $index++; ?></td>
                                        <td><?= htmlspecialchars($product['product_name']); ?></td>
                                        <td><?= $qty; ?></td>

                                        <td>₹<?= number_format($origUnit,   2); ?></td>   <!-- Original -->
                                        <td>₹<?= number_format($discounted, 2); ?></td>   <!-- NEW -->
                                        <td>₹<?= number_format($total,      2); ?></td>   <!-- CHANGED -->
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>

                            <!-- <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th id="totalAmount">₹<span id="totalAmountValue"><?php echo number_format($order['final_amount'], 2); ?></span></th>

                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Rent:</th>
                                    <th id="rentDisplay">₹0</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Grand Total:</th>
                                    <th id="grandTotal">₹<?php echo number_format($totalOriginalAmount, 2); ?></th>
                                </tr>
                            </tfoot> -->
                            <tfoot>
                                <tr><th colspan="5" class="text-end">Item&nbsp;Total:</th><th id="itemTotal">₹0.00</th></tr>
                                <tr><th colspan="5" class="text-end">Freight:</th><th id="rentDisplay">₹0.00</th></tr>
                                <!-- <tr><th colspan="5" class="text-end">GST:</th><th id="gstDisplay">₹0.00</th></tr> -->
                                <tr><th colspan="5" class="text-end">Discount:</th><th id="discountDisplay">₹0.00</th></tr>
                                <tr class="table-dark"><th colspan="5" class="text-end fw-bold">Grand&nbsp;Total:</th><th id="grandTotal" class="fw-bold">₹0.00</th></tr>
                            </tfoot>

                            <input type="hidden" name="order_id"     value="<?= $order_id ?>"> <!-- if not already there -->
                            <input type="hidden" name="rent"         id="rentInput">
                            <input type="hidden" name="grand_total"  id="grandTotalInput">
                            <input type="hidden" name="discounted_amount" id="discountedAmountInput">

                        </table>
                    </div>

                    <button class="btn btn-success" onclick="downloadPDF()">Download PDF</button>
                    <button type="submit" class="btn btn-success" name="save_bill" onclick="pushTotalsToHidden()">
                        <i class="fas fa-save"></i> Save Bill
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
<script>
         document.addEventListener("DOMContentLoaded", function() {
        setTimeout(applyFinalPrice, 100);
      });

      function applyFinalPrice() {
          let finalAmount = parseFloat(document.getElementById("finalAmountPaid").value) || 0;
          let totalAmount = Array.from  (document.querySelectorAll(".final-price")).reduce((sum, row) => 
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

  

        // function calculateTotals() {
        //     const finalPaid = +document.getElementById("finalAmountPaid").value || 0;
        //     const rent      = +document.getElementById("rentAmount").value     || 0;
        //     const gstPct    = +document.getElementById("gstPercent").value     || 0;

        //     /* ------------- item total ------------- */
        //     const rows = document.querySelectorAll("tr.product-row");
        //     const itemTotal = [...rows].reduce(
        //         (sum, tr) => sum + (+tr.dataset.unit || 0) * (+tr.dataset.qty || 0), 0);

        //     /* ------------- discount --------------- */
        //     const discountAmt = Math.max(itemTotal - finalPaid, 0);

        //     /* ------------- GST -------------------- */
        //     const gstAmt = (finalPaid + rent) * gstPct / 100;

        //     /* ------------- grand total ------------ */
        //     const grand = itemTotal + rent + gstAmt - discountAmt;

        //     /* ------------- write to UI ------------ */
        //     document.getElementById("itemTotal").textContent    = `₹${itemTotal.toFixed(2)}`;
        //     document.getElementById("rentDisplay").textContent  = `₹${rent.toFixed(2)}`;
        //     document.getElementById("gstDisplay").textContent   = `₹${gstAmt.toFixed(2)}`;
        //     document.getElementById("discountDisplay").textContent = `₹${discountAmt.toFixed(2)}`;
        //     document.getElementById("grandTotal").textContent   = `₹${grand.toFixed(2)}`;
        // }
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



            /* auto-run */
            document.addEventListener("DOMContentLoaded", calculateTotals);


        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: "p", unit: "mm", format: "a4" });

            // ---------- Header ----------
            doc.setFontSize(18).setFont("helvetica","bold");
            doc.text("RC Mall – Customer Bill", 105, 15, { align: "center" });
            doc.setLineWidth(0.5).line(14,18,196,18);

            // ---------- Customer block ----------
            doc.setFontSize(11).setFont("helvetica","normal");
            doc.text(`Name  : ${document.querySelectorAll("input[disabled]")[0].value}`, 14, 28);
            doc.text(`Phone : ${document.querySelectorAll("input[disabled]")[2].value}`, 14, 33);
            doc.text(`Date  : ${document.querySelectorAll("input[disabled]")[3].value}`, 14, 38);

            // ---------- Product table ----------
            const tableBody = [];
            document.querySelectorAll("#billTable tbody tr").forEach((tr,i)=>{
                const cells = tr.querySelectorAll("td");
                tableBody.push([
                    i + 1,
                    cells[1].innerText,  // Item
                    cells[2].innerText,  // Qty
                    cells[4].innerText,  // Unit ₹  (discounted)
                    cells[5].innerText   // Total ₹ (custom_price)
                ]);
            });

            doc.autoTable({
                head: [["#", "Item", "Qty", "Unit ₹", "Total ₹"]],
                body: tableBody,
                startY: 44,
                theme: "grid",
                headStyles: { fillColor:[0,123,255], textColor:255 },
                styles:     { halign:"center", valign:"middle" }
            });

            const y = doc.autoTable.previous.finalY + 6;
            const get = id => document.getElementById(id).value || document.getElementById(id).textContent;

            // doc.text(`Discount  :  ₹${get("discountAmount")}  (${get("discountPercent")})`, 14, y);
            // doc.text(`Rent      :  ₹${get("rentAmount")}`,                             14, y+5);
            // doc.text(`GST (${get("gstPercent")}%) :  ₹${get("gstAmount")}`,            14, y+10);
            // doc.setFont("helvetica","bold");
            // doc.text(`Grand Total :  ${document.getElementById("grandTotal").textContent}`, 14, y+17);

            doc.save(`RCMall_Bill_${new Date().toISOString().slice(0,10)}.pdf`);
            /* ---------- summary table ---------- */
            const summaryBody = [
                ["Item Total",  document.getElementById("itemTotal").textContent],
                ["Rent",        document.getElementById("rentDisplay").textContent],
                ["Discount",    document.getElementById("discountDisplay").textContent],
                ["Grand Total", document.getElementById("grandTotal").textContent]
            ];


            doc.autoTable({
                head: [["Description", "Amount"]],
                body: summaryBody,
                startY: doc.autoTable.previous.finalY + 6,
                theme: "grid",
                styles: { halign:"right" },
                headStyles: { fillColor:[40,167,69], textColor:255 }
            });

        }

       
       function pushTotalsToHidden (){
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

    </script>
</html>
