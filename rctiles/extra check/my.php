<?php  
// Include your database connection
include '../db_connect.php';

// Database configuration
$host = 'localhost';
$dbname = 'rc_ceramic_mall_db_agrima';
$username = 'root';
$password = '';

try {
    // Establish a connection to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch categories
    $stmt = $pdo->query("SELECT category_id, category_name FROM category");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all products
    $stmt = $pdo->prepare("SELECT * FROM products");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter products if category is selected
    $categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : null;
    if ($categoryId) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Error connecting to the database");
}
?>
    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Form Example</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
        .detail-entry {
            border: 1px solid #ccc;
            padding: 20px;
            margin-top: 10px;
            position: relative;
            border-radius: 5px;
            background-color: #f9f9f9;
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
        .hidden { display: none; }
        .toggle-btn { position: absolute; top: 20px; right: 20px; }
        .volume-layout {
            background-color: #f0f0f0;  
        }
        .standard-layout {
            background-color: #ffffff;  
        }

    </style>
</head>
<body>
<div class="container mt-3">
    <h1 class="mb-4">Create New Order</h1>
    <div id="orderDetailsContainer"></div>
    <button class="btn btn-success" onclick="addDetail()">Add New Detail</button>
</div>

<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Select Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered product-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Description</th>
                            <th>Stock</th>
                            <th>Area (m²)</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic product rows will be injected here -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveProducts()">Save Products</button>
            </div>
        </div>
    </div>
</div>

<script>
    let detailCounter = 0;
    const selectedProductsData = {}; // Stores selected products for each div

    function addDetail() {
        const container = document.getElementById('orderDetailsContainer');
        const detail = document.createElement('div');
        detail.className = 'detail-entry standard-layout';
        detail.setAttribute('data-id', detailCounter);
        detail.innerHTML = `
            <div class="form-row">
                <div class="form-group col-md-4 standard-input">
                    <label>Wall Length (m):</label>
                    <input type="number" class="form-control wall-length" oninput="calculateAreas(this.closest('.detail-entry'))">
                </div>
                <div class="form-group col-md-4 standard-input">
                    <label>Wall Width (m):</label>
                    <input type="number" class="form-control wall-width" oninput="calculateAreas(this.closest('.detail-entry'))">
                </div>
                <div class="form-group col-md-4">
                    <label>Wall Height (m):</label>
                    <input type="number" class="form-control wall-height" oninput="calculateAreas(this.closest('.detail-entry'))">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4 standard-input">
                    <label>Floor Length (m):</label>
                    <input type="number" class="form-control floor-length" oninput="calculateAreas(this.closest('.detail-entry'))">
                </div>
                <div class="form-group col-md-4 standard-input">
                    <label>Floor Width (m):</label>
                    <input type="number" class="form-control floor-width" oninput="calculateAreas(this.closest('.detail-entry'))">
                </div>
            </div>
            <div class="form-group">
                <label>Category:</label>
                <select class="form-control categorySelect" onchange="filterProductsForDiv(this)">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="selected-products-${detailCounter}" class="selected-products">
                <p>No products selected yet for this detail.</p>
            </div>
            <button class="btn btn-primary mt-2" onclick="openProductModal(${detailCounter})">Choose Product</button>
            <button class="btn btn-danger mt-2" onclick="removeDetail(${detailCounter}, this)">Remove</button>
        `;
        container.appendChild(detail);
        selectedProductsData[detailCounter] = { products: {}, divId: detailCounter }; // Initialize product selection for this detail
        detailCounter++;
    }

    function openProductModal(detailId) {
        const categorySelect = document.querySelector(`#selected-products-${detailId} .categorySelect`);
        const selectedCategory = categorySelect ? categorySelect.value : null;
        
        // Dynamically filter products based on the selected category
        const filteredProducts = filterProductsByCategory(selectedCategory);
        
        // Pass filtered products to the modal (you can use AJAX if needed or fetch dynamically)
        const productTableBody = document.querySelector('#productModal .product-table tbody');
        productTableBody.innerHTML = '';
        
        filteredProducts.forEach(product => {
            productTableBody.innerHTML += `
                <tr>
                    <td><img src="${product.product_image}" class="product-image" /></td>
                    <td>${product.product_name}</td>
                    <td>${product.description}</td>
                    <td>${product.stock}</td>
                    <td>${product.area} m²</td>
                    <td><input type="number" class="form-control" name="product_quantity[${product.product_id}]" value="0" min="0"></td>
                </tr>
            `;
        });
        
        $('#productModal').data('detail-id', detailId).modal('show');
    }

    function filterProductsForDiv(select) {
        const selectedCategory = select.value;
        const divId = select.closest('.detail-entry').getAttribute('data-id');
        
        // Open modal with filtered products based on selected category
        openProductModal(divId);
    }

    // This function filters products based on category and returns them
    function filterProductsByCategory(categoryId) {
        if (!categoryId) return []; // If no category selected, return an empty array

        // Filter products on the client-side based on the categoryId
        return <?php echo json_encode($products); ?>.filter(product => product.category_id == categoryId);
    }

    function removeDetail(detailId, button) {
        delete selectedProductsData[detailId];
        button.closest('.detail-entry').remove();
    }
</script>


</body>
</html>
