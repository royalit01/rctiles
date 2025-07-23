<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}

if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}

include '../db_connect.php';

// Support both POST and GET for order_id
if (isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
} elseif (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
} else {
    $order_id = '';
}
$order_id_int = is_numeric($order_id) ? (int)$order_id : 0;
$stmt = $mysqli->prepare("SELECT product_id, product_name, quantity, original_price, custom_price FROM pending_orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id_int);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

// Fetch customer details using order_id
$customer = [
    'name' => '',
    'phone_no' => '',
    'city' => '',
    'address' => ''
];
if ($order_id_int > 0) {
    $stmt = $mysqli->prepare("SELECT c.name, c.phone_no, c.city, c.address
                              FROM orders o
                              JOIN customers c ON o.customer_id = c.customer_id
                              WHERE o.order_id = ?");
    $stmt->bind_param("i", $order_id_int);
    $stmt->execute();
    $stmt->bind_result($name, $phone_no, $city, $address);
    if ($stmt->fetch()) {
        $customer = [
            'name' => $name,
            'phone_no' => $phone_no,
            'city' => $city,
            'address' => $address
        ];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>New Order</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- jQuery (Required for Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
window.productsFromPHP = <?php echo json_encode($products ?? []); ?>;

// Initialize selectedProductsData from productsFromPHP
let selectedProductsData = { "0": { wall: [], floor: [] } };
if (window.productsFromPHP && window.productsFromPHP.length > 0) {
    selectedProductsData["0"].wall = window.productsFromPHP.map((product) => ({
        id: product.product_id, // use real product_id from DB
        name: product.product_name,
        quantity: parseInt(product.quantity) || 1,
        unitPrice: parseFloat(product.custom_price ?? product.original_price) || 0,
        totalPrice: (parseInt(product.quantity) || 1) * (parseFloat(product.custom_price ?? product.original_price) || 0),
        currentTotalPrice: (parseInt(product.quantity) || 1) * (parseFloat(product.custom_price ?? product.original_price) || 0),
        originalTotalPrice: (parseInt(product.quantity) || 1) * (parseFloat(product.original_price) || 0)
    }));
}
document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
});

function updateTotalAmount() {
    let total = 0;
    document.querySelectorAll('.final-price').forEach(cell => {
        // Extract the price from the text content (removing the ₹ symbol)
        total += parseFloat(cell.getAttribute("data-current-price")) || 0;
    });
    document.getElementById("totalAmount").textContent = `₹${total.toFixed(2)}`;
    document.getElementById("finalAmountPaid").value = total.toFixed(2);
    document.getElementById("final_price").value = total.toFixed(2);
}

const submitBtn = document.querySelector('#orderForm button[type="submit"]');
if (submitBtn) {
    submitBtn.type = "button";
    submitBtn.id = "openConfirmModalBtn";
}

function applyFinalPrice() {
    let finalAmountInput = document.getElementById("finalAmountPaid");
    let finalAmount = parseFloat(finalAmountInput.value) || 0;

    document.getElementById("final_price").value = finalAmount;
    let totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace("₹", "")) || 0;

    if (finalAmount > totalAmount || finalAmount <= 0) {
        console.warn("Invalid final amount. Discount not applied.");
        return;
    }

    let discount = totalAmount - finalAmount;
    let allProducts = document.querySelectorAll(".final-price");

    // Calculate total original amount from data attributes
    let totalOriginalAmount = 0;
    allProducts.forEach(row => {
        totalOriginalAmount += parseFloat(row.getAttribute("data-original-price")) || 0;
    });

    if (allProducts.length === 0 || totalOriginalAmount <= 0) {
        return;
    }

    let remainingDiscount = discount;
    let lastIndex = allProducts.length - 1;

    allProducts.forEach((row, index) => {
        let originalPrice = parseFloat(row.getAttribute("data-original-price")) || 0;
        let discountShare = (originalPrice / totalOriginalAmount) * discount;

        if (index === lastIndex) {
            discountShare = remainingDiscount;
        }

        let newPrice = Math.max(originalPrice - discountShare, 0);
        row.textContent = `₹${newPrice.toFixed(2)}`;
        row.setAttribute("data-current-price", newPrice.toFixed(2));

        // Update product data in the data structure if needed
        const productId = row.getAttribute("data-id");
        if (productId) {
            Object.values(selectedProductsData).forEach(section => {
                [...section.wall, ...section.floor].forEach(product => {
                    if (product.id == productId) {
                        product.currentTotalPrice = newPrice;
                    }
                });
            });
        }

        remainingDiscount -= discountShare;
    });

    // Don't update the finalAmountPaid input, but do update the hidden field
    document.getElementById("final_price").value = finalAmount;
    updateGrandAmount();
}// Add this at the top of your <script> block or before any usage

document.addEventListener("DOMContentLoaded", () => {

    // Intercept the submit button to show the confirmation modal
    const submitBtn = document.querySelector('#orderForm button[type="submit"]');
    if (submitBtn) {
        submitBtn.type = "button";
        submitBtn.id = "openConfirmModalBtn";
    }

    // Show confirmation modal on submit button click
    document.getElementById('openConfirmModalBtn').addEventListener('click', function() {
        // Optionally, you can run validation here before showing the modal
        var confirmModal = new bootstrap.Modal(document.getElementById('confirmSubmitModal'));
        confirmModal.show();
    });

    // On confirm, trigger the form submit handler
    document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
        document.getElementById('orderForm').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}));
    });

    // Main form submit handler
    document.getElementById("orderForm").addEventListener("submit", function(event) {
        // Prevent default form submission
        event.preventDefault();

        console.log("Form submission initiated");

        // Validate final amount
        let finalAmount = parseFloat(document.getElementById("finalAmountPaid").value) || 0;
        if (finalAmount <= 0) {
            alert("Final amount must be greater than 0");
            return;
        }

        // Check if we have any products selected
        let hasProducts = false;
        let productsArray = [];

        // Build products array from all detail sections
        Object.entries(selectedProductsData).forEach(([sectionId, section]) => {
            // Always get the latest values from the summary table inputs
            let multiplier = 1;
            let detailGroup = document.querySelector(`[data-section-id="${sectionId}"]`);
            if (detailGroup) {
                multiplier = parseInt(detailGroup.querySelector(".multiply-order")?.value) || 1;
            }

            ['wall', 'floor'].forEach(type => {
                if (section[type] && section[type].length > 0) {
                    hasProducts = true;
                    section[type].forEach((product, idx) => {
                        // Find the corresponding row in the summary table using data-index (use index, not id)
                        let row = document.querySelector(`.product-quantity[data-index="${idx}"]`)?.closest('tr');
                        let quantity = product.quantity;
                        let customPrice = product.unitPrice;
                        let finalPrice = product.totalPrice;
                        if (row) {
                            // Get the latest quantity and custom price from the inputs
                            quantity = parseInt(row.querySelector('.product-quantity')?.value) || product.quantity;
                            customPrice = parseFloat(row.querySelector('.fixed-price-input')?.value) || product.unitPrice;
                            finalPrice = parseFloat(row.querySelector('.final-price')?.getAttribute('data-current-price')) || (quantity * customPrice);
                        }
                        let productObj = {
                            id: product.id,
                            name: product.name,
                            quantity: quantity,
                            unitPrice: customPrice, // this is custom_price
                            totalPrice: finalPrice, // this is final_price
                            multiplier: multiplier
                        };
                        productsArray.push(productObj);
                    });
                }
            });
        });

        if (!hasProducts) {
            alert("No products selected. Please add at least one product.");
            return;
        }

        // Remove any previously added hidden inputs
        document.querySelectorAll(".hidden-product-input").forEach(input => input.remove());

        // Add products as a hidden input
        let productsInput = document.createElement("input");
        productsInput.type = "hidden";
        productsInput.name = "products";
        productsInput.className = "hidden-product-input";
        productsInput.value = JSON.stringify(productsArray);
        this.appendChild(productsInput);

        // Verify form data before submission
        const formData = new FormData(this);
        console.log("FormData contents:");
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

 
    this.submit();

    });

});

