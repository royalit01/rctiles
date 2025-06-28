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
// $result = $mysqli->query("SELECT * FROM users");
// if (!$result) {
//     die('Error retrieving users: ' . $mysqli->error);
// }

$result = $mysqli->query("
    SELECT u.*, sa.storage_area_name, r.role_name 
    FROM users u
    LEFT JOIN storage_areas sa ON u.storage_area_id = sa.storage_area_id
    LEFT JOIN roles r ON u.role_id = r.role_id
");


/* ----------------------------------------------------------
   HANDLE USER UPDATE  (runs only on POST)
---------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {

    $user_id = (int)$_POST['user_id'];

    /* 1. Load current row (for fallback values) */
    $current = $mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
    $current->bind_param("i", $user_id);
    $current->execute();
    $row = $current->get_result()->fetch_assoc();

    /* 2. Collect new field values (or keep old) */
    $name            = $_POST['user_name']        ?: $row['name'];
    $email           = $_POST['user_email']       ?: $row['email'];
    $phone_no        = $_POST['user_phone_no']    ?: $row['phone_no'];
    $storage_area_id = $_POST['storage_area_id']  ?: $row['storage_area_id'];
    $role_id         = $_POST['role_id']          ?: $row['role_id'];
    $aadhar_id_no    = $_POST['aadhar_id_no']     ?: $row['aadhar_id_no'];

    /* 3. File upload (optional) */
    $image = $row['user_image'];                        // default = old image
    if (!empty($_FILES['user_image']['name'])) {
        $image = $_FILES['user_image']['name'];
        move_uploaded_file($_FILES['user_image']['tmp_name'],
                           "../uploads/".$image);
    }

    /* 4. Decide whether password will be updated */
    $newPwdPlain = trim($_POST['password'] ?? '');
    $updatePwd   = $newPwdPlain !== '';

    if ($updatePwd) {
        $newPwdHash = password_hash($newPwdPlain, PASSWORD_DEFAULT);
        $sql  = "UPDATE users
                   SET name=?, email=?, phone_no=?, storage_area_id=?,
                       role_id=?, aadhar_id_no=?, user_image=?, password=?
                 WHERE user_id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssiisssi",
                          $name, $email, $phone_no, $storage_area_id,
                          $role_id, $aadhar_id_no, $image,
                          $newPwdHash, $user_id);
    } else {
        $sql  = "UPDATE users
                   SET name=?, email=?, phone_no=?, storage_area_id=?,
                       role_id=?, aadhar_id_no=?, user_image=?
                 WHERE user_id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssiissi",
                          $name, $email, $phone_no, $storage_area_id,
                          $role_id, $aadhar_id_no, $image, $user_id);
    }

    /* 5. Execute exactly once */
    if (!$stmt->execute()) {
        die("Update failed: ".$stmt->error);
    }
    $stmt->close();
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

