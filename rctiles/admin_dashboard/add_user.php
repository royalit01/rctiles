<?php
if(session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
include "../db_connect.php"; 



    
// if(!isset ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2   )) {
//         header("Location: ../");
//         exit;
// }


// Handle POST request to add a user
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $user_image = 'default_user.png'; // default fallback
$user_image = 'default_img.png'; // Set default image

if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] == 0) {
    $uploadDir = '../uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];

    if (in_array($_FILES['user_image']['type'], $allowedTypes)) {
        $uniqueName = uniqid() . '_' . basename($_FILES['user_image']['name']);
        $targetPath = $uploadDir . $uniqueName;

        if (move_uploaded_file($_FILES['user_image']['tmp_name'], $targetPath)) {
            $user_image = $uniqueName; // Set uploaded image only if successful
        }
    }
}
        // Extract and sanitize input
        $name = htmlspecialchars($_POST['name']);
        $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : null;
        $phone = htmlspecialchars($_POST['phone']);
        $aadhar = isset($_POST['aadhar']) ? htmlspecialchars($_POST['aadhar']) : null;
        if (isset($_POST['password'], $_POST['confirmPassword']) &&
            $_POST['password'] !== $_POST['confirmPassword']) {
            throw new Exception('Passwords do not match.');
        }
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the passwor
        $role_id = $_POST['role_id'];
        $storage_area_id = isset($_POST['storage_area_id']) ? $_POST['storage_area_id'] : null;

        if ($role_id != 3) { // Assuming '3' is the ID for salesperson
            $storage_area_id = null; // Ensure no storage area is assigned if not a salesperson
        }

        // Prepare SQL statement to insert user
$stmt = $mysqli->prepare("INSERT INTO users (name, email, phone_no, aadhar_id_no, password, role_id, storage_area_id, user_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $mysqli->error);
        }

$stmt->bind_param("sssssiis", $name, $email, $phone, $aadhar, $password, $role_id, $storage_area_id, $user_image);

        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $stmt->error);
        }

         echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            const alertBox = document.getElementById('formAlert');
            const alertMsg = document.getElementById('formAlertMsg');
            alertMsg.textContent = 'User added successfully';
            alertBox.classList.remove('d-none', 'alert-danger', 'show');
            alertBox.classList.add('alert-success', 'show');
        });
      </script>";
        
        // Success alert
        // echo "<script>alert('User added successfully');</script>";
    } catch (Exception $e) {
         echo '<script>
        document.addEventListener("DOMContentLoaded", function () {
            const msg = "' . addslashes($e->getMessage()) . '";
            const alertBox = document.getElementById("formAlert");
            const alertMsg = document.getElementById("formAlertMsg");
            alertMsg.textContent = msg;
            alertBox.classList.remove("d-none", "alert-success", "show");
            alertBox.classList.add("alert-danger", "show");
        });
      </script>';
        // Error alert
        //echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Fetch roles
$rolesQuery = "SELECT role_id, role_name FROM roles";
$rolesResult = $mysqli->query($rolesQuery);

// Fetch storage areas
$storageQuery = "SELECT storage_area_id, storage_area_name FROM storage_areas";
$storageResult = $mysqli->query($storageQuery);
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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />               
        <link href="../css/styles.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <style>
  /* Main card styling */
  .card {
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-top: 2rem;
    margin-bottom: 2rem;
  }

  /* Form title */
  h1 {
    color: black;
    font-weight: 600;
    margin-bottom: 1.5rem;
  }

  /* Form labels */
  .form-label {
    font-weight: 550;
    color: #495057;
    margin-bottom: 0.5rem;
  }

  /* Form inputs */
  .form-control {
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    border: 1px solid #ced4da;
    transition: all 0.3s ease;
  }

  .form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  }

  /* Select dropdowns */
  .form-select {
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    border: 1px solid #ced4da;
  }

  /* Alert styling */
  .alert {
    border-radius: 0.75rem;
    max-width: 700px;
    margin: 0 auto 1rem auto;
  }

  /* Button styling */
  .btn-primary {
    border-radius: 0.6rem;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, rgb(92, 140, 212) 0%, rgb(31, 106, 218) 100%);
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, rgb(125, 154, 198) 0%, rgb(37, 106, 210) 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  }

  .btn-primary:active {
    transform: translateY(0);
    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
  }

  /* File input styling */
  .custom-file-input {
    border: 1px solid #ced4da;
    border-radius: 0.75rem;
    padding: 0.5rem 1rem;
    height: 48px;
    font-size: 1rem;
    display: flex;
    align-items: center;
  }

  .custom-file-input::-webkit-file-upload-button {
    background-color: #e9ecef;
    border: none;
    padding: 8px 16px;
    border-radius: 0.5rem 0 0 0.5rem;
    margin-right: 1rem;
    cursor: pointer;
  }

  .custom-file-input::file-selector-button {
    background-color: #e9ecef;
    border: none;
    padding: 9px 16px;
    border-radius: 0.5rem 0.5rem 0.5rem 0.5rem;
    margin-right: 1rem;
    cursor: pointer;
  }

  .custom-file-input:hover::file-selector-button {
    background-color: #d3d3d3;
  }

  /* Password toggle button */
  .input-group-text {
    border-radius: 0 0.75rem 0.75rem 0;
    cursor: pointer;
    background-color: #e9ecef;
    border: 1px solid #ced4da;
  }

  /* Required field indicator */
  .text-danger {
    color: #dc3545;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .card {
      margin: 1rem;
      padding: 1.5rem;
     
    }
    
    h1 {
      font-size: 1.75rem;
    }
  }