function updateSummary() {
    const summaryBody = document.getElementById("summaryBody");
    summaryBody.innerHTML = "";
    let totalAmount = 0;
    let products = window.productsFromPHP || [];
    if (products.length > 0) {
        products.forEach((product, index) => {
            let quantity = parseInt(product.quantity) || 1;
            let fixedPrice = parseFloat(product.custom_price ?? product.original_price) || 0;
            let originalPrice = parseFloat(product.original_price) || fixedPrice;
            let finalPrice = quantity * fixedPrice;
            totalAmount += finalPrice;
            summaryBody.insertAdjacentHTML("beforeend", `
                <tr>
                    <td>${product.product_name}</td>
                    <td><input type="number" class="form-control product-quantity" min="1" value="${quantity}" data-index="${index}" oninput="updateRowTotal(this)"></td>
                    <td>${originalPrice.toFixed(2)}</td>
                    <td><input type="number" class="form-control fixed-price-input" min="0" step="0.01" value="${fixedPrice.toFixed(2)}" data-index="${index}" oninput="updateRowTotal(this)"></td>
                    <td class="final-price" data-index="${index}" data-original-price="${finalPrice.toFixed(2)}" data-current-price="${finalPrice.toFixed(2)}">₹${finalPrice.toFixed(2)}</td>
                </tr>
            `);
        });
    }
    document.getElementById("totalAmount").textContent = `₹${totalAmount.toFixed(2)}`;
    document.getElementById("finalAmountPaid").value = totalAmount.toFixed(2);
    document.getElementById("final_price").value = totalAmount.toFixed(2);
    updateGrandAmount(); // <-- Add this line

}

