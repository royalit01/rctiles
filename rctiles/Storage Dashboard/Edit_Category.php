
<?php
// Include database connection
include '../db_connect.php';

$successMessage = '';

// Handle adding a new category
if (isset($_POST['addCategory'])) {
    $category_name = $mysqli->real_escape_string($_POST['category_name']);
    $query = "INSERT INTO category (category_name) VALUES ('$category_name')";
    if ($mysqli->query($query) === TRUE) {
        $successMessage = 'Category added successfully!';
    }
}

// Handle deleting a category
if (isset($_POST['deleteCategory'])) {
    $category_id = $mysqli->real_escape_string($_POST['category_id']);
    $query = "DELETE FROM category WHERE category_id = '$category_id'";
    if ($mysqli->query($query) === TRUE) {
        $successMessage = 'Category deleted successfully!';
    }
}

// Handle updating a category
if (isset($_POST['updateCategory'])) {
    $category_id = $mysqli->real_escape_string($_POST['category_id']);
    $category_name = $mysqli->real_escape_string($_POST['category_name']);
    $query = "UPDATE category SET category_name = '$category_name' WHERE category_id = '$category_id'";
    if ($mysqli->query($query) === TRUE) {
        $successMessage = 'Category updated successfully!';
    }
}

// Fetch all categories from the database

$result = $mysqli->query("SELECT * FROM category");

// Calculate total category count
$countResult = $mysqli->query("SELECT COUNT(*) as total FROM category");
$totalCategories = $countResult->fetch_assoc()['total'];
// ...existing code...

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
                    <div class="container-fluid ">
                          <div class="card border-0 shadow my-3 rounded-3 p-4 bg-white mx-auto" style="max-width: 950px; min-height: 550px;">

                        <h1 class="fw-bold text-center m-2 mb-4">Edit Category</h1>

                        <button class="btn btn-primary btn-sm mb-3" style="max-width: 200px;min-height:40px" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
  <i class="fas fa-plus me-1"></i> Add New Category
</button>         <p class="fw-bold mb-2" style="font-size: 1.5rem;">Total Categories: <?php echo $totalCategories; ?></p>

                        <!-- Add Category Modal -->
                        <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
                            
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="Edit_Category.php">
                                            <div class="mb-3">
                                                <label for="category_name" class="form-label">Category Name</label>
                                                <input type="text" class="form-control" name="category_name" required />
                                            </div>
                                            <button type="submit" name="addCategory" class="btn btn-primary">Add</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search Bar -->
                        <input type="text" class="form-control mb-3" id="searchCategory" placeholder="Search for category..." />

                        <!-- Categories List Table -->
                        <table class="table" id="categoryTable">
                            <thead>
                                <tr>
                                    <th>Category Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="categoryTableBody">
                                <?php
                                // Fetch all categories from the database
                                $result = $mysqli->query("SELECT * FROM category");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['category_name'] . "</td>";
                                    echo "<td>
                                            <form method='POST' style='display:inline;'>
                                                <input type='hidden' name='category_id' value='" . $row['category_id'] . "' />
                                                <button type='button' class='btn btn-danger' onclick='confirmDelete(" . $row['category_id'] . ", \"" . $row['category_name'] . "\")'>Delete</button>
                                                <button type='button' class='btn btn-primary' onclick='editCategory(" . $row['category_id'] . ", \"" . htmlspecialchars($row['category_name']) . "\")'>Edit</button>
                                            </form>
                                        </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Edit Category Modal -->
                    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="Edit_Category.php">
                                        <div class="mb-3">
                                            <label for="edit_category_name" class="form-label">Category Name</label>
                                            <input type="text" class="form-control" id="edit_category_name" name="category_name" required />
                                            <input type="hidden" id="edit_category_id" name="category_id">
                                        </div>
                                        <button type="submit" name="updateCategory" class="btn btn-primary">Save</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteConfirmationLabel">Confirm Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete <span id="deleteCategoryName" class="fw-bold"></span> category?
                                </div>
                                <div class="modal-footer">
                                    <form method="POST" action="Edit_Category.php">
                                        <input type="hidden" id="deleteCategoryId" name="category_id">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="deleteCategory" class="btn btn-danger">OK</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success Modal -->
                    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                            <div class="modal-body">
                            <p id="successMessage"></p>
                        </div>
                            </div>
                    </div>
                </main> 

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
            // Search functionality
            document.getElementById("searchCategory").addEventListener("input", function () {
                const searchText = this.value.toLowerCase();
                const rows = document.querySelectorAll("#categoryTableBody tr");
                rows.forEach(row => {
                    const categoryName = row.querySelector("td").textContent.toLowerCase();
                    row.style.display = categoryName.includes(searchText) ? "" : "none";
                });
            });

            // Confirm delete function
            function confirmDelete(categoryId, categoryName) {
                document.getElementById("deleteCategoryId").value = categoryId;
                document.getElementById("deleteCategoryName").textContent = categoryName;
                new bootstrap.Modal(document.getElementById("deleteConfirmationModal")).show();
            }
            
            // Edit Category
            function editCategory(categoryId, categoryName) {
                document.getElementById("edit_category_id").value = categoryId;
                document.getElementById("edit_category_name").value = categoryName;
                new bootstrap.Modal(document.getElementById("editCategoryModal")).show();
            }


            // Show success modal
            <?php if (!empty($successMessage)) { ?>
                document.addEventListener("DOMContentLoaded", function() {
                    document.getElementById("successMessage").textContent = "<?php echo $successMessage; ?>";
                    const successModal = new bootstrap.Modal(document.getElementById("successModal"));
                    successModal.show();

                    // Hide the success modal after 3 seconds
                    setTimeout(function() {
                        successModal.hide();
                    }, 3000);
                });
            <?php } ?>
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