if (!empty($password)) {
    $password = password_hash($password, PASSWORD_DEFAULT);   // keep it hashed
    $sql  = "UPDATE users
             SET name = ?, email = ?, phone_no = ?, storage_area_id = ?,
                 role_id = ?, aadhar_id_no = ?, user_image = ?, password = ?
             WHERE user_id = ?";
    $updateStmt = $mysqli->prepare($sql);
    $updateStmt->bind_param("sssiisssi",
            $name, $email, $phone_no, $storage_area_id,
            $role_id, $aadhar_id_no, $image, $password, $user_id);
} else {
    $sql  = "UPDATE users
             SET name = ?, email = ?, phone_no = ?, storage_area_id = ?,
                 role_id = ?, aadhar_id_no = ?, user_image = ?
             WHERE user_id = ?";
    $updateStmt = $mysqli->prepare($sql);
    $updateStmt->bind_param("sssiissi",
            $name, $email, $phone_no, $storage_area_id,
            $role_id, $aadhar_id_no, $image, $user_id);
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
                                        echo "<tr data-bs-userid='" . $row['user_id'] . "'"
                                            . " data-bs-name='" . htmlspecialchars($row['name']) . "'"
                                            . " data-bs-email='" . htmlspecialchars($row['email']) . "'"
                                            . " data-bs-phone='" . htmlspecialchars($row['phone_no']) . "'"
                                            . " data-bs-storagearea='" . $row['storage_area_name'] . "'"
                                            . " data-bs-role='" . $row['role_name'] . "'"
                                            . " data-bs-aadhar='" . $row['aadhar_id_no'] . "'"
                                            . " onclick='showUserDetails(this)'>"; // Ensure you define `showUserDetails` in JavaScript
echo "<td><img src='../uploads/" . htmlspecialchars($row['user_image']) . "' alt='User Image' class='img-fluid rounded-circle' style='width: 50px; height: 50px;'></td>";
                                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                        echo "<td class='d-none d-lg-table-cell'>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td class='d-none d-lg-table-cell'>" . htmlspecialchars($row['phone_no']) . "</td>";
                                        echo "<td>
                                                <button class='btn btn-primary btn-sm mt-1 edit-btn' data-bs-toggle='modal' data-bs-target='#editUserModal'
                                        data-bs-userid='" . $row['user_id'] . "'
                                        data-bs-name='" . htmlspecialchars($row['name']) . "'
                                        data-bs-email='" . htmlspecialchars($row['email']) . "'
                                        data-bs-phone='" . htmlspecialchars($row['phone_no']) . "'
                                        data-bs-storagearea='" . $row['storage_area_id'] . "'
                                        data-bs-roleid='" . $row['role_id'] . "'
                                        data-bs-aadhar='" . $row['aadhar_id_no'] . "'
                                        data-bs-image='../uploads/" . htmlspecialchars($row['user_image']) . "?t=" . time() . "'>Edit</button>
                                        <a href='?action=delete&user_id=" . $row['user_id'] . "' class='btn btn-danger btn-sm mt-1 delete-btn' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a>
                                            </td>";
                                        echo "</tr>";
                                    }
                                ?>

                                </tbody>
                            </table>
                            </div>
                        </div>

                        <!-- View User Modal -->
                        <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewUserModalLabel">User Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <strong>Name:</strong> <span id="view_name"></span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Email:</strong> <span id="view_email"></span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Phone Number:</strong> <span id="view_phone_no"></span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Storage Area:</strong> <span id="view_storage_area"></span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Role:</strong> <span id="view_role"></span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Aadhar ID:</strong> <span id="view_aadhar_id_no"></span>
                                        </div>
                                    </div>
                                </div>
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
                                                <div class="input-group">
                                                    <input  type="password"
                                                            class="form-control"
                                                            id="user_password"
                                                            name="password">

                                                    <button class="btn btn-outline-secondary"
                                                            type="button"
                                                            id="toggleEditPwd">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
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
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.edit-btn, .delete-btn').forEach(button => {
                    button.addEventListener('click', function (event) {
                        event.stopPropagation(); // This stops the click event from bubbling up to the parent tr
                    });
                });

                var editUserModal = document.getElementById('editUserModal');
                editUserModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var userId = button.getAttribute('data-bs-userid');
                    var name = button.getAttribute('data-bs-name');
                    var email = button.getAttribute('data-bs-email');
                    var phone = button.getAttribute('data-bs-phone');
                    var storageArea = button.getAttribute('data-bs-storagearea');
                    var roleId = button.getAttribute('data-bs-roleid');
                    var aadhar = button.getAttribute('data-bs-aadhar');
                    var image = button.getAttribute('data-bs-image');

                    document.getElementById('user_id').value = userId;
                    document.getElementById('user_name').value = name;
                    document.getElementById('user_email').value = email;
                    document.getElementById('user_phone_no').value = phone;
                    document.getElementById('user_storage_area_id').value = storageArea;
                    document.getElementById('user_role_id').value = roleId;
                    document.getElementById('user_aadhar_id_no').value = aadhar;
                });
            });

            function showUserDetails(row) {
                // Extract info from data-* attributes
                var userId = row.getAttribute('data-bs-userid');
                var name = row.getAttribute('data-bs-name');
                var email = row.getAttribute('data-bs-email');
                var phone = row.getAttribute('data-bs-phone');
                var storageArea = row.getAttribute('data-bs-storagearea');
                var role = row.getAttribute('data-bs-role');
                var aadhar = row.getAttribute('data-bs-aadhar');

                // Populate the view modal
                document.getElementById('view_name').textContent = name;
                document.getElementById('view_email').textContent = email;
                document.getElementById('view_phone_no').textContent = phone;
                document.getElementById('view_storage_area').textContent = storageArea;
                document.getElementById('view_role').textContent = role;
                document.getElementById('view_aadhar_id_no').textContent = aadhar;

                // Show the modal
                var viewModal = new bootstrap.Modal(document.getElementById('viewUserModal'));
                viewModal.show();
            }

            (() => {
                const btn  = document.getElementById('toggleEditPwd');
                const fld  = document.getElementById('user_password');
                btn.addEventListener('click', () => {
                    const icon = btn.querySelector('i');
                    fld.type = (fld.type === 'password') ? 'text' : 'password';
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            })();
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>
