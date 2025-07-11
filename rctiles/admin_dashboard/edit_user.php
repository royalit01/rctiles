<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
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
    $role_name       = $_POST['role_name']        ?: $row['role_name'];

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
                          $newPwdHash, $user_id );
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
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
                        <div class="card border-0 shadow rounded-3 p-4 bg-white my-5 mx-auto" style="max-width: 1200px;">
                            <h2 class="text-center mb-3">Edit User Details</h2>
                            <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th class="d-none d-lg-table-cell">Email</th> <!-- Hides on xs to md, visible on lg and larger -->
                                        <th class="d-none d-lg-table-cell">phone_no</th>
                                        <th class="d-none d-lg-table-cell"> Role </th>
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
                                        echo "<td class='d-none d-lg-table-cell'>" . htmlspecialchars($row['role_name']) . "</td>";
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

                                            <!-- Admin Access -->
<div class="mb-3 position-relative">

<div class="d-flex justify-content-center">
<button type="button" class="btn btn-outline-primary w-auto" id="adminAccessBtn">
    Admin Access
  </button>
</div>
  <div id="adminAccessOptions" class="dropdown-menu p-3" style="width:100%; max-height: 200px; overflow-y: auto; display: none;">

    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="dashboard" id="admin_dashboard">
      <label class="form-check-label" for="admin_dashboard">Admin Dashboard</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="members" id="admin_members">
      <label class="form-check-label" for="admin_users">Add Member</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="edit_n_view" id="edit_n_view">
      <label class="form-check-label" for="admin_reports">Edit & View Member</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="create" id="create_order">
      <label class="form-check-label" for="admin_reports">Create Order</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="view" id="view_order">
      <label class="form-check-label" for="admin_reports">View Order</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="estimate" id="view_estimate">
      <label class="form-check-label" for="admin_reports">View Estimate</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="minus" id="minus_order">
      <label class="form-check-label" for="admin_reports">Minus Stock Order</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="delivery" id="assign_delivery">
      <label class="form-check-label" for="admin_reports">Assign Delivery</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="createbill" id="create_bill">
      <label class="form-check-label" for="admin_reports">Create Bill</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="custombill" id="custom_bill">
      <label class="form-check-label" for="admin_reports">Custom Bill</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="log" id="members_log">
      <label class="form-check-label" for="admin_reports">Members Log</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="bin" id="recycle_bin">
      <label class="form-check-label" for="admin_reports">Recycle Bin</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="delete" id="delete_order">
      <label class="form-check-label" for="admin_reports">Delete Order</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="ledger" id="cutomer_ledger">
      <label class="form-check-label" for="admin_reports">Customer Ledger</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="ledger2" id="member_ledger">
      <label class="form-check-label" for="admin_reports">Member Ledger</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="payment" id="delivery_payment">
      <label class="form-check-label" for="admin_reports">Delivery Payment</label>
    </div>
  </div>
</div>

<!-- Storage Access -->
<div class="mb-3 position-relative">
  
<div class="d-flex justify-content-center">
<button type="button" class="btn btn-outline-primary w-auto" id="storageAccessBtn">
    Storage Access
  </button>
</div>
  <div id="storageAccessOptions" class="dropdown-menu p-3" style="width:100%; max-height: 200px; overflow-y: auto; display: none;">

    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="product" id="product">
      <label class="form-check-label" for="storage_1">Product</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="transaction" id="transaction">
      <label class="form-check-label" for="storage_2">Transaction</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="stock" id="add_stock">
      <label class="form-check-label" for="storage_3">Add Stock</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="stock2" id="minus_stock">
      <label class="form-check-label" for="storage_3">Minus Stock</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="addproduct" id="add_product">
      <label class="form-check-label" for="storage_3">Add Product</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="editproduct" id="edit_product">
      <label class="form-check-label" for="storage_3">Edit Product</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="category" id="edit_category">
      <label class="form-check-label" for="storage_3">Edit Category</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="supplier" id="edit_supplier">
      <label class="form-check-label" for="storage_3">Edit Supplier</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="storage" id="edit_storage">
      <label class="form-check-label" for="storage_3">Edit Storage Area</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="stocktransfer" id="stock_transfer">
      <label class="form-check-label" for="storage_3">Stock Transfer</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="excel" id="excel">
      <label class="form-check-label" for="storage_3">Stock Update Excel</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="report" id="total_stock_report">
      <label class="form-check-label" for="storage_3">Total Stock Report</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="report2" id="low_stock_report">
      <label class="form-check-label" for="storage_3">Stock Update Excel</label>
    </div>
    
  </div>
</div>

<!-- Delivery Access -->
<div class="mb-3 position-relative">
  
<div class="d-flex justify-content-center">
<button type="button" class="btn btn-outline-primary w-auto" id="storageAccessBtn">
    Delivery Access
  </button>
</div>
  <div id="storageAccessOptions" class="dropdown-menu p-3" style="width:100%; max-height: 200px; overflow-y: auto; display: none;">

    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="dash1" id="dash1">
      <label class="form-check-label" for="storage_1">dash1</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="dash2" id="dash2">
      <label class="form-check-label" for="storage_2">dash2</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="dash3" id="dash3">
      <label class="form-check-label" for="storage_3">dash3</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="dash4" id="dash4">
      <label class="form-check-label" for="storage_3">dash4</label>
    </div>
    
  </div>
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
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    
        <script>
            
        document.addEventListener('DOMContentLoaded', function () {
        const toggleDropdown = (buttonId, dropdownId) => {
            const btn = document.getElementById(buttonId);
            const dropdown = document.getElementById(dropdownId);

            btn.addEventListener('click', function () {
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            });

            // Optional: Hide dropdown if clicked outside
            document.addEventListener('click', function (e) {
            if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
            });
        };

        toggleDropdown('adminAccessBtn', 'adminAccessOptions');
        toggleDropdown('storageAccessBtn', 'storageAccessOptions');
        toggleDropdown('dashAccessBtn', 'dashAccessOptions');
        });
</script>
    
    </body>
</html>
