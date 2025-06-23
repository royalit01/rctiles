<?php
include "../db_connect.php";

$storage_areas = [];
$result = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $storage_areas[] = $row;
    }
}

$categories = [];
$products = [];

// Default query to fetch all products
$product_query = "SELECT p.product_name, p.description, ps.quantity, ps.product_image
                  FROM products p
                  JOIN product_stock ps ON p.product_id = ps.product_id";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['storage_area_id'])) {
        $storage_area_id = $_POST['storage_area_id'];
        $category_query = "SELECT DISTINCT c.category_id, c.category_name FROM category c
                            JOIN products p ON c.category_id = p.category_id
                            JOIN product_stock ps ON p.product_id = ps.product_id
                            WHERE ps.storage_area_id = ?";
        $stmt = $mysqli->prepare($category_query);
        $stmt->bind_param("i", $storage_area_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($cat_row = $result->fetch_assoc()) {
            $categories[] = $cat_row;
        }
        $stmt->close();

        // Adjust the product query to filter by storage area
        $product_query .= " WHERE ps.storage_area_id = " . $storage_area_id;
    }

    if (!empty($_POST['category_id'])) {
        $category_id = $_POST['category_id'];
        // Further filter products by category if selected
        $product_query .= " AND p.category_id = " . $category_id;
    }
}

// Execute the product query
$result = $mysqli->query($product_query);
if ($result->num_rows > 0) {
    while ($prod_row = $result->fetch_assoc()) {
        $products[] = $prod_row;
    }
}
$mysqli->close();
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
        <style>
        .product-image {
            max-width: 100px;
            height: auto;
        }
    </style>   
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="index.html">Admin Dashboard</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#!">Settings</a></li>
                        <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="#!">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Main Dashboard</div>
                            <a class="nav-link" href="Product.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Product
                            </a>
                            <a class="nav-link" href="Transaction.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Transaction
                            </a>
                            <a class="nav-link" href="Add_Stock.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Add Stock
                            </a>
                            <a class="nav-link" href="Minus_Stock.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Minus Stock
                            </a>
                            <a class="nav-link" href="Low_Stock.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Low Stock
                            </a>
                            <a class="nav-link" href="Add_Product.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Add Product
                            </a>


                            <div class="sb-sidenav-menu-heading">Edit Options</div>
                            <a class="nav-link" href="Edit_Product.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Product
                            </a>
                            <a class="nav-link" href="Edit_Category.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Category
                            </a>
                            <a class="nav-link" href="Edit_Supplier.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Supplier 
                            </a>
                            <a class="nav-link" href="Edit_Storage_Area.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Storage Area 
                            </a>


                            <div class="sb-sidenav-menu-heading">Advance Edit Options</div>
                            <a class="nav-link" href="Bulk_Stock_Update.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Bulk Stock Update
                            </a>
                            <a class="nav-link" href="Stock_Transfer.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Stock Transfer
                            </a>
                            <a class="nav-link" href="Stock_Update_Excel.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Stock Update Excel
                            </a>
                           


                            <div class="sb-sidenav-menu-heading">Report</div>
                            <a class="nav-link" href="Report.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Total Stock Report
                            </a>
                            <a class="nav-link" href="Add_Report.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Add Stock Report
                            </a>
                            <a class="nav-link" href="Minus_Report.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Minus Stock Report
                            </a>
                            <a class="nav-link" href="Low_Stock_Report.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Low Stock Report
                            </a>
                           
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        Admin
                    </div>
                </nav>
            </div> 

            <!-- ---------------------------- -->
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid">
                        <h1 class="mb-4">Storage Dashboard </h1>
                        <form action="" method="post">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="storage-area" class="form-label">Storage Area:</label>
                                    <select id="storage-area" name="storage_area_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">Select Storage Area</option>
                                        <?php foreach ($storage_areas as $area): ?>
                                            <option value="<?= $area['storage_area_id'] ?>" <?= isset($_POST['storage_area_id']) && $_POST['storage_area_id'] == $area['storage_area_id'] ? 'selected' : '' ?>>
                                                <?= $area['storage_area_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="category" class="form-label">Category:</label>
                                    <select id="category" name="category_id" class="form-select" onchange="this.form.submit()" <?= empty($categories) ? 'disabled' : '' ?>>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['category_id'] ?>" <?= isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id'] ? 'selected' : '' ?>>
                                                <?= $category['category_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                        <?php if (!empty($products)): ?>
                            <div class="row">
                                <div class="col-12">
                                <div class="mb-3">
                                     <input type="text" id="searchInput" class="form-control" placeholder="Search products..." onkeyup="filterTable()"style="max-width: 410px; width: 100%;">
                                </div>
                                    <h4>Products Details</h4>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Stock</th>
                                                <th>Image</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($products as $product): ?>
                                                <tr>
                    <td><?= htmlspecialchars($product['product_name']) ?></td>
                    <td><?= htmlspecialchars($product['description']) ?></td>
                    <td><?= $product['quantity'] ?></td>
                    <td><img src="../uploads/<?= htmlspecialchars($product['product_image']) ?>" alt="Product Image" class="product-image" style="width:90px; height:80px; max-width:80px; max-height:80px; cursor:pointer;" data-bs-toggle="modal" 
                    data-bs-target="#imageModal" 
                    onclick="document.getElementById('modalImage').src = this.src;"></td>
                    </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>  
                </main> 
                <!-- Bootstrap Modal -->
                <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body">
                                <img id="modalImage" src="" alt="Expanded Image" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            ·
                            <a href="#">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
            </div>
         <!-- ---------------------------- -->   
        </div>
       
        <script>
            function filterTable() {
            
                // Get the search query
                let input = document.getElementById("searchInput");
                let filter = input.value.toUpperCase();
                let table = document.querySelector("table"); // Select the table element
                let tr = table.getElementsByTagName("tr");

                // Loop through all table rows (except the first row, which is the header)
                for (let i = 1; i < tr.length; i++) {
                    let tdName = tr[i].getElementsByTagName("td")[0]; // Column with product name
                    if (tdName) {
                        let nameValue = tdName.textContent || tdName.innerText;
                        // Check if the input matches the name (case insensitive)
                        if (nameValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = ""; // Show the row
                        } else {
                            tr[i].style.display = "none"; // Hide the row
                        }
                    }
                }
            }
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>
