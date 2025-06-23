<?php
include "../db_connect.php";

// Fetch dropdown data for storage areas
$storageAreasResult = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas");
$storageAreas = [];
while ($area = $storageAreasResult->fetch_assoc()) {
    $storageAreas[] = $area;
}

// Fetch dropdown data for roles
$rolesResult = $mysqli->query("SELECT role_id, role_name FROM roles");
$roles = [];
while ($role = $rolesResult->fetch_assoc()) {
    $roles[] = $role;
}

// Fetch all users for display
$result = $mysqli->query("SELECT * FROM users");
if (!$result) {
    die('Error retrieving users: ' . $mysqli->error);
}

// Handling user update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $currentUserQuery = $mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
    $currentUserQuery->bind_param("i", $user_id);
    $currentUserQuery->execute();
    $currentUserResult = $currentUserQuery->get_result();
    $currentUser = $currentUserResult->fetch_assoc();

    $name = $_POST['user_name'] ?? $currentUser['name'];
    $email = $_POST['user_email'] ?? $currentUser['email'];
    $phone_no = $_POST['user_phone_no'] ?? $currentUser['phone_no'];
    $storage_area_id = $_POST['storage_area_id'] ?? $currentUser['storage_area_id'];
    $role_id = $_POST['role_id'] ?? $currentUser['role_id'];
    $aadhar_id_no = $_POST['aadhar_id_no'] ?? $currentUser['aadhar_id_no'];
    $password = $_POST['password']; // Make sure to handle password securely

    $image = $_FILES['user_image']['name'] ? $_FILES['user_image']['name'] : $currentUser['user_image'];
    $tmp_name = $_FILES['user_image']['tmp_name'];

    if ($image) {
        move_uploaded_file($tmp_name, "../uploads/" . $image);
    }

    $updateStmt = $mysqli->prepare("UPDATE users SET name = ?, email = ?, phone_no = ?, storage_area_id = ?, role_id = ?, aadhar_id_no = ?, user_image = ? WHERE user_id = ?");
    $updateStmt->bind_param("sssiissi", $name, $email, $phone_no, $storage_area_id, $role_id, $aadhar_id_no, $image, $user_id);

    if (!$updateStmt->execute()) {
        echo "Error: " . $updateStmt->error;
    }
    $updateStmt->close();
}


// Fetch initial data for the modal
if (isset($_GET['edit_user_id'])) {
    $edit_user_id = $_GET['edit_user_id'];
    $edit_result = $mysqli->query("SELECT * FROM users WHERE user_id = $edit_user_id");
    $edit_row = $edit_result->fetch_assoc();
}

