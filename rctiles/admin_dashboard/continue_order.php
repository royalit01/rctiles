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
$stmt = $mysqli->prepare("SELECT product_name, quantity, original_price, custom_price FROM pending_orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id_int);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
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

document.addEventListener('DOMContentLoaded', function() {
    updateSummary();
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
    if (typeof applyFinalPrice === 'function') applyFinalPrice();
}

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

                    
                </div>
            </div>
        </main>
    </div>



<!-- Product Selection Modal -->
<!-- Product Selection Modal -->
<!-- Product Selection Modal -->



<!-- 🔹 Bill Container (Hidden Initially) -->
<!-- Full-Screen Bill Modal -->

<!-- ✅ Ensure the modal is placed before closing body tag -->
</body>
</html>


