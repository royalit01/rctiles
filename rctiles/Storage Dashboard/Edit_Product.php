<?php
session_start();
include "../db_connect.php";

// Handle Delete Request with logging
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $productId = $mysqli->real_escape_string($_POST['delete_product_id']);

    // === Fetch product name BEFORE deleting it ===
    $stmtProd = $mysqli->prepare("SELECT product_name FROM products WHERE product_id = ?");
    $stmtProd->bind_param("i", $productId);
    $stmtProd->execute();
    $stmtProd->bind_result($product_name);
    $stmtProd->fetch();
    $stmtProd->close();

    // Perform delete
    $sql = "DELETE FROM products WHERE product_id = $productId";
    if ($mysqli->query($sql)) {
        // === Log the deletion ===
        if (isset($_SESSION['user_id'])) {
            $user_id = (int)$_SESSION['user_id'];

            // Fetch user name
            $stmtUser = $mysqli->prepare("SELECT name FROM users WHERE user_id = ?");
            $stmtUser->bind_param("i", $user_id);
            $stmtUser->execute();
            $stmtUser->bind_result($user_name);
            $stmtUser->fetch();
            $stmtUser->close();

            // Prepare and execute log insert
            $desc = "Product '$product_name' deleted by $user_name";
            $stmtLog = $mysqli->prepare(
                "INSERT INTO transactions 
                   (user_id, product_id, storage_area_id, transaction_type, quantity_changed, transaction_date, description)
                 VALUES (?, ?, NULL, 'Delete', 0, NOW(), ?)"
            );
            $stmtLog->bind_param("iis", $user_id, $productId, $desc);

            if (! $stmtLog->execute()) {
                // Log the error so you can inspect it
                error_log("🚨 transactions INSERT failed: " . $stmtLog->error);
                // Optionally, display it (in dev only!)
                echo "<pre>Log Error: " . $stmtLog->error . "</pre>";
            }
            $stmtLog->close();
        }

        $deleteMessage = 'Product deleted successfully!';
    } else {
        $deleteMessage = 'Failed to delete the product: ' . $mysqli->error;
    }
}

$storage_areas = $categories = $products = [];

// Fetch storage areas
$result = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas");
while ($row = $result->fetch_assoc()) {
    $storage_areas[] = $row;
}

// Fetch all categories
$category_result = $mysqli->query("SELECT DISTINCT category_id, category_name FROM category");
while ($cat_row = $category_result->fetch_assoc()) {
    $categories[] = $cat_row;
}

$whereClauses = [];
$selectedStorageArea = $_POST['storage_area_id'] ?? null;
$selectedCategory = $_POST['category_id'] ?? null;

// Pagination setup
$productsPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $productsPerPage;

// Count total products for pagination
$count_query = "SELECT COUNT(DISTINCT ps.product_id, ps.pieces_per_packet) as total
                FROM products p
                JOIN product_stock ps ON p.product_id = ps.product_id
                JOIN category c ON p.category_id = c.category_id";
if (count($whereClauses) > 0) {
    $count_query .= " WHERE " . implode(' AND ', $whereClauses);
}
$count_result = $mysqli->query($count_query);
$totalProducts = $count_result ? (int)$count_result->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalProducts / $productsPerPage);

// Modify the product query based on selected options
$product_query = "SELECT p.product_name, p.description, ps.product_id, SUM(ps.quantity) as total_quantity, 
                         ps.pieces_per_packet, ps.min_stock_level, p.product_image, 
                         c.category_name
                  FROM products p
                  JOIN product_stock ps ON p.product_id = ps.product_id
                  JOIN category c ON p.category_id = c.category_id"; // Join category table

if ($selectedStorageArea) {
    $whereClauses[] = "ps.storage_area_id = " . intval($selectedStorageArea);
}

if ($selectedCategory) {
    $whereClauses[] = "p.category_id = " . intval($selectedCategory);
}

if (count($whereClauses) > 0) {
    $product_query .= " WHERE " . implode(' AND ', $whereClauses);
}

$product_query .= " GROUP BY ps.product_id, ps.pieces_per_packet";
$product_query .= " LIMIT $productsPerPage OFFSET $offset";

