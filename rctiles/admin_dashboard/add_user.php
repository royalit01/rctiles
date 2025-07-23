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

        // Get sidebar_index array from POST and encode as JSON
        $sidebar_index = isset($_POST['sidebar_index']) ? json_encode($_POST['sidebar_index']) : json_encode([]);

        // Prepare SQL statement to insert user (add sidebar_index column)
        $stmt = $mysqli->prepare("INSERT INTO users (name, email, phone_no, aadhar_id_no, password, role_id, storage_area_id, user_image, sidebar_index) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $mysqli->error);
        }

        $stmt->bind_param("sssssiiss", $name, $email, $phone, $aadhar, $password, $role_id, $storage_area_id, $user_image, $sidebar_index);

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

<div class="mb-3 position-relative" style="margin: 10px; align-items: center; justify-content: center; justify-space:between;">
  <?php
  // Prepare sidebar_index array for pre-checking checkboxes (for future extensibility)
  $sidebar_index_arr = [];
  if (isset($edit_row['sidebar_index'])) {
      $decoded = json_decode($edit_row['sidebar_index'], true);
      if (is_array($decoded)) {
          $sidebar_index_arr = $decoded;
      }
  }
  ?>

  <div class="d-flex justify-content-center">
    <button type="button" class="btn btn-outline-primary w-100" id="adminAccessBtn">
      Admin Access
    </button>
  </div>
  <div id="adminAccessOptions" class="dropdown-menu p-3" style="width:100%; max-height: 200px; overflow-y: auto; display: none;">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="1" id="admin_dashboard" name="sidebar_index[]" <?php if(in_array("1", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="admin_dashboard">Admin Dashboard</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="2" id="storage_dashboard" name="sidebar_index[]" <?php if(in_array("2", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="storage_dashboard">Storage Dashboard</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="3" id="add_member" name="sidebar_index[]" <?php if(in_array("3", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="add_member">Add Member</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="4" id="edit_view_member" name="sidebar_index[]" <?php if(in_array("4", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="edit_view_member">Edit & View Member</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="5" id="create_order" name="sidebar_index[]" <?php if(in_array("5", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="create_order">Create Order</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="8" id="view_estimate" name="sidebar_index[]" <?php if(in_array("8", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="view_estimate">View Estimate</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="9" id="minus_order_stock" name="sidebar_index[]" <?php if(in_array("9", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="minus_order_stock">Minus Order Stock</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="10" id="assign_delivery" name="sidebar_index[]" <?php if(in_array("10", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="assign_delivery">Assign Delivery</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="11" id="create_bill" name="sidebar_index[]" <?php if(in_array("11", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="create_bill">Create Bill</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="12" id="custom_bill" name="sidebar_index[]" <?php if(in_array("12", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="custom_bill">Custom Bill</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="13" id="members_log" name="sidebar_index[]" <?php if(in_array("13", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="members_log">Members Log</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="14" id="storage_log" name="sidebar_index[]" <?php if(in_array("14", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="storage_log">Storage Log</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="15" id="view_stock" name="sidebar_index[]" <?php if(in_array("15", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="view_stock">View Stock</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="16" id="low_stock" name="sidebar_index[]" <?php if(in_array("16", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="low_stock">Low Stock</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="17" id="recycle_bin" name="sidebar_index[]" <?php if(in_array("17", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="recycle_bin">Recycle Bin</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="18" id="delete_orders" name="sidebar_index[]" <?php if(in_array("18", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="delete_orders">Delete Orders</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="19" id="low_stock_report" name="sidebar_index[]" <?php if(in_array("19", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="low_stock_report">Low Stock Report</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="20" id="customer_ledger" name="sidebar_index[]" <?php if(in_array("20", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="customer_ledger">Customer Ledger</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="21" id="member_ledger" name="sidebar_index[]" <?php if(in_array("21", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="member_ledger">Member Ledger</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="22" id="delivery_payment" name="sidebar_index[]" <?php if(in_array("22", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="delivery_payment">Delivery Payment</label>
    </div>
  </div>
</div>

<!-- Storage Access -->
<div class="mb-3 position-relative">
  <div class="d-flex justify-content-center">
    <button type="button" class="btn btn-outline-primary w-100" id="storageAccessBtn">
      Storage Access
    </button>
  </div>
  <div id="storageAccessOptions" class="dropdown-menu p-3" style="width:100%; max-height: 200px; overflow-y: auto; display: none;">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="23" id="product" name="sidebar_index[]" <?php if(in_array("23", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="product">23. Product</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="24" id="transaction" name="sidebar_index[]" <?php if(in_array("24", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="transaction">24. Transaction</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="25" id="add_stock" name="sidebar_index[]" <?php if(in_array("25", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="add_stock">25. Add Stock</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="26" id="minus_stock" name="sidebar_index[]" <?php if(in_array("26", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="minus_stock">26. Minus Stock</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="27" id="add_product" name="sidebar_index[]" <?php if(in_array("27", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="add_product">27. Add Product</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="28" id="edit_product" name="sidebar_index[]" <?php if(in_array("28", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="edit_product">28. Edit Product</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="29" id="edit_category" name="sidebar_index[]" <?php if(in_array("29", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="edit_category">29. Edit Category</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="30" id="edit_supplier" name="sidebar_index[]" <?php if(in_array("30", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="edit_supplier">30. Edit Supplier</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="31" id="edit_storage" name="sidebar_index[]" <?php if(in_array("31", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="edit_storage">31. Edit Storage Area</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="32" id="stock_transfer" name="sidebar_index[]" <?php if(in_array("32", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="stock_transfer">32. Stock Transfer</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="33" id="excel" name="sidebar_index[]" <?php if(in_array("33", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="excel">33. Stock Update Excel</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="34" id="total_stock_report" name="sidebar_index[]" <?php if(in_array("34", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="total_stock_report">34. Total Stock Report</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="35" id="low_stock_report" name="sidebar_index[]" <?php if(in_array("35", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="low_stock_report">35. Low Stock Report</label>
    </div>
  </div>
</div>

<!-- Delivery Access -->
<div class="mb-3 position-relative">
  <div class="d-flex justify-content-center">
    <button type="button" class="btn btn-outline-primary w-100" id="deliveryAccessBtn">
      Delivery Access
    </button>
  </div>
  <div id="deliveryAccessOptions" class="dropdown-menu p-3" style="width:100%; max-height: 200px; overflow-y: auto; display: none;">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="36" id="dash1" name="sidebar_index[]" <?php if(in_array("36", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="dash1">Delivery Dashboard</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="37" id="dash2" name="sidebar_index[]" <?php if(in_array("37", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="dash2">Live Order List</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="38" id="dash3" name="sidebar_index[]" <?php if(in_array("38", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="dash3">Total Income</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="39" id="dash4" name="sidebar_index[]" <?php if(in_array("39", $sidebar_index_arr)) echo 'checked'; ?>>
      <label class="form-check-label" for="dash4">My Ledger</label>
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
        toggleDropdown('deliveryAccessBtn', 'deliveryAccessOptions');
        });
</script>


</html>
