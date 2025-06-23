<?php
ini_set('display_errors', 0);
error_reporting(0);

$servername = "localhost";  // Replace with your server name
$username = "root";         // Replace with your database username
$password = "";             // Replace with your database password
$dbname = "rc_ceramic_mall_db_agrima";       // Your database name

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['action']) && $_GET['action'] == 'fetch_products' && isset($_GET['category_id'])) {
    header('Content-Type: application/json'); // Set the content type to JSON

    $category_id = intval($_GET['category_id']);
    $stmt = $mysqli->prepare("SELECT product_id, product_name FROM products WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode($products);
    exit;
}


// Fetch categories
$categories = [];
if ($stmt = $mysqli->prepare("SELECT category_id, category_name FROM category")) {
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard - SB Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        
    </head>
    
    <body class="sb-nav-fixed">
    <?php  include 'navbar.php'; ?>


            <!-- ---------------------------- -->
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                <h2 class="mt-4 text-center fw-bold  pb-3">RC Mall</h2>
                <div class="row mb-3">
                <div class="col-md-4 mb-3 fw-bold">
                    <label for="customerName">Customer Name:</label>
                    <input type="text" class="form-control" placeholder="Enter Customer Name" id="customerName">
                </div>
                <div class="col-md-4 mb-3 fw-bold">
                    <label for="customerAddress">Customer Address:</label>
                    <input type="text" class="form-control" placeholder="Enter Customer Address" id="customerAddress">
                </div>
                <div class="col-md-4 mb-3 fw-bold">
                    <label for="currentDate">Date:</label>
                    <input type="text" class="form-control" id="currentDate" value="<?php echo date('Y-m-d'); ?>" disabled>
                </div>
            </div>
                    <div class="table-responsive">
                        <table class="table ">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Price Per Item</th>
                                    <th>Total Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemTable"></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" style="text-align:right">Total:</th>
                                    <th id="totalPrice">Rs 0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
               
                <button type="button" class="btn btn-primary" onclick="addItem()">Add Item</button>
                <button type="button" class="btn btn-success" onclick="window.print()">Print</button>
                    </div>
                </main>
            </div>
         <!-- ---------------------------- -->   

       
         <script>
        let categories = <?php echo json_encode($categories); ?>;
        let itemId = 0;

        function addItem() {
            const table = document.getElementById("itemTable");
            const row = table.insertRow();
            row.innerHTML = `
                <td data-label="#">${++itemId}</td>
                <td data-label="Category">${getCategoryDropdown()}</td>
                <td data-label="Item Name"><select class="form-control product-name" disabled><option>Select product</option></select></td>
                <td data-label="Quantity"><input type="number" class="form-control quantity" value="1" min="1" onchange="updateTotal()"></td>
                <td data-label="Price Per Item"><input type="number" class="form-control price" value="0" min="0" onchange="updateTotal()"></td>
                <td data-label="Total Price">Rs 0</td>
                <td data-label="Actions"><button class="btn btn-danger" onclick="deleteItem(this.parentNode.parentNode)">Delete</button></td>
            `;
        }

        // function addItem() {
        //     const table = document.getElementById("itemTable");
        //     const row = table.insertRow();
        //     row.innerHTML = `
        //         <td>${++itemId}</td>
        //         <td>${getCategoryDropdown()}</td>
        //         <td><select class="form-control product-name" disabled><option>Select product</option></select></td>
        //         <td><input type="number" class="form-control quantity" value="1" min="1" onchange="updateTotal()"></td>
        //         <td><input type="number" class="form-control price" value="0" min="0" onchange="updateTotal()"></td>
        //         <td>Rs 0</td>
        //         <td><button class="btn btn-danger" onclick="deleteItem(this.parentNode.parentNode)">Delete</button></td>
        //     `;
        // }

        function getCategoryDropdown() {
            return `<select class="form-control category-select" onchange="loadProducts(this.value, this.parentNode.parentNode.cells[2].children[0])">
                <option value="">Select Category</option>
                ${categories.map(cat => `<option value="${cat.category_id}">${cat.category_name}</option>`).join('')}
            </select>`;
        }

        function loadProducts(categoryId, productSelect) {
                if (!categoryId) {
                    productSelect.innerHTML = '<option>Select product</option>';
                    productSelect.disabled = true;
                    return;
                }
                fetch(`?action=fetch_products&category_id=${categoryId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    } else {
                        return response.json();
                    }
                })
                .then(data => {
                    productSelect.innerHTML = data.map(product => `<option value='${product.product_id}'>${product.product_name}</option>`).join('');
                    productSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Fetch error:', error.message);
                    productSelect.innerHTML = '<option>Error loading products</option>';
                    productSelect.disabled = true;
                });
        }


        function updateTotal() {
            let total = 0;
            document.querySelectorAll("#itemTable tr").forEach(row => {
                const quantity = parseInt(row.querySelector(".quantity").value, 10) || 0;
                const price = parseFloat(row.querySelector(".price").value) || 0;
                const subtotal = quantity * price;
                row.cells[5].textContent = 'Rs ' + subtotal.toFixed(2);
                total += subtotal;
            });
            document.getElementById("totalPrice").textContent = 'Rs ' + total.toFixed(2);
        }

        function deleteItem(row) {
            row.parentNode.removeChild(row);
            updateTotal();
        }
    </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>