$result = $mysqli->query($product_query);
while ($prod_row = $result->fetch_assoc()) {
    $products[] = $prod_row;
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
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.3.0/css/all.css">
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <style>
        .product-image {
            max-width: 100px;
            height: auto;
        }
    </style>   
    </head>
    <body class="sb-nav-fixed">
  <?php  include 'navbar.php'; ?>
            <!-- ---------------------------- -->
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid">
                        <h1 class="mb-4 mt-3">Edit Product</h1>
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
                <input type="text" id="searchInput" class="form-control" placeholder="Search products..." onkeyup="filterTable()" style="max-width: 410px; width: 100%;">
            </div>
            <div class="table-responsive">
                <table class="table align-middle" id="sortableTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th onclick="sortTable(1)">Product<span class="sort-icon" style="float:right;">⬍</span></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <?php
                        $packets = intdiv($product['total_quantity'], $product['pieces_per_packet']);
                        $pieces = $product['total_quantity'] % $product['pieces_per_packet'];
                        ?>
                        <tr>
                            <td onclick="event.stopPropagation();">
                                <img src="../uploads/<?= htmlspecialchars($product['product_image']) ?>" 
                                     alt="" 
                                     class="rounded-circle border border-primary" 
                                     style="width: 50px; height: 50px; cursor: pointer;"
                                     onclick="showImageModal('<?= htmlspecialchars($product['product_image']) ?>');">
                            </td>
                            <td>
                                <div>
                                    <strong class="text-dark"><?= htmlspecialchars($product['product_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($product['category_name']) ?></small> &nbsp;<span class="badge text-bg-warning"><?= htmlspecialchars($product['description']) ?></span><br>
                                    <span class="badge bg-primary"><?= $packets ?> Box</span>
                                    <span class="badge bg-secondary mt-1"><?= $pieces ?> Pc</span>
                                </div>
                            </td>
                            <td>
                                <a href="Update_Product.php?product_id=<?= $product['product_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $product['product_id'] ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Pagination Controls -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?><?= isset($_POST['storage_area_id']) ? '&storage_area_id=' . $_POST['storage_area_id'] : '' ?><?= isset($_POST['category_id']) ? '&category_id=' . $_POST['category_id'] : '' ?>">Previous</a></li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= isset($_POST['storage_area_id']) ? '&storage_area_id=' . $_POST['storage_area_id'] : '' ?><?= isset($_POST['category_id']) ? '&category_id=' . $_POST['category_id'] : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?><?= isset($_POST['storage_area_id']) ? '&storage_area_id=' . $_POST['storage_area_id'] : '' ?><?= isset($_POST['category_id']) ? '&category_id=' . $_POST['category_id'] : '' ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
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

                <!-- <footer class="py-4 bg-light mt-auto">
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
            </footer> -->
            </div>
         <!-- ---------------------------- -->   
        </div>
        
      
        <!-- Product Details Modal -->
        <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productDetailsModalLabel">Product Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Product details will be filled by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

<!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">Are you sure you want to delete this product?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">OK</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><p id="successMessage"></p></div>
            </div>
        </div>
    </div>
       
        <script>
           function filterTable() {
    let input = document.getElementById("searchInput");
    let filter = input.value.toUpperCase();
    let table = document.querySelector("table"); // Adjust selector if needed
    let tr = table.getElementsByTagName("tr");

    // Adjust the index below if the product name is not in the first visible td
    const nameColumnIndex = 1; // Change this index based on which td contains the product name

    for (let i = 1; i < tr.length; i++) { // Start at 1 to skip header row if it is within the same <tbody>
        let tdName = tr[i].getElementsByTagName("td")[nameColumnIndex]; // Now dynamic based on actual layout
        if (tdName) {
            let nameValue = tdName.textContent || tdName.innerText;
            if (nameValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
 let currentColumn = null; // Track the currently sorted column
    let sortDirection = true; // true for ascending, false for descending

    function sortTable(columnIndex) {
      const table = document.getElementById("sortableTable");
      const rows = Array.from(table.rows).slice(1); // Exclude the header row
      sortDirection = columnIndex === currentColumn ? !sortDirection : true;
      currentColumn = columnIndex;

      rows.sort((a, b) => {
        const cellA = a.cells[columnIndex].innerText.toLowerCase();
        const cellB = b.cells[columnIndex].innerText.toLowerCase();

        if (cellA < cellB) return sortDirection ? -1 : 1;
        if (cellA > cellB) return sortDirection ? 1 : -1;
        return 0;
      });

      // Append sorted rows to the table
      rows.forEach(row => table.tBodies[0].appendChild(row));

      updateIcons(columnIndex);
    }

    function updateIcons(columnIndex) {
      const headers = document.querySelectorAll("#sortableTable th .sort-icon");
      headers.forEach((icon, index) => {
        if (index === columnIndex) {
          icon.textContent = sortDirection ? "⬆" : "⬇";
        } else {
          icon.textContent = "⬍"; // Reset icon for other columns
        }
      });
    }
            
            //Show Image Modal
            function showImageModal(imageUrl) {
                const modalBody = document.querySelector('#imageModal .modal-body');
                modalBody.innerHTML = `<img src="../uploads/${imageUrl}" alt="Product Image" class="img-fluid">`;
                new bootstrap.Modal(document.getElementById('imageModal')).show();
            }

            
            //Show product Details
            function showProductDetails(product) {
                const modalBody = document.querySelector('#productDetailsModal .modal-body');
                modalBody.innerHTML = `
                    <p><strong>Name:</strong> ${product.name}</p>
                    <p><strong>Description:</strong> ${product.description}</p>
                    <p><strong>Category:</strong> ${product.category}</p>
                    <p><strong>Stock Level:</strong> ${product.minStockLevel}</p>
                    <p><strong>Boxes:</strong> ${product.boxes} Box</p>
                    <p><strong>Pieces:</strong> ${product.pieces} Pc</p>
                `;
                new bootstrap.Modal(document.getElementById('productDetailsModal')).show();
            }
            
            function confirmDelete(productId) {
                document.getElementById('confirmDeleteButton').onclick = function() {
                    performDeletion(productId);
                };
                new bootstrap.Modal(document.getElementById('deleteConfirmationModal')).show();
            }
            
            function performDeletion(productId) {
                const formData = new FormData();
                formData.append('delete_product_id', productId);
                fetch('', { method: 'POST', body: formData })
                    .then(response => response.text())
                    .then(() => {
                        document.getElementById('successMessage').textContent = 'Product deleted successfully!';
                        new bootstrap.Modal(document.getElementById('successModal')).show();
                        setTimeout(() => window.location.reload(), 2000); // Refresh after 2 seconds
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('successMessage').textContent = 'Failed to delete the product.';
                        new bootstrap.Modal(document.getElementById('successModal')).show();
                    });
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