</style>
    </head>
    <body class="sb-nav-fixed">
        <?php include "admin_header.php";  ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="card border-0 shadow rounded-3 p-4 bg-white  mx-auto" style="max-width: 900px;">
                        <h1 class="mt-2 text-center">Add User</h1>
                        <!-- error / success messages -->
                        <div id="formAlert" class="alert alert-danger alert-dismissible fade d-none" role="alert">
                            <span id="formAlertMsg"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name"  class="form-label">Name:<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email (optional)">
                                </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number:<span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" maxlength="10" pattern="\d{10}" title="Enter a 10-digit mobile number" required>
                            </div>
                            <div class="mb-3">
                                <label for="aadhar" class="form-label">Aadhar Number:</label>
                                <input type="text" class="form-control" id="aadhar" name="aadhar">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:<span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        required>

                                    <button class="btn btn-outline-secondary"
                                            type="button"
                                            id="togglePassword">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>                            
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password:</label>
                                <div class="input-group">
                                    <input type="password"
                                        class="form-control"
                                        id="confirmPassword"
                                        name="confirmPassword"
                                        required>

                                    <button class="btn btn-outline-secondary"
                                            type="button"
                                            id="toggleConfirm">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>                            
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role:<span class="text-danger">*</span></label>
                                <select class="form-control" id="role" name="role_id" required>
                                    <?php while ($row = $rolesResult->fetch_assoc()): ?>
                                        <option value="<?= $row['role_id']; ?>"><?= $row['role_name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3 hidden" id="storageAreaContainer">
                                <label for="storageArea" class="form-label">Storage Area:</label>
                                <select class="form-control" id="storageArea" name="storage_area_id">
                                    <option value="">Select Storage Area</option>
                                    <?php while ($row = $storageResult->fetch_assoc()): ?>
                                        <option value="<?= $row['storage_area_id']; ?>"><?= $row['storage_area_name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
  <label for="userImage" class="form-label">User Image:</label>
  <input type="file" class="form-control" id="userImage" name="user_image" accept="image/*">
</div>

<!-- Admin Access -->
<div class="mb-3 position-relative">
  <button type="button" class="btn btn-outline-primary w-100" id="adminAccessBtn">
    Admin Access
  </button>
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
  <button type="button" class="btn btn-outline-primary w-100" id="storageAccessBtn">
    Storage Access
  </button>
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
  <button type="button" class="btn btn-outline-primary w-100" id="storageAccessBtn">
    Delivery Access
  </button>
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

<div class="d-flex justify-content-center">
  <button type="submit" class="btn btn-primary">Add User</button>
</div>                        </form>
                    </div>
                </main>
            </div>
        </div>
        <script>
        (() => {
            /* -------- password visibility toggles -------- */
            const toggle = (btnId, fieldId) => {
                document.getElementById(btnId).addEventListener('click', () => {
                    const fld  = document.getElementById(fieldId);
                    const icon = document.querySelector(`#${btnId} i`);
                    const type = fld.type === 'password' ? 'text' : 'password';
                    fld.type = type;
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            };
            toggle('togglePassword', 'password');
            toggle('toggleConfirm',  'confirmPassword');

            /* -------- live phone filter -------- */
            const phoneInput = document.getElementById('phone');
            phoneInput.addEventListener('input', () => {
                phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 10);
            });

            /* -------- final submit guard -------- */
            const form = document.querySelector('form');
            form.addEventListener('submit', (e) => {
                const name     = document.getElementById('name').value.trim();
                const phone    = phoneInput.value.trim();
                const password = document.getElementById('password').value;
                const confirm  = document.getElementById('confirmPassword').value;
                const role     = document.getElementById('role').value;

                /* 1. basic required fields  */
                if (!name || !phone || !password || !role) {
                    alert('Name, Phone Number, Password, and Role are mandatory.');
                    e.preventDefault();
                    return;
                }

                /* 2. 10-digit phone check */
                if (!/^\d{10}$/.test(phone)) {
                    alert('Phone number must be exactly 10 digits.');
                    e.preventDefault();
                    return;
                }

                /* 3. password confirmation  */
                if (password !== confirm) {
                    e.preventDefault();
                    showAlert('Passwords do not match!');
                    return;
                }
            });
        })();

        function showAlert(message, type = 'danger') {
            const alertBox = document.getElementById('formAlert');
            const alertMsg = document.getElementById('formAlertMsg');

            alertMsg.textContent = message;
            alertBox.classList.remove('d-none', 'alert-danger', 'alert-success', 'show');
            alertBox.classList.add('alert-' + type, 'show');
        }

        </script>

        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    </body>

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
        });
</script>


</html>
