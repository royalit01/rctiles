<?php
// Include database connection
include '../db_connect.php';

$successMessage = '';

// Handle adding a new supplier
if (isset($_POST['addSupplier'])) {
    $supplier_name = $mysqli->real_escape_string($_POST['supplier_name']);
    $supplier_details = $mysqli->real_escape_string($_POST['supplier_details']);
    $query = "INSERT INTO suppliers (supplier_name, supplier_details) VALUES ('$supplier_name', '$supplier_details')";
    if ($mysqli->query($query) === TRUE) {
        $successMessage = 'Supplier added successfully!';
    }
}

// Handle deleting a supplier
if (isset($_POST['deleteSupplier'])) {
    $supplier_id = $mysqli->real_escape_string($_POST['supplier_id']);
    $query = "DELETE FROM suppliers WHERE supplier_id = '$supplier_id'";
    if ($mysqli->query($query) === TRUE) {
        $successMessage = 'Supplier deleted successfully!';
    }
}

// Handle updating a supplier
if (isset($_POST['updateSupplier'])) {
    $supplier_id = $mysqli->real_escape_string($_POST['supplier_id']);
    $supplier_name = $mysqli->real_escape_string($_POST['supplier_name']);
    $supplier_details = $mysqli->real_escape_string($_POST['supplier_details']);
    $query = "UPDATE suppliers SET supplier_name = '$supplier_name', supplier_details = '$supplier_details' WHERE supplier_id = '$supplier_id'";
    if ($mysqli->query($query) === TRUE) {
        $successMessage = 'Supplier updated successfully!';
    } else {
        // You can handle the error case here
    }
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
 


    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid">
                 <div class="card mt-3 border-0 shadow rounded-3 p-4 bg-white mx-auto" style="max-width: 950px;min-height: 550px;">
                <h1 class="my-4 text-center fw-bold">Manage Suppliers</h1>

                <!-- Add Supplier Button -->
                <button class="btn btn-primary btn-sm mb-3" style="max-width: 200px; min-height:40px" data-bs-toggle="modal" data-bs-target="#addSupplierModal"><i class="fas fa-plus me-1"></i>Add New Supplier</button>

                <!-- Add Supplier Modal -->
                <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addSupplierModalLabel">Add New Supplier</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="supplier_name" class="form-label">Supplier Name</label>
                                        <input type="text" class="form-control" name="supplier_name" required />
                                    </div>
                                    <div class="mb-3">
                                        <label for="supplier_details" class="form-label">Supplier Details</label>
                                        <input type="text" class="form-control" name="supplier_details" required />
                                    </div>
                                    <button type="submit" name="addSupplier" class="btn btn-primary ">Add</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Bar -->
                <input type="text" class="form-control mb-3" id="searchSupplier" placeholder="Search for supplier..." />

                <!-- Suppliers List Table -->
                <table class="table" id="supplierTable">
                    <thead>
                        <tr>
                            <th>Supplier Name</th>
                            <th>Supplier Details</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="supplierTableBody">
                        <?php
                        // Fetch all suppliers from the database
                        $result = $mysqli->query("SELECT * FROM suppliers");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['supplier_name'] . "</td>";
                            echo "<td>" . $row['supplier_details'] . "</td>";
                            echo "<td>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='supplier_id' value='" . $row['supplier_id'] . "' />
                                        <button type='button' class='btn btn-primary mb-2 mb-md-0 ' onclick='editSupplier(" . $row['supplier_id'] . ", \"" . htmlspecialchars($row['supplier_name']) . "\", \"" 
                                        . htmlspecialchars($row['supplier_details']) . "\")'>Edit</button> 
                                        <button type='button' class='btn btn-danger' onclick='confirmDelete(" . $row['supplier_id'] . ", \"" . $row['supplier_name'] . "\")'>Delete</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Edit Supplier Modal -->
            <div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="edit_supplier_name" class="form-label">Supplier Name</label>
                                    <input type="text" class="form-control" id="edit_supplier_name" name="supplier_name" required />
                                </div>
                                <div class="mb-3">
                                    <label for="edit_supplier_details" class="form-label">Supplier Details</label>
                                    <input type="text" class="form-control" id="edit_supplier_details" name="supplier_details" required />
                                </div>
                                <input type="hidden" id="edit_supplier_id" name="supplier_id">
                                <button type="submit" name="updateSupplier" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteSupplierModal" tabindex="-1" aria-labelledby="deleteSupplierLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteSupplierLabel">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete <span id="deleteSupplierName" class="fw-bold"></span> supplier?
                        </div>
                        <div class="modal-footer">
                            <form method="POST" action="">
                                <input type="hidden" id="deleteSupplierId" name="supplier_id">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="deleteSupplier" class="btn btn-danger">OK</button>
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
    </div>

    <script>
        // Search functionality
        document.getElementById("searchSupplier").addEventListener("input", function () {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll("#supplierTableBody tr");
            rows.forEach(row => {
                const supplierName = row.cells[0].textContent.toLowerCase() + " " + row.cells[1].textContent.toLowerCase();
                row.style.display = supplierName.includes(searchText) ? "" : "none";
            });
        });

        // Confirm delete function
        function confirmDelete(supplierId, supplierName) {
            document.getElementById("deleteSupplierId").value = supplierId;
            document.getElementById("deleteSupplierName").textContent = supplierName;
            new bootstrap.Modal(document.getElementById("deleteSupplierModal")).show();
        }
        
        // Edit Supplier
        function editSupplier(supplierId, supplierName, supplierDetails) {
            document.getElementById("edit_supplier_id").value = supplierId;
            document.getElementById("edit_supplier_name").value = supplierName;
            document.getElementById("edit_supplier_details").value = supplierDetails;
            new bootstrap.Modal(document.getElementById("editSupplierModal")).show();
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
</body>
</html>
