<?php
include "../db_connect.php"; 

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
        
    </head>
    <body class="sb-nav-fixed">
        <?php include "admin_header.php";  ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="card border-0 shadow rounded-3 p-4 bg-white mx-auto" style="max-width: 1200px;">
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
                            <button type="submit" class="btn btn-primary">Add User</button>
                        </form>
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

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>
