<?php
session_start();  // Start the session at the beginning of the script

include './db_connect.php';  // Include your database connection details

$login_err = '';  // Initialize the login error message variable

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone_no = trim($_POST['phone_no']);  // Get the phone number from form
    $password = $_POST['password'];        // Get the password from form

    // SQL to check the existence of user with the given phone number and fetch their role
    $sql = "SELECT user_id, password, role_id,name FROM users WHERE phone_no = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        // Bind the phone number to the prepared SQL statement
        $stmt->bind_param("s", $phone_no);

        // Execute the statement
        if ($stmt->execute()) {
            // Store the result to check if the user exists
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // Bind the results
                $stmt->bind_result($user_id, $hashed_password, $role_id,$user_name);

                if ($stmt->fetch()) {
                    // Use password_verify to check if the entered password matches the one stored
                    if (password_verify($password, $hashed_password)) {
                        // Password is correct, so start a new session
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['phone_no'] = $phone_no;
                        $_SESSION['role_id'] = $role_id;
                        $_SESSION['user_name'] = $user_name;  

                        // Redirect to different dashboards based on the role
                        // switch ($role_id) {
                        //     case 1:  // Assuming 1 is the role_id for Admin
                        //         header("location: Storage Dashboard");
                        //         break;
                        //     case 2:  // Assuming 2 is the role_id for Manager
                        //         header("location: Storage Dashboard");
                        //         break;
                        //     case 3:  // Assuming 3 is the role_id for Salesperson
                        //         header("location: Storage Dashboard");
                        //         break;
                        //     case 4:  // Assuming 3 is the role_id for Salesperson
                        //     header("location: Storage Dashboard");
                        //     break;
                        //     default:
                        //         header("location: Storage Dashboard");  // Default dashboard for other roles
                        //         break;
                        // }
                        switch ($role_id) {
                            case 1:  // Assuming 1 is the role_id for Admin
                                header("location: admin_dashboard/admin_orders.php");
                                break;
                            case 2:  // Assuming 2 is the role_id for Manager
                                header("location: Manager_dashboard/manager_dashboard.php");
                                break;
                            case 3:  // Assuming 3 is the role_id for Salesperson
                                header("location: Sales_dashboard/sales_dashboard.php");
                                break;
                            case 4:  // Assuming 3 is the role_id for Salesperson
                                header("location: delivery_dashboard/delivery_dashboard.php");
                                break;
                            default:
                                header("location: user_dashboard.php");  // Default dashboard for other roles
                                break;
                        }
                        exit;
                    } else {
                        // Password is not correct
                        $login_err = 'Invalid password.';
                    }
                }
            } else {
                // No user with that phone number
                $login_err = 'No account found with that phone number.';
            }
        } else {
            $login_err = "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }

    // Close connection
    $mysqli->close();
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
        <title>Login - SB Admin</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body style="background-image: url('./assets/img/bg.jpg');">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header"><h3 class="text-center font-weight-light my-4">Login</h3></div>
                                <div class="card-body">
                                    <?php if ($login_err): ?>
                                        <div class="alert alert-danger"><?php echo $login_err; ?></div>
                                    <?php endif; ?>
                                    <form action="" method="POST">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="input_phone_no" type="text" name="phone_no" placeholder="Phone number" required />
                                            <label for="input_phone_no">Phone number</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputPassword" type="password" name="password" placeholder="Password" required />
                                            <label for="inputPassword">Password</label>
                                        </div>
                                        <!-- <div class="form-check mb-3">
                                            <input class="form-check-input" id="inputRememberPassword" type="checkbox" value="" />
                                            <label class="form-check-label" for="inputRememberPassword">Remember Password</label>
                                        </div> -->
                                        <div class="d-grid gap-1">
                                            <!-- <a class="small" href="password.html">Forgot Password?</a> -->
                                            <button class="btn btn-primary" type="submit">Login</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="#">Need an account? Sign up!</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
