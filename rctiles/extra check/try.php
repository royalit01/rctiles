<?php 
include '../db_connect.php'; // Ensure your DB connection is correct

$host = 'localhost';  // Database host
$dbname = 'rc_ceramic_mall_db_agrima';  // Database name
$username = 'root';  // Database username
$password = '';  // Database password

// Establish a connection to the database
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Query to fetch categories from the database
$stmt = $pdo->query("SELECT category_id, category_name FROM category");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch products from the database
try {
    
    $productStmt = $pdo->query("SELECT p.product_name, p.description, ps.product_id, SUM(ps.quantity) as total_quantity, 
                         ps.pieces_per_packet, ps.min_stock_level, p.product_image, 
                         c.category_name
                  FROM products p
                  JOIN product_stock ps ON p.product_id = ps.product_id
                  JOIN category c ON p.category_id = c.category_id"); // Adjust the query as needed
    $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $products = []; // Initialize to an empty array on error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Form Example</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Ensure jQuery is loaded first -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap JS (with Popper) -->

    <style>
        .detail-entry { 
            border: 1px solid #ccc; 
            padding: 20px; 
            margin-top: 10px; 
            position: relative; 
            border-radius: 5px; 
            background-color: #f9f9f9; 
        }
        .hidden { display: none; }
        .toggle-btn { position: absolute; top: 20px; right: 20px; }
        .volume-layout {
            background-color: #f0f0f0;  
        }
        .standard-layout {
            background-color: #ffffff;  
        }
        .modal-fullpage {
            max-width: 100%;
            width: 100%;
            height: 100vh;
            margin: 0;
        }
        .modal-dialog {
            max-width: 100%;
            width: 100%;
            height: 100%;
            margin: 0;
        }
        .product-table th, .product-table td {
            vertical-align: middle;
        }
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
        }
        .quantity-controls input {
            width: 50px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-3">
        <h1 class="mb-4">Create New Order</h1>
        <div id="orderDetailsContainer"></div>
        <button class="btn btn-success" onclick="addDetail()">Add New Detail</button>
    </div>

 
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Choose Products</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($products)): ?>
                    <div class="container mt-3">
                        <h1>Choose Products</h1>
                        <form action="" method="POST">
                        <table class="table align-middle" id="sortableTable">
                <thead>
                    <tr>
                         <th>Image</th>
                        <th>Product</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $packets = intdiv($product['total_quantity'], $product['pieces_per_packet']);
                        $pieces = $product['total_quantity'] % $product['pieces_per_packet'];
                        ?>
                        <tr> <!-- Adding onclick event here -->
                            <!-- Image Column -->
                            <td onclick="event.stopPropagation();"> <!-- Stop propagation for image click -->
                                <img src="../uploads/<?= htmlspecialchars($product['product_image']) ?>" 
                                     alt="" 
                                     class="rounded-circle border border-primary" 
                                     style="width: 50px; height: 50px; cursor: pointer;"
                                     onclick="showImageModal('<?= htmlspecialchars($product['product_image']) ?>');">
                            </td>
                            
                            <!-- Product Information Column -->
                            <td>
                                <div>
                                    <strong class="text-dark"><?= htmlspecialchars($product['product_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($product['category_name']) ?></small> &nbsp;<span class="badge text-bg-warning"><?= htmlspecialchars($product['description']) ?></span><br>
                                     <span class="badge bg-primary"><?= $packets ?> Box</span>
                                     <span class="badge bg-secondary mt-1"><?= $pieces ?> Pc</span>
                                </div>
                            </td>
                            
                            <!-- Stock Column -->
                            <td>
                                     <a href="Update_Product.php?product_id=<?= $product['product_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <!--<button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $product['product_id'] ?>)">Delete</button>-->
                                <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $product['product_id'] ?>)">Delete</button>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                

            </table>
                            <!-- <table class="table table-bordered product-table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Product Name</th>
                                        <th>Description</th>
                                        <th>Stock</th>
                                        <th>Area</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="Product Image" class="product-image"></td>
                                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                                            <td><?php echo htmlspecialchars($product['area']); ?> m²</td>
                                            <td>
                                                <div class="quantity-controls">
                                                    <button type="button" class="btn btn-danger btn-sm decrease" onclick="changeQuantity(this, 'decrease')">-</button>
                                                    <input type="number" name="quantity[<?php echo $product['product_id']; ?>]" value="0" min="0" class="form-control quantity-input" data-product-id="<?php echo $product['product_id']; ?>" readonly>
                                                    <button type="button" class="btn btn-primary" onclick="saveProducts()">Save Products</button>
                                                    </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table> -->
                            <button type="button" class="btn btn-primary" onclick="saveProducts()">Save Products</button>
                            </form>
                    </div>
                    <?php else: ?>
                    <p>No products found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let detailCounter = 0;
    const selectedProductsData = {}; // Object to store product data for each div by div ID
                         // Store the total area and category values
        let calculatedTotalArea = 0;
        let selectedCategory = "";
        
        function toggleLayout(detail) {
            const isStandardLayout = detail.classList.contains('standard-layout');
            detail.classList.toggle('standard-layout', !isStandardLayout);
            detail.classList.toggle('volume-layout', isStandardLayout);
            updateVisibility(detail);
            calculateAreas(detail);
        }

        function updateVisibility(detail) {
            const isVolumeLayout = detail.classList.contains('volume-layout');
            detail.querySelectorAll('.standard-input').forEach(input => input.classList.toggle('hidden', isVolumeLayout));
            detail.querySelectorAll('.custom-input').forEach(input => input.classList.toggle('hidden', !isVolumeLayout));
            detail.querySelector('.wall-height').classList.remove('hidden');  // Always visible
        }

        function calculateAreas(detail) { 
            const wallAreaSpan = detail.querySelector('.wall-area');
            const floorAreaSpan = detail.querySelector('.floor-area');
            const totalAreaSpan = detail.querySelector('.total-area');
            let wallArea = 0, floorArea = 0;

            if (detail.classList.contains('standard-layout')) {
                const length = parseFloat(detail.querySelector('.wall-length').value) || 0;
                const width = parseFloat(detail.querySelector('.wall-width').value) || 0;
                const height = parseFloat(detail.querySelector('.wall-height').value) || 0;
                wallArea = 2 * height * (length + width);  
                floorArea = (parseFloat(detail.querySelector('.floor-length').value) || 0) *
                            (parseFloat(detail.querySelector('.floor-width').value) || 0);  
            } else {
                const perimeter = parseFloat(detail.querySelector('.wall-perimeter').value) || 0;
                const height = parseFloat(detail.querySelector('.wall-height').value) || 0;
                wallArea = perimeter * height;  

                const floorAreaInput = parseFloat(detail.querySelector('.floor-area-direct').value) || 0;
                floorArea = floorAreaInput;
            }

            const totalArea = wallArea + floorArea;  
            wallAreaSpan.textContent = `Wall Area: ${wallArea.toFixed(2)} m²`;
            floorAreaSpan.textContent = `Floor Area: ${floorArea.toFixed(2)} m²`;
            totalAreaSpan.textContent = `Total Area: ${totalArea.toFixed(2)} m²`;
            calculatedTotalArea = totalArea;
        }

    function addDetail() {
        const container = document.getElementById('orderDetailsContainer');
        const detailId = `detail-${detailCounter}`; // Generate a unique ID for the detail-entry
        const detail = document.createElement('div');
        detail.className = 'detail-entry standard-layout';
        detail.id = detailId; // Assign the unique ID
        detail.innerHTML = `
           <div class="form-row">
                    <div class="form-group col-md-4 standard-input">
                        <label>Wall Length (m):</label>
                        <input type="number" class="form-control wall-length" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                    <div class="form-group col-md-4 standard-input">
                        <label>Wall Width (m):</label>
                        <input type="number" class="form-control wall-width" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Wall Height (m):</label>
                        <input type="number" class="form-control wall-height" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                    <div class="form-group col-md-4 custom-input hidden">
                        <label>Wall Perimeter (m):</label>
                        <input type="number" class="form-control wall-perimeter" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4 standard-input">
                        <label>Floor Length (m):</label>
                        <input type="number" class="form-control floor-length" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                    <div class="form-group col-md-4 standard-input">
                        <label>Floor Width (m):</label>
                        <input type="number" class="form-control floor-width" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                    <div class="form-group col-md-4 custom-input hidden">
                        <label>Floor Area (m²):</label>
                        <input type="number" class="form-control floor-area-direct" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                </div>
                <p>Wall Area: <span class="wall-area">0 m²</span></p>
                <p>Floor Area: <span class="floor-area">0 m²</span></p>
                <p>Total Area: <span class="total-area">0 m²</span></p>
                <button class="btn btn-info toggle-btn" onclick="toggleLayout(this.parentNode)">Toggle Layout</button>

                <div>
            <div class="form-group">
                <label>Category:</label>
                <select class="form-control categorySelect" onchange="filterProductsForDiv('${detailId}', this)">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Category:</label>
                <select class="form-control categorySelect" onchange="filterProductsForDiv('${detailId}', this)">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="selected-products-${detailCounter}" class="selected-products">
                <p>No products selected yet for this detail.</p>
            </div>
            <button class="btn btn-primary mt-2" onclick="openProductModal('${detailId}')">Choose Product</button>
            <button class="btn btn-danger mt-2" onclick="removeDetail('${detailId}', this)">Remove</button>
        `;
        container.appendChild(detail);
        selectedProductsData[detailId] = []; // Initialize product selection for this detail
        detailCounter++;
    }

    function openProductModal(detailId) {
        const categorySelect = document.querySelector(`#${detailId} .categorySelect`);
        const selectedCategory = categorySelect.value;

        if (!selectedCategory) {
            alert("Please select a category first.");
            return;
        }

        const filteredProducts = filterProductsByCategory(selectedCategory);

        if (filteredProducts.length === 0) {
            alert("No products available for the selected category.");
            return;
        }

        const productTableBody = document.querySelector('#productModal .product-table tbody');
        productTableBody.innerHTML = ''; // Clear previous entries

        filteredProducts.forEach(product => {
            const existingProduct = selectedProductsData[detailId].find(p => p.product_id === product.product_id);
            const quantity = existingProduct ? existingProduct.quantity : 0;

            productTableBody.innerHTML += `
                <tr>
                    <td><img src="${product.product_image}" class="product-image" /></td>
                    <td>${product.product_name}</td>
                    <td>${product.description}</td>
                    <td>${product.stock}</td>
                    <td>${product.area} m²</td>
                    <td>
                        <input type="number" class="form-control" data-product-id="${product.product_id}" value="${quantity}" min="0">
                    </td>
                </tr>
            `;
        });

        $('#productModal').data('detail-id', detailId).modal('show');
    }

    function saveProducts() {
        const detailId = $('#productModal').data('detail-id');
        const productTableBody = document.querySelector('#productModal .product-table tbody');
        const inputs = productTableBody.querySelectorAll('input[data-product-id]');
        const selectedProducts = [];

        inputs.forEach(input => {
            const productId = input.getAttribute('data-product-id');
            const quantity = parseInt(input.value, 10);

            if (quantity > 0) {
                selectedProducts.push({ product_id: productId, quantity });
            }
        });

        selectedProductsData[detailId] = selectedProducts; // Update data for the div

        updateSelectedProductsView(detailId);
        $('#productModal').modal('hide');
    }

    function updateSelectedProductsView(detailId) {
        const selectedProductsDiv = document.querySelector(`#${detailId} .selected-products`);
        const selectedProducts = selectedProductsData[detailId];

        if (selectedProducts.length === 0) {
            selectedProductsDiv.innerHTML = '<p>No products selected yet for this detail.</p>';
            return;
        }

        selectedProductsDiv.innerHTML = '<ul>';
        selectedProducts.forEach(product => {
            selectedProductsDiv.innerHTML += `
                <li>Product ID: ${product.product_id}, Quantity: ${product.quantity}</li>
            `;
        });
        selectedProductsDiv.innerHTML += '</ul>';
    }

    function filterProductsForDiv(detailId, select) {
        const selectedCategory = select.value;
        openProductModal(detailId);
    }

    function filterProductsByCategory(categoryId) {
        return <?php echo json_encode($products); ?>.filter(product => product.category_id == categoryId);
    }

    function removeDetail(detailId, button) {
        delete selectedProductsData[detailId];
        button.closest('.detail-entry').remove();
    }
</script>
</body>
</html>
