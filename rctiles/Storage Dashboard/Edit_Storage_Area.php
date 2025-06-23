<?php
// Include database connection
include '../db_connect.php';

$successMessage = '';

// Handle adding a new storage area
if (isset($_POST['addStorage'])) {
    $storage_name = $mysqli->real_escape_string($_POST['storage_name']);
    $storage_address = $mysqli->real_escape_string($_POST['storage_address']);
    $query = "INSERT INTO storage_areas (storage_area_name, location) VALUES ('$storage_name', '$storage_address')";
    if ($mysqli->query($query) === TRUE) {
        $successMessage = 'Storage area added successfully!';
    }
}

// Handle deleting a storage area
if (isset($_POST['deleteStorage'])) {
    $storage_id = $mysqli->real_escape_string($_POST['storage_id']);
    $query = "DELETE FROM storage_areas WHERE storage_area_id  = '$storage_id'";
    if ($mysqli->query($query) === TRUE) {
        $successMessage = 'Storage area deleted successfully!';
    }
}

// Handle updating a storage area
if (isset($_POST['updateStorage'])) {
    $storage_id = $mysqli->real_escape_string($_POST['storage_id']);
    $storage_name = $mysqli->real_escape_string($_POST['storage_name']);
    $storage_address = $mysqli->real_escape_string($_POST['storage_address']);
    $query = "UPDATE storage_areas SET storage_area_name = '$storage_name', location = '$storage_address' WHERE storage_area_id = '$storage_id'";
    if ($mysqli->query($query) === TRUE) {
        $successMessage = 'Storage area updated successfully!';
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
            <div class="container-fluid px-4">
                <h1 class="mt-4">Manage Storage Areas</h1>

                <!-- Add Storage Area Button -->
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addStorageModal">Add New Storage Area</button>

                <!-- Add Storage Area Modal -->
                <div class="modal fade" id="addStorageModal" tabindex="-1" aria-labelledby="addStorageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addStorageModalLabel">Add New Storage Area</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="storage_name" class="form-label">Storage Area Name</label>
                                        <input type="text" class="form-control" name="storage_name" required />
                                    </div>
                                    <div class="mb-3">
                                        <label for="storage_address" class="form-label">Storage Area Address</label>
                                        <input type="text" class="form-control" name="storage_address" required />
                                    </div>
                                    <button type="submit" name="addStorage" class="btn btn-primary">Add</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Bar -->
                <input type="text" class="form-control mb-3" id="searchStorage" placeholder="Search for storage area..." />

                <!-- Storage Areas List Table -->
                <table class="table " id="storageTable">
                    <thead>
                        <tr>
                            <th>Storage Area Name</th>
                            <th>Storage Area Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="storageTableBody">
                        <?php
                        // Fetch all storage areas from the database
                        $result = $mysqli->query("SELECT * FROM storage_areas");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['storage_area_name'] . "</td>";
                            echo "<td>" . $row['location'] . "</td>";
                            echo "<td>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='storage_id' value='" . $row['storage_area_id'] . "' />
                                         <button type='button' class='btn btn-primary' onclick='editStorage(" . $row['storage_area_id'] . ", \"" . htmlspecialchars($row['storage_area_name']) . "\", \"" 
                                        . htmlspecialchars($row['location']) . "\")'>Edit</button>
                                        <button type='button' class='btn btn-danger' onclick='confirmDelete(" . $row['storage_area_id'] . ", \"" . $row['storage_area_name'] . "\")'>Delete</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Edit Storage Area Modal -->
            <div class="modal fade" id="editStorageModal" tabindex="-1" aria-labelledby="editStorageModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editStorageModalLabel">Edit Storage Area</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="edit_storage_name" class="form-label">Storage Area Name</label>
                                    <input type="text" class="form-control" id="edit_storage_name" name="storage_name" required />
                                </div>
                                <div class="mb-3">
                                    <label for="edit_storage_address" class="form-label">Storage Area Address</label>
                                    <input type="text" class="form-control" id="edit_storage_address" name="storage_address" required />
                                </div>
                                <input type="hidden" id="edit_storage_id" name="storage_id">
                                <button type="submit" name="updateStorage" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteStorageModal" tabindex="-1" aria-labelledby="deleteStorageLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteStorageLabel">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete <span id="deleteStorageName" class="fw-bold"></span> storage area?
                        </div>
                        <div class="modal-footer">
                            <form method="POST" action="">
                                <input type="hidden" id="deleteStorageId" name="storage_id">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="deleteStorage" class="btn btn-danger">OK</button>
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
        </main>
    </div>

    <script>
        // Search functionality
        document.getElementById("searchStorage").addEventListener("input", function () {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll("#storageTableBody tr");
            rows.forEach(row => {
                const storageName = row.cells[0].textContent.toLowerCase() + " " + row.cells[1].textContent.toLowerCase();
                row.style.display = storageName.includes(searchText) ? "" : "none";
            });
        });
        
        //Edit Storage
        function editStorage(storageId, storageName, storageAddress) {
            document.getElementById("edit_storage_id").value = storageId;
            document.getElementById("edit_storage_name").value = storageName;
            document.getElementById("edit_storage_address").value = storageAddress;
            new bootstrap.Modal(document.getElementById("editStorageModal")).show();
        }


        // Confirm delete function
        function confirmDelete(storageId, storageName) {
            document.getElementById("deleteStorageId").value = storageId;
            document.getElementById("deleteStorageName").textContent = storageName;
            new bootstrap.Modal(document.getElementById("deleteStorageModal")).show();
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