// Handling user deletion
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['user_id'])) {
    $stmt = $mysqli->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_GET['user_id']);
    $stmt->execute();
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
        <style>
            @media (max-width: 576px) {
                .btn-sm {
                    padding: .25rem .5rem;
                    font-size: .875rem;
                    line-height: 1.5;
                }
                .table {
                    font-size: 0.8rem;
                }
            }
        </style>

    </head>
    <body class="sb-nav-fixed">
        <?php include "admin_header.php";  ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <div class="container mt-3">
                            <h2 class="text-center mb-3">Edit User Details</h2>
                            <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th class="d-none d-lg-table-cell">Email</th> <!-- Hides on xs to md, visible on lg and larger -->
                                        <th class="d-none d-lg-table-cell">phone_no</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
                                    echo "<tr>";
                                    echo "<td><img src='../uploads/" . htmlspecialchars($row['user_image']) . "?t=" . time() . "' alt='User Image' class='img-fluid rounded-circle' style='width: 50px; height: 50px;'></td>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td class='d-none d-lg-table-cell'>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td class='d-none d-lg-table-cell'>" . htmlspecialchars($row['phone_no']) . "</td>";
                                    echo "<td>
                                    <button class='btn btn-primary btn-sm mt-1' data-bs-toggle='modal' data-bs-target='#editUserModal'
                                        data-bs-userid='" . $row['user_id'] . "'
                                        data-bs-name='" . htmlspecialchars($row['name']) . "'
                                        data-bs-email='" . htmlspecialchars($row['email']) . "'
                                        data-bs-phone='" . htmlspecialchars($row['phone_no']) . "'
                                        data-bs-storagearea='" . $row['storage_area_id'] . "'
                                        data-bs-roleid='" . $row['role_id'] . "'
                                        data-bs-aadhar='" . $row['aadhar_id_no'] . "'
                                        data-bs-image='../uploads/" . htmlspecialchars($row['user_image']) . "?t=" . time() . "'>
                                        Edit
                                    </button>
                                

                                        <a href='?action=delete&user_id=" . $row['user_id'] . "' class='btn btn-danger btn-sm mt-1' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a>
                                        </td>";
                                    echo "</tr>";
                                }
                                ?>

                                </tbody>
                            </table>
                            </div>
                        </div>

                        <!-- Modal for editing user -->
                        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="editUserForm" method="post" action="" enctype="multipart/form-data">
                                            <input type="hidden" name="user_id" id="user_id">
                                            <div class="mb-3">
                                                <label for="user_name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="user_name" name="user_name">
                                            </div>
                                            <div class="mb-3">
                                                <label for="user_email" the class="form-label">Email</label>
                                                <input type="email" class="form-control" id="user_email" name="user_email">
                                            </div>
                                            <div class="mb-3">
                                                <label for="user_phone_no" class="form-label">phone_no</label>
                                                <input type="text" class="form-control" id="user_phone_no" name="user_phone_no">
                                            </div>
                                            <!-- Storage Area Dropdown -->
                                            <div class="mb-3">
                                                <label for="user_storage_area_id" class="form-label">Storage Area</label>
                                                <select class="form-control" id="user_storage_area_id" name="storage_area_id">
                                                    <option value="">Select Storage Area</option>
                                                    <?php foreach ($storageAreas as $area): ?>
                                                        <option value="<?= $area['storage_area_id'] ?>" <?= isset($edit_row) && $edit_row['storage_area_id'] == $area['storage_area_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($area['storage_area_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="user_aadhar_id_no" class="form-label">Aadhar ID</label>
                                                <input type="text" class="form-control" id="user_aadhar_id_no" name="aadhar_id_no">
                                            </div>
                                            <!-- Role Dropdown -->
                                            <div class="mb-3">
                                                <label for="user_role_id" class="form-label">Role</label>
                                                <select class="form-control" id="user_role_id" name="role_id">
                                                    <option value="">Select Role</option>
                                                    <?php foreach ($roles as $role): ?>
                                                        <option value="<?= $role['role_id'] ?>" <?= isset($edit_row) && $edit_row['role_id'] == $role['role_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($role['role_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="user_password" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="user_password" name="password">
                                            </div>
                                            <div class="mb-3">
                                                <label for="user_image" class="form-label">Image</label>
                                                <input type="file" class="form-control" id="user_image" name="user_image">
                                            </div>
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>

        <!-- <script>
            var editUserModal = document.getElementById('editUserModal');
            editUserModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-bs-userid');
                var name = button.getAttribute('data-bs-name');
                var email = button.getAttribute('data-bs-email');
                var phone_no = button.getAttribute('data-bs-phone_no');

                document.getElementById('user_id').value = userId;
                document.getElementById('user_name').value = name;
                document.getElementById('user_email').value = email;
                document.getElementById('user_phone_no').value = phone_no;
            });
        </script> -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var editUserModal = document.getElementById('editUserModal');
                editUserModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    // Extract info from data-* attributes
                    var userId = button.getAttribute('data-bs-userid');
                    var name = button.getAttribute('data-bs-name');
                    var email = button.getAttribute('data-bs-email');
                    var phone = button.getAttribute('data-bs-phone');
                    var storageArea = button.getAttribute('data-bs-storagearea');
                    var roleId = button.getAttribute('data-bs-roleid');
                    var aadhar = button.getAttribute('data-bs-aadhar');
                    var image = button.getAttribute('data-bs-image');

                    // Update the modal's content.
                    document.getElementById('user_id').value = userId;
                    document.getElementById('user_name').value = name;
                    document.getElementById('user_email').value = email;
                    document.getElementById('user_phone_no').value = phone;
                    document.getElementById('user_storage_area_id').value = storageArea;
                    document.getElementById('user_role_id').value = roleId;
                    document.getElementById('user_aadhar_id_no').value = aadhar;
                    // Optionally update image and other fields
                });
            });
        </script>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>