function updateRowTotal(input) {
    const row = input.closest('tr');
    const index = parseInt(input.getAttribute('data-index'));
    const qtyInput = row.querySelector('.product-quantity');
    const priceInput = row.querySelector('.fixed-price-input');
    let qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
    let price = priceInput ? parseFloat(priceInput.value) || 0 : 0;
    let finalPrice = qty * price;
    const finalPriceCell = row.querySelector('.final-price');
    finalPriceCell.textContent = `₹${finalPrice.toFixed(2)}`;
    finalPriceCell.setAttribute('data-original-price', finalPrice.toFixed(2));
    finalPriceCell.setAttribute('data-current-price', finalPrice.toFixed(2));
    // Recalculate total
    let total = 0;
    document.querySelectorAll('.final-price').forEach(cell => {
        total += parseFloat(cell.getAttribute('data-current-price')) || 0;
    });
    document.getElementById('totalAmount').textContent = '₹' + total.toFixed(2);
    document.getElementById('finalAmountPaid').value = total.toFixed(2);
    document.getElementById('final_price').value = total.toFixed(2);
    applyFinalPrice();
        updateGrandAmount(); // <-- Add this line

}


  function updateGrandAmount() {
    let finalAmount = parseFloat(document.getElementById('finalAmountPaid').value) || 0;
    let rentAmount = parseFloat(document.getElementById('RentAmount').value) || 0;
    let returnAmount = parseFloat(document.getElementById('returnAmount').value) || 0;
    let grandAmount = finalAmount + rentAmount - returnAmount;
    document.getElementById('grandAmountPaid').value = grandAmount;
    document.getElementById('totalAmount').textContent = finalAmount ? `₹${finalAmount.toFixed(2)}` : '₹0.00';
    console.log('finalAmount:', finalAmount);
console.log('rentAmount:', rentAmount);
console.log('returnAmount:', returnAmount);
console.log('grandAmount:', grandAmount);
}

