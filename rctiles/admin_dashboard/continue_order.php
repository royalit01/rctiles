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
        unitPrice: 0, // Set to 0 when editing
        totalPrice: 0, // This will be 0 since unitPrice is 0
        currentTotalPrice: 0, // This will be 0 since unitPrice is 0
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

        // Collect products from summary table (this includes both existing and newly added products)
        let hasProducts = false;
        let productsArray = [];
        
        document.querySelectorAll('#summaryBody tr').forEach((row, index) => {
            const productName = row.cells[0]?.textContent?.trim();
            const quantity = parseInt(row.querySelector('.product-quantity')?.value) || 0;
            const unitPrice = parseFloat(row.querySelector('.fixed-price-input')?.value) || 0;
            const finalPrice = parseFloat(row.querySelector('.final-price')?.getAttribute('data-current-price')) || 0;
            const productId = row.querySelector('.final-price')?.getAttribute('data-id') || row.getAttribute('data-product-id') || index;

            if (productName && quantity > 0) {
                hasProducts = true;
                productsArray.push({
                    id: parseInt(productId) || index,
                    name: productName,
                    quantity: quantity,
                    unitPrice: unitPrice,
                    totalPrice: finalPrice,
                    multiplier: 1
                });
            }
        });

        if (!hasProducts) {
            alert("No products selected. Please add at least one product.");
            return;
        }

        console.log("Products to submit:", productsArray);

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
            let fixedPrice = 0; // Set fixed price to 0 when editing
            let originalPrice = parseFloat(product.original_price) || 0;
            let finalPrice = quantity * fixedPrice; // This will be 0
            totalAmount += finalPrice;
            summaryBody.insertAdjacentHTML("beforeend", `
                <tr data-product-id="${product.product_id}">
                    <td>${product.product_name}</td>
                    <td><input type="number" class="form-control product-quantity" min="1" value="${quantity}" data-index="${index}" oninput="updateRowTotal(this)"></td>
                    <td>${originalPrice.toFixed(2)}</td>
                    <td><input type="number" class="form-control fixed-price-input" min="0" step="0.01" value="${fixedPrice.toFixed(2)}" data-index="${index}" oninput="updateRowTotal(this)"></td>
                    <td class="final-price" data-id="${product.product_id}" data-index="${index}" data-original-price="${finalPrice.toFixed(2)}" data-current-price="${finalPrice.toFixed(2)}">₹${finalPrice.toFixed(2)}</td>
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
                                <!-- Add Products Button -->
<div class="mb-3">
    <button type="button" class="btn btn-primary" id="addProductsBtn">Add Products</button>
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
<!-- Add Products Modal -->
<div class="modal fade" id="addProductsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Products</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <select id="addProdCategory" class="form-select">
                    <option value="">Select Category</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" id="productSearch" class="form-control" placeholder="Search product name">
            </div>
            <div class="col-md-4 text-end">
                <small class="text-muted" id="productCount"></small>
            </div>
        </div>
        <div class="table-responsive" style="max-height:420px;overflow:auto;">
            <table class="table table-sm table-bordered align-middle" id="addProductsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:35%">Product</th>
                        <th style="width:10%">Qty</th>
                        <th style="width:15%">Unit Price</th>
                        <th style="width:15%">Total</th>
                        <th style="width:10%">Add</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="confirmAddProductsBtn">Add Selected</button>
      </div>
    </div>
  </div>
</div>

<!-- BEGIN: Dynamic Detail Sections (added) -->
<div class="mt-4">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0">Add/Update Order Details</h5>
    <button type="button" class="btn btn-primary btn-sm" id="addDetailBtn">Add Detail</button>
  </div>
  <div id="orderDetailsContainer"></div>
</div>
<!-- END: Dynamic Detail Sections -->

<!-- Product Selection Modal (added) -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalLabel">Select Products</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-1"><strong>Selected Category:</strong> <span id="selectedCategoryName"></span></p>
        <p class="mb-3"><strong>Total Area:</strong> <span id="selectedTotalArea" class="area-value">0.00 m²</span></p>
        <div id="productListContainer" class="table-responsive">
          <table class="table table-bordered table-sm align-middle product-table mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:60px">Image</th>
                <th>Product Name</th>
                <th style="width:120px">Area / Unit</th>
                <th style="width:90px">Qty</th>
                <th style="width:70px">Select</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="confirmProductSelectBtn">Confirm Selection</button>
      </div>
    </div>
  </div>
</div>
<?php
// ...existing code...
?>
<script>
// === Detail Section Product Selection Logic (added) ===
let currentSelectionType = ''; // 'wall' or 'floor'
let selectedProductsWall = []; // temp for current modal
let selectedProductsFloor = []; // temp for current modal
// selectedProductsData already defined earlier; ensure it exists
if (typeof selectedProductsData === 'undefined') { selectedProductsData = {}; }

// Add Products Modal functionality
(function(){
    const addBtn = document.getElementById('addProductsBtn');
    if(!addBtn) return;
    let addProductsModalInstance;
    const productsCache = {}; // category_id -> products array
    const selectedToAdd = {}; // temp selections product_id -> {id,name,qty,unitPrice}

    function fetchCategoriesForAdd(){
        fetch('fetch_category.php')
          .then(r=>r.json())
          .then(data=>{
             const sel = document.getElementById('addProdCategory');
             sel.innerHTML = '<option value="">All Categories</option>';
             data.forEach(c=> sel.insertAdjacentHTML('beforeend', `<option value="${c.category_id}">${c.category_name}</option>`));
          }).catch(console.error);
    }

    function fetchProductsForAdd(categoryId){
        const key = categoryId || 'all';
        if(productsCache[key]){ renderProducts(productsCache[key]); return; }
        const url = categoryId ? `fetch_products.php?category_id=${categoryId}&total_area=0` : 'fetch_products.php?total_area=0';
        fetch(url)
          .then(r=>r.json())
          .then(data=>{
              console.log('Fetched data:', data); // Debug log
              const arr = (data.products||[]).map(p=>({
                id: p.id,
                name: p.name,
                unitPrice: p.price && p.price !== 'N/A' ? parseFloat(p.price):0
              }));
              productsCache[key]=arr;
              renderProducts(arr);
          }).catch(error => {
              console.error('Error fetching products:', error);
              document.querySelector('#addProductsTable tbody').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading products</td></tr>';
          });
    }

    function renderProducts(list){
        const tbody = document.querySelector('#addProductsTable tbody');
        const search = document.getElementById('productSearch').value.toLowerCase();
        tbody.innerHTML='';
        let filtered = list.filter(p=> !search || p.name.toLowerCase().includes(search));
        document.getElementById('productCount').textContent = filtered.length + ' items';
        if(filtered.length===0){
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No products</td></tr>'; return;
        }
        filtered.forEach(p=>{
            const existing = selectedToAdd[p.id];
            const qty = existing? existing.qty:1;
            const unit = existing? existing.unitPrice : p.unitPrice;
            const total = qty*unit;
            tbody.insertAdjacentHTML('beforeend', `
              <tr data-id="${p.id}">
                <td>${p.name}</td>
                <td><input type="number" class="form-control form-control-sm add-qty" min="1" value="${qty}"></td>
                <td><input type="number" class="form-control form-control-sm add-unit" min="0" step="0.01" value="${unit.toFixed(2)}"></td>
                <td class="add-row-total">₹${total.toFixed(2)}</td>
                <td class="text-center">
                    <input type="checkbox" class="form-check-input add-select" ${existing?'checked':''}>
                </td>
              </tr>`);
        });
    }

    function recalcRow(row){
        const qty = parseInt(row.querySelector('.add-qty').value)||1;
        const unit = parseFloat(row.querySelector('.add-unit').value)||0;
        row.querySelector('.add-row-total').textContent = '₹'+(qty*unit).toFixed(2);
        if(row.querySelector('.add-select').checked){
            const id = row.getAttribute('data-id');
            selectedToAdd[id] = {id: parseInt(id), name: row.cells[0].textContent.trim(), qty: qty, unitPrice: unit};
        }
    }

    document.addEventListener('input', function(e){
        if(e.target.classList.contains('add-qty') || e.target.classList.contains('add-unit')){
            const row = e.target.closest('tr');
            recalcRow(row);
        }
        if(e.target.id==='productSearch'){
            const cat = document.getElementById('addProdCategory').value;
            fetchProductsForAdd(cat);
        }
    });
    document.addEventListener('change', function(e){
        if(e.target.id==='addProdCategory'){
            fetchProductsForAdd(e.target.value);
        }
        if(e.target.classList.contains('add-select')){
            const row = e.target.closest('tr');
            if(e.target.checked){
                recalcRow(row);
            } else {
                const id=row.getAttribute('data-id'); 
                delete selectedToAdd[id];
            }
        }
    });

    function ensureIndexAttributes(){
        // Reassign data-index sequentially for quantity & price inputs
        document.querySelectorAll('#summaryBody tr').forEach((tr, idx)=>{
            const qtyInput = tr.querySelector('.product-quantity');
            const priceInput = tr.querySelector('.fixed-price-input');
            const finalCell = tr.querySelector('.final-price');
            if(qtyInput) qtyInput.setAttribute('data-index', idx);
            if(priceInput) priceInput.setAttribute('data-index', idx);
            if(finalCell) finalCell.setAttribute('data-index', idx);
        });
    }

    function addSelectedProducts(){
        const body = document.getElementById('summaryBody');
        Object.values(selectedToAdd).forEach(p=>{
            // See if product already exists (match by product ID)
            let existingRow = body.querySelector(`tr[data-product-id="${p.id}"]`);
            const unitTotal = (p.qty * p.unitPrice);
            if(existingRow){
                // Update quantity & prices
                const qtyInput = existingRow.querySelector('.product-quantity');
                const priceInput = existingRow.querySelector('.fixed-price-input');
                const finalCell = existingRow.querySelector('.final-price');
                const newQty = (parseInt(qtyInput.value)||0) + p.qty;
                qtyInput.value = newQty;
                priceInput.value = p.unitPrice.toFixed(2); // adopt new unit price
                const newFinal = newQty * p.unitPrice;
                finalCell.textContent = '₹'+newFinal.toFixed(2);
                finalCell.setAttribute('data-original-price', newFinal.toFixed(2));
                finalCell.setAttribute('data-current-price', newFinal.toFixed(2));
                finalCell.setAttribute('data-id', p.id); // Ensure data-id is set
            } else {
                const index = body.querySelectorAll('tr').length;
                body.insertAdjacentHTML('beforeend', `
                    <tr data-product-id="${p.id}">
                        <td>${p.name}</td>
                        <td><input type="number" class="form-control product-quantity" min="1" value="${p.qty}" data-index="${index}" oninput="updateRowTotal(this)"></td>
                        <td>${(p.unitPrice * p.qty).toFixed(2)}</td>
                        <td><input type="number" class="form-control fixed-price-input" min="0" step="0.01" value="${p.unitPrice.toFixed(2)}" data-index="${index}" oninput="updateRowTotal(this)"></td>
                        <td class="final-price" data-id="${p.id}" data-index="${index}" data-original-price="${unitTotal.toFixed(2)}" data-current-price="${unitTotal.toFixed(2)}">₹${unitTotal.toFixed(2)}</td>
                    </tr>`);
            }
        });
        ensureIndexAttributes();
        updateTotalAmount();
        applyFinalPrice();
        // Clear selection
        Object.keys(selectedToAdd).forEach(key => delete selectedToAdd[key]);
    }

    addBtn.addEventListener('click', function(){
        if(!addProductsModalInstance){
            addProductsModalInstance = new bootstrap.Modal(document.getElementById('addProductsModal'));
        }
        fetchCategoriesForAdd();
        fetchProductsForAdd('');
        addProductsModalInstance.show();
    });

    document.getElementById('confirmAddProductsBtn').addEventListener('click', function(){
        addSelectedProducts();
        if(addProductsModalInstance) addProductsModalInstance.hide();
    });
})();

// Add initial index tracker for new sections
function addDetail() {
  const container = document.getElementById('orderDetailsContainer');
  const detailIndex = container.querySelectorAll('.dimension-group').length;
  const div = document.createElement('div');
  div.className = 'dimension-group';
  div.setAttribute('data-section-id', detailIndex);
  div.innerHTML = `
    <h6 class="fw-bold">Detail ${detailIndex + 1}</h6>
    <div class="mb-2">
      <label class="form-label">Title (e.g., Washroom 1, Kitchen, etc.)</label>
      <input type="text" class="form-control" name="titles[]" placeholder="Enter location name">
    </div>
    <div class="row g-2">
      <div class="col-md-4"><label class="form-label">Wall Length (ft²)</label><input type="number" class="form-control" name="wall_lengths[]" step="0.1" min="0" oninput="calculateAreas(this)"></div>
      <div class="col-md-4"><label class="form-label">Wall Width (ft²)</label><input type="number" class="form-control" name="wall_widths[]" step="0.1" min="0" oninput="calculateAreas(this)"></div>
      <div class="col-md-4"><label class="form-label">Wall Height (ft²)</label><input type="number" class="form-control" name="wall_heights[]" step="0.1" min="0" oninput="calculateAreas(this)"></div>
      <div class="col-md-4"><label class="form-label">Door Area (ft²)</label><input type="number" class="form-control" name="door_areas[]" step="0.1" min="0" oninput="calculateAreas(this)"></div>
      <div class="col-md-4"><label class="form-label">Window Area (ft²)</label><input type="number" class="form-control" name="window_areas[]" step="0.1" min="0" oninput="calculateAreas(this)"></div>
    </div>
    <p class="mt-2 mb-1"><strong>Wall Area:</strong> <span class="wall-area">0.00 m²</span></p>
    <div class="mb-2">
      <label class="form-label">Select Wall Category</label>
      <select class="form-select wall-category-select" name="wall_category_ids[]"><option value="">Select Category</option></select>
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openProductModal(this,'wall')">Choose Wall Tile</button>
    <div class="selected-products-wall mt-2 text-muted"><p class="mb-0">No wall tiles selected yet.</p></div>
    <hr class="my-3" />
    <div class="row g-2">
      <div class="col-md-4"><label class="form-label">Floor Length (ft²)</label><input type="number" class="form-control" name="floor_lengths[]" step="0.1" min="0" oninput="calculateAreas(this)"></div>
      <div class="col-md-4"><label class="form-label">Floor Width (ft²)</label><input type="number" class="form-control" name="floor_widths[]" step="0.1" min="0" oninput="calculateAreas(this)"></div>
    </div>
    <div class="mb-2 mt-2">
      <label class="form-label">Select Floor Category</label>
      <select class="form-select floor-category-select" name="floor_category_ids[]"><option value="">Select Category</option></select>
    </div>
    <p class="mt-2 mb-1"><strong>Floor Area:</strong> <span class="floor-area">0.00 m²</span></p>
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openProductModal(this,'floor')">Choose Floor Tile</button>
    <div class="selected-products-floor mt-2 text-muted"><p class="mb-0">No floor tiles selected yet.</p></div>
    <div class="mt-3">
      <label class="form-label">Number of Copies</label>
      <input type="number" class="form-control multiply-order" name="multipliers[]" min="1" value="1" oninput="updateMultiplication(this)">
    </div>
    <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removeDetail(this)">Remove Detail</button>
  `;
  div.selectedProductsWall = [];
  div.selectedProductsFloor = [];
  document.getElementById('orderDetailsContainer').appendChild(div);
  // ensure data object slot
  if(!selectedProductsData[detailIndex]) selectedProductsData[detailIndex] = { wall: [], floor: [] };
  // fetch categories for selects
  fetchCategories(div.querySelector('.wall-category-select'));
  fetchCategories(div.querySelector('.floor-category-select'));
}

function fetchCategories(selectEl){
  fetch('fetch_category.php')
    .then(r=>r.json())
    .then(data=>{ selectEl.innerHTML = '<option value="">Select Category</option>'; data.forEach(c=> selectEl.insertAdjacentHTML('beforeend', `<option value="${c.category_id}">${c.category_name}</option>`)); })
    .catch(console.error);
}

function calculateAreas(input){
  const group = input.closest('.dimension-group');
  const wallL = parseFloat(group.querySelector('input[name="wall_lengths[]"]').value)||0;
  const wallW = parseFloat(group.querySelector('input[name="wall_widths[]"]').value)||0;
  const wallH = parseFloat(group.querySelector('input[name="wall_heights[]"]').value)||0;
  const winA = parseFloat(group.querySelector('input[name="window_areas[]"]').value)||0;
  const doorA = parseFloat(group.querySelector('input[name="door_areas[]"]').value)||0;
  const floorL = parseFloat(group.querySelector('input[name="floor_lengths[]"]').value)||0;
  const floorW = parseFloat(group.querySelector('input[name="floor_widths[]"]').value)||0;
  const wallArea = Math.max(0,(2*wallH*(wallL+wallW)) - winA - doorA);
  const floorArea = Math.max(0,(floorL*floorW));
  group.querySelector('.wall-area').textContent = wallArea.toFixed(2)+' m²';
  group.querySelector('.floor-area').textContent = floorArea.toFixed(2)+' m²';
}

function openProductModal(btn,type){
  const group = btn.closest('.dimension-group');
  document.querySelectorAll('.dimension-group').forEach(g=>g.classList.remove('active'));
  group.classList.add('active');
  currentSelectionType = type;
  const catSelect = type==='wall'? group.querySelector('.wall-category-select'): group.querySelector('.floor-category-select');
  const categoryId = catSelect.value;
  const areaSpan = type==='wall'? group.querySelector('.wall-area'): group.querySelector('.floor-area');
  const totalArea = parseFloat(areaSpan.textContent)||0;
  document.getElementById('selectedCategoryName').textContent = catSelect.options[catSelect.selectedIndex]?.text||'';
  document.getElementById('selectedTotalArea').textContent = (totalArea||0).toFixed(2)+' m²';
  if(!selectedProductsData[group.getAttribute('data-section-id')]) selectedProductsData[group.getAttribute('data-section-id')]={wall:[],floor:[]};
  const pre = selectedProductsData[group.getAttribute('data-section-id')][type] || [];
  fetchProducts(categoryId, pre);
  new bootstrap.Modal(document.getElementById('productModal')).show();
}

function fetchProducts(categoryId, preSelected){
  const url = `fetch_products.php?category_id=${categoryId||''}&total_area=0`;
  fetch(url).then(r=>r.json()).then(data=>{
    const tbody = document.querySelector('#productListContainer tbody');
    tbody.innerHTML='';
    const products = data.products||[];
    if(products.length===0){ tbody.innerHTML='<tr><td colspan="5" class="text-center text-muted">No products</td></tr>'; return; }
    products.forEach(p=>{
      const isSel = preSelected.some(x=>x.id==p.id);
      const preObj = preSelected.find(x=>x.id==p.id) || {};
      const qty = preObj.quantity || 1;
      tbody.insertAdjacentHTML('beforeend',`
        <tr>
          <td><img src="${p.image && p.image!=='null'? p.image: '../assets/img/default_img.jpg'}" alt="" width="50" onerror="this.src='../assets/img/default_img.jpg';"></td>
          <td>${p.name}</td>
          <td>${p.area_per_unit !== 'N/A'? p.area_per_unit: 'N/A'} m²</td>
          <td><input type="number" class="form-control form-control-sm product-quantity" min="1" value="${qty}" data-product-id="${p.id}" data-unit-price="${p.price && p.price!=='N/A'? p.price:0}"></td>
          <td class="text-center"><input type="checkbox" class="form-check-input product-checkbox" data-product-id="${p.id}" ${isSel? 'checked':''} onchange="toggleProductSelection(this, ${p.id}, '${p.name.replace(/'/g,"\\'")}', ${p.price && p.price!=='N/A'? p.price:0}, ${p.area_per_unit && p.area_per_unit!=='N/A'? p.area_per_unit:0})"></td>
        </tr>`);
    });
  }).catch(console.error);
}

function toggleProductSelection(cb,id,name,unitPrice,areaPerUnit){
  const row = cb.closest('tr');
  const qtyInput = row.querySelector('.product-quantity');
  const qty = parseInt(qtyInput.value)||1;
  const totalPrice = qty * (unitPrice||0);
  const totalArea = qty * (areaPerUnit||0);
  const group = document.querySelector('.dimension-group.active');
  if(!group) return;
  const sectionId = group.getAttribute('data-section-id');
  if(!selectedProductsData[sectionId]) selectedProductsData[sectionId] = {wall:[], floor:[]};
  const list = selectedProductsData[sectionId][currentSelectionType];
  const idx = list.findIndex(p=>p.id==id);
  if(cb.checked){
    if(idx===-1){ list.push({id:id,name:name,quantity:qty,unitPrice:unitPrice,totalPrice:qty*unitPrice,area:areaPerUnit,totalArea:totalArea}); }
    else { list[idx].quantity=qty; list[idx].totalPrice=qty*unitPrice; list[idx].totalArea=totalArea; }
  } else if(idx>-1){ list.splice(idx,1); }
}

function saveSelectedProducts(){
  const group = document.querySelector('.dimension-group.active');
  if(!group) return;
  const sectionId = group.getAttribute('data-section-id');
  ['wall','floor'].forEach(type=>{
    const container = group.querySelector('.selected-products-'+type);
    updateSelectedProductsUI(container, selectedProductsData[sectionId][type]);
  });
  // Append newly selected products to summary table (merge)
  mergeSelectedIntoSummary();
  const modalEl = document.getElementById('productModal');
  bootstrap.Modal.getInstance(modalEl).hide();
}

document.addEventListener('DOMContentLoaded', function() {
    if(document.getElementById('confirmProductSelectBtn')) {
        document.getElementById('confirmProductSelectBtn').addEventListener('click', saveSelectedProducts);
    }
    if(document.getElementById('addDetailBtn')) {
        document.getElementById('addDetailBtn').addEventListener('click', addDetail);
    }
});

function updateSelectedProductsUI(container,list){
  container.innerHTML='';
  if(!list || list.length===0){ container.innerHTML='<p class="mb-0 text-muted">No tiles selected yet.</p>'; return; }
  let totalArea=0;
  list.forEach(p=>{ totalArea += (p.totalArea||0); container.insertAdjacentHTML('beforeend',`<div class="d-flex justify-content-between small border rounded px-2 py-1 mb-1"><span>${p.name}</span><span class="text-muted">${p.quantity} pcs</span></div>`); });
  container.insertAdjacentHTML('beforeend',`<div class="text-end fw-bold small">Area: ${totalArea.toFixed(2)} m²</div>`);
}

function updateMultiplication(input){
  const group = input.closest('.dimension-group');
  const multiplier = Math.max(1, parseInt(input.value)||1);
  const sectionId = group.getAttribute('data-section-id');
  ['wall','floor'].forEach(type=>{
    (selectedProductsData[sectionId][type]||[]).forEach(p=>{ if(!p.baseQuantity) p.baseQuantity=p.quantity; p.quantity = p.baseQuantity * multiplier; p.totalPrice = p.quantity * p.unitPrice; });
    updateSelectedProductsUI(group.querySelector('.selected-products-'+type), selectedProductsData[sectionId][type]);
  });
  mergeSelectedIntoSummary();
}

function mergeSelectedIntoSummary(){
  const summaryBody = document.getElementById('summaryBody');
  // Map existing rows by product name (fallback) & data-id
  const existing = {};
  summaryBody.querySelectorAll('tr').forEach(tr=>{
    const name = tr.cells[0]?.textContent.trim();
    existing[name] = tr;
  });
  Object.values(selectedProductsData).forEach(section=>{
    ['wall','floor'].forEach(type=>{
      (section[type]||[]).forEach(p=>{
        const nameKey = p.name;
        const lineTotal = p.quantity * p.unitPrice;
        if(existing[nameKey]){
          // update quantity + prices (assume 3rd td is fixed price total & 4th is input of unit price?)
          const qtyInput = existing[nameKey].querySelector('.product-quantity');
          const priceInput = existing[nameKey].querySelector('.fixed-price-input');
          const finalCell = existing[nameKey].querySelector('.final-price');
          if(qtyInput) qtyInput.value = p.quantity;
          if(priceInput) priceInput.value = p.unitPrice.toFixed(2);
          if(finalCell){
            finalCell.textContent = '₹'+lineTotal.toFixed(2);
            finalCell.setAttribute('data-original-price', lineTotal.toFixed(2));
            finalCell.setAttribute('data-current-price', lineTotal.toFixed(2));
          }
        } else {
          const idx = summaryBody.querySelectorAll('tr').length;
          summaryBody.insertAdjacentHTML('beforeend',`
            <tr>
              <td>${p.name}</td>
              <td><input type="number" class="form-control product-quantity" min="1" value="${p.quantity}" data-index="${idx}" oninput="updateRowTotal(this)"></td>
              <td>${(p.unitPrice * p.quantity).toFixed(2)}</td>
              <td><input type="number" class="form-control fixed-price-input" min="0" step="0.01" value="${p.unitPrice.toFixed(2)}" data-index="${idx}" oninput="updateRowTotal(this)"></td>
              <td class="final-price" data-id="${p.id}" data-index="${idx}" data-original-price="${lineTotal.toFixed(2)}" data-current-price="${lineTotal.toFixed(2)}">₹${lineTotal.toFixed(2)}</td>
            </tr>`);
        }
      });
    });
  });
  // Re-index
  summaryBody.querySelectorAll('tr').forEach((tr,i)=>{
    tr.querySelectorAll('[data-index]').forEach(el=> el.setAttribute('data-index', i));
  });
  updateTotalAmount();
  applyFinalPrice();
}

function removeDetail(btn){
  const group = btn.closest('.dimension-group');
  if(!group) return;
  const id = group.getAttribute('data-section-id');
  delete selectedProductsData[id];
  group.remove();
  document.querySelectorAll('#orderDetailsContainer .dimension-group').forEach((g,idx)=>{
    g.setAttribute('data-section-id', idx);
    g.querySelector('h6').textContent = 'Detail '+(idx+1);
    if(!selectedProductsData[idx]) selectedProductsData[idx] = selectedProductsData[id]||{wall:[],floor:[]};
  });
  mergeSelectedIntoSummary();
}

// === End Added Logic ===
</script>
<?php
// ...existing code...
?>


