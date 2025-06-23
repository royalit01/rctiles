<?php
include "../db_connect.php";  // Ensure this file exists and provides a valid mysqli connection object `$mysqli`

// Fetch storage areas
$storage_areas = [];
$result = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas");
while ($row = $result->fetch_assoc()) {
    $storage_areas[] = $row;  // Confirmed fetching storage areas correctly
}

// Variables for categories and products
$categories = [];
$products = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storage_area_id = $_POST['storage_area_id'] ?? '';
    
    if ($storage_area_id) {
        $category_query = $mysqli->prepare("SELECT DISTINCT c.category_id, c.category_name FROM category c
                           JOIN products p ON c.category_id = p.category_id
                           JOIN product_stock ps ON p.product_id = ps.product_id
                           WHERE ps.storage_area_id = ?");
        $category_query->bind_param("i", $storage_area_id);
        $category_query->execute();
        $result = $category_query->get_result();
        while ($cat_row = $result->fetch_assoc()) {
            $categories[] = $cat_row;  // Properly handling categories based on selected storage area
        }
        $category_query->close();

        $product_query = $mysqli->prepare("SELECT p.product_id, p.product_name, ps.quantity, ps.storage_area_id, ps.pieces_per_packet 
                          FROM products p 
                          JOIN product_stock ps ON p.product_id = ps.product_id 
                          WHERE ps.storage_area_id = ?");
        $product_query->bind_param("i", $storage_area_id);
        $product_query->execute();
        $products_result = $product_query->get_result();
        $products = $products_result->fetch_all(MYSQLI_ASSOC);  // Ensures all products for the selected area are fetched
        $product_query->close();
    }

    if (isset($_POST['product_id'], $_POST['packets'], $_POST['loose_pieces'], $_POST['pieces_per_packet'])) {
        $product_id = $_POST['product_id'];
        $packets = $_POST['packets'];
        $loose_pieces = $_POST['loose_pieces'];
        $pieces_per_packet = $_POST['pieces_per_packet'];

        $total_deduction = ($packets * $pieces_per_packet) + $loose_pieces;  // Calculation looks correct

        $current_quantity_query = $mysqli->prepare("SELECT quantity FROM product_stock WHERE product_id = ? AND storage_area_id = ?");
        $current_quantity_query->bind_param("ii", $product_id, $storage_area_id);
        $current_quantity_query->execute();
        $current_quantity_query->bind_result($current_quantity);
        $current_quantity_query->fetch();
        $current_quantity_query->close();

        if ($total_deduction <= $current_quantity) {
            $update_stock_query = $mysqli->prepare("UPDATE product_stock SET quantity = quantity - ? WHERE product_id = ? AND storage_area_id = ?");
            $update_stock_query->bind_param("iii", $total_deduction, $product_id, $storage_area_id);
            $success = $update_stock_query->execute();  // This should check if execution is successful
            $update_stock_query->close();

            if ($success) {
                echo "<script>alert('Stock updated successfully!');</script>";
            } else {
                echo "<script>alert('Failed to update stock.');</script>";
            }
        } else {
            echo "<script>alert('Error: Insufficient stock. Available stock is $current_quantity.');</script>";
        }
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
        .form-control.small-input { width: 70px; } /* Smaller input box */
        .success-modal { display: none; }
        @media (max-width: 768px) {
            .form-control.small-input { width: 100%; } /* Full-width on smaller screens */
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
                            <a class="nav-link" href="#">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Product
                            </a>
                            <a class="nav-link" href="storage_dashboard.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Transaction
                            </a>
                            <a class="nav-link" href="#">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Add Stock
                            </a>
                            <a class="nav-link" href="#">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Minus Stock
                            </a>


                            <div class="sb-sidenav-menu-heading">Edit Options</div>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Product
                            </a>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Category
                            </a>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Supplier 
                            </a>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Storage Area 
                            </a>

                            
                            <div class="sb-sidenav-menu-heading">Advance Edit Options</div>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Bulk Stock Update
                            </a>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Stock Transfer
                            </a>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Stock Update Excel
                            </a>

                            
                            <div class="sb-sidenav-menu-heading">Report</div>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Total Stock Report
                            </a>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Add Stock Report
                            </a>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Minus Stock Report
                            </a>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
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
                <h1 class="mb-4">Minus Stock</h1>
                <form action="" method="post">
                    <div class="row mb-3">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
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
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search products..." onkeyup="filterTable()">
                        </div>
                    </div>
                </form>
                
                <?php if (!empty($products)): ?>
                    <div class="table-responsive">
                        <table class="table" id="productTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                        // Calculate packets and loose pieces
                                        $packets = intdiv($product['quantity'], $product['pieces_per_packet']);
                                        $loose_pieces = $product['quantity'] % $product['pieces_per_packet'];
                                    ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($product['product_name']) ?>
                                            <div class="text-muted small">Stock: <?= $packets ?>/<?= $loose_pieces ?></div>
                                        </td>
                                        <td>
                                            <form method="post" style="display: flex; align-items: center; gap: 5px;">
                                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                                <input type="hidden" name="pieces_per_packet" value="<?= $product['pieces_per_packet'] ?>">
                                                <input type="number" name="packets" min="0" class="form-control small-input" placeholder="Packets" required>
                                                <input type="number" name="loose_pieces" min="0" class="form-control small-input" placeholder="Pieces" required>
                                                <button type="submit" class="btn btn-danger btn-sm">Minus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
                </main> 

                <div class="modal fade success-modal" id="successModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body text-center">
                                <p class="text-success">Stock updated successfully!</p>
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
            // Show success modal for 3 seconds
            function showSuccessModal() {
                let successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
                setTimeout(() => successModal.hide(), 3000);
            }

            // Filter function for the search bar
            function filterTable() {
                let input = document.getElementById("searchInput");
                let filter = input.value.toUpperCase();
                let table = document.getElementById("productTable");
                let tr = table.getElementsByTagName("tr");

                for (let i = 1; i < tr.length; i++) {
                    let tdName = tr[i].getElementsByTagName("td")[0];
                    if (tdName) {
                        let nameValue = tdName.textContent || tdName.innerText;
                        tr[i].style.display = nameValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
                    }
                }
            }

            function updateDeductionDisplay() {
                const packets = parseInt(document.querySelector('[name="packets"]').value) || 0;
                const loose_pieces = parseInt(document.querySelector('[name="loose_pieces"]').value) || 0;
                const pieces_per_packet = parseInt(document.querySelector('[name="pieces_per_packet"]').value) || 0;
                const total_deduction = (packets * pieces_per_packet) + loose_pieces;

                document.querySelector('#totalDeductionDisplay').textContent = `Total deduction: ${total_deduction} pieces`;
            }
            document.querySelectorAll('[name="packets"], [name="loose_pieces"]').forEach(input => {
                input.addEventListener('input', updateDeductionDisplay);
            });
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