// ...existing code...
window.updateFinalAndRentFromGrand = function() {
    let grand = parseFloat(document.getElementById('grandAmountPaid').value) || 0;
    let rent = parseFloat(document.getElementById('RentAmount').value) || 0;
    let final = grand - rent;
    document.getElementById('finalAmountPaid').value = final.toFixed(2);
    applyFinalPrice();
};
// ...existing code...

    </script>
    <style>
        @media (max-width: 768px) {
    .modal-fullscreen .modal-content {
        padding: 10px;
    }
    .table {
        font-size: 14px;
    }
    .custom-price {
        width: 100%;
    }
}

        body {
            background-color: #f8f9fa;
        }

        .container-box {
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin: auto;
        }

        .form-step {
            display: none;
        }

        .active-step {
            display: block;
        }

        .dimension-group {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
            margin-bottom: 10px;
        }

        .summary-table {
            width: 100%;
            margin-top: 20px;
        }

        .summary-table th,
        .summary-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include "admin_header.php"; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container mt-4">
                <div class="container-box">
                    <h2 class="text-center mb-3">Continue Order</h2>
                    <div class="mb-3">
                        <strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?>
                    </div>
                                           <div class="table-responsive-sm">
                                    <table class="table table-bordered summary-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                 <th>Fixed Price</th>
                                                <th>Original Price</th>
                                                <th>Final Price</th>
                                            </tr>
                                        </thead>
                                        <tbody id="summaryBody"></tbody>
                                        <tfoot>
                                            <tr>
                                                    <td colspan="3" class="border-0"></td> 
                                                <th  class="text-end">Total Amount:</th>
                                                <th id="totalAmount">₹0.00</th>
                                            </tr>
                                        </tfoot>
                                    </table>
</div>
  <div class="mb-3">

    <form id="orderForm" method="POST" action="submit_order_estimate.php">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>">

<!-- Pre-filled customer fields -->
<div class="row mb-3">
  <div class="col-md-6 mb-2">
    <label for="customer_name" class="form-label"><strong>Customer Name</strong></label>
    <input type="text" class="form-control" id="customer_name" name="customer_name" required
           value="<?= htmlspecialchars($customer['name']) ?>">
  </div>
  <div class="col-md-6 mb-2">
    <label for="phone_no" class="form-label"><strong>Phone Number</strong></label>
    <input type="text" class="form-control" id="phone_no" name="phone_no" required
           value="<?= htmlspecialchars($customer['phone_no']) ?>">
  </div>
  <div class="col-md-6 mb-2">
    <label for="city" class="form-label"><strong>City</strong></label>
    <input type="text" class="form-control" id="city" name="city" required
           value="<?= htmlspecialchars($customer['city']) ?>">
  </div>
  <div class="col-md-6 mb-2">
    <label for="address" class="form-label"><strong>Address</strong></label>
    <input type="text" class="form-control" id="address" name="address" required
           value="<?= htmlspecialchars($customer['address']) ?>">
  </div>
</div>

<label for="returnAmount" class="form-label"><strong>Return Amount (₹):</strong></label>
<input type="text" class="form-control" id="returnAmount" name="return_amount"
    placeholder="Enter return amount"
    oninput="this.value = this.value.replace(/[^0-9.]/g, ''); applyFinalPrice(); updateGrandAmount();">

                                    <label for="finalAmountPaid" class="form-label"><strong>Final Amount Paid (₹):</strong></label>
<input type="text" class="form-control" id="finalAmountPaid" name="final_amount"
                                        placeholder="Enter final amount"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, ''); applyFinalPrice(); updateGrandAmount();">

                                    <label for="RentAmount" class="form-label"><strong>Freight Paid (₹):</strong></label>
                                    <input type="text" class="form-control" id="RentAmount" name="rent_amount"
                                     placeholder="Enter rent amount" value="0"
                                     oninput="this.value = this.value.replace(/[^0-9.]/g, ''); applyFinalPrice(); updateGrandAmount();">
 
                                     <label for="grandAmountPaid" class="form-label"><strong>Grand Amount Paid (₹):</strong></label>
                                     <input type="text" class="form-control" id="grandAmountPaid" name="grand_amount_paid" 
                                         oninput="this.value = this.value.replace(/[^0-9.]/g, ''); updateFinalAndRentFromGrand();">
                                    <input type="hidden" name="final_price" id="final_price">
                                    <button type="submit" class="btn btn-success">Submit Order</button>

                                    </form>
                                </div>
                    
                </div>
            </div>
        </main>
    </div>



<!-- Product Selection Modal -->
<!-- Product Selection Modal -->
<!-- Product Selection Modal -->



<!-- 🔹 Bill Container (Hidden Initially) -->
<!-- Full-Screen Bill Modal -->
<div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmSubmitModalLabel">Confirm Submission</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to submit this order?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="confirmSubmitBtn">Yes, Submit</button>
      </div>
    </div>
  </div>
</div>
<!-- ✅ Ensure the modal is placed before closing body tag -->
</body>
</html>


