<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
include '../db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);  // show any SQL error right away
$mysqli->set_charset('utf8mb4'); 
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging: Log received POST data
    //  echo "<pre>";
    // print_r($_POST);
    // echo "</pre>";

    // Validate required fields
    if (empty($_POST['customer_name']) || empty($_POST['phone_no']) || empty($_POST['address']) || empty($_POST['city'])) {
        die("Error: Missing required fields.");
    }

    $customer_name = $_POST['customer_name'];
    $phone_no = $_POST['phone_no'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $final_amount = 0;
    $rent_amount = 0;

    // Debugging: Log customer details
    // echo "Customer Name: $customer_name<br>";
    // echo "Phone: $phone_no<br>";
    // echo "Address: $address<br>";
    // echo "City: $city<br>";

    // Decode products JSON
    if (empty($_POST['products'])) {
        die("Error: No products selected.");
    }

    $products = json_decode($_POST['products'], true);
    if (!is_array($products)) {
        die("Error: Invalid products data.");
    }

    // Debugging: Log products
    // echo "<pre>";
    // print_r($products);
    // echo "</pre>";

    // Get final price
    // $final_price = isset($_POST['final_price']) ? floatval($_POST['final_price']) : 0;
    // if ($final_price <= 0) {
    //     die("Error: Invalid final price.");
    // }

    // Start transaction
    $mysqli->begin_transaction();
    try {
        // Step 1: Check if customer exists
        $stmt = $mysqli->prepare("SELECT customer_id FROM customers WHERE phone_no = ?");
        $stmt->bind_param("s", $phone_no);
        $stmt->execute();
        $stmt->bind_result($customer_id);
        $stmt->fetch();
        $stmt->close();

        // Step 2: Insert customer if not exists
        if (!$customer_id) {
            $stmt = $mysqli->prepare("INSERT INTO customers (name, phone_no, address, city) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $customer_name, $phone_no, $address, $city);
            $stmt->execute();
            $customer_id = $stmt->insert_id;
            $stmt->close();
            // echo "New customer inserted. Customer ID: $customer_id<br>";
        } else {
            // echo "Customer already exists. Customer ID: $customer_id<br>";
        }

        // Step 3: Calculate total_amount as the sum of all product original prices (before any discount)
        $total_amount = 0;
        foreach ($products as $product) {
            $total_amount += floatval($product['unitPrice']) * intval($product['quantity']);
        }

        // Now insert order
        $sql = "INSERT INTO orders (customer_id, total_amount, final_amount, rent_amount, order_date) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("iddd", $customer_id, $total_amount, $final_amount, $rent_amount);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();
        // echo "Order inserted. Order ID: $order_id<br>";

        // Step 4: Insert each product into `pending_orders`
        // foreach ($products as $product) {
        //     if (empty($product['id']) || empty($product['name']) || empty($product['quantity']) || empty($product['unitPrice']) || empty($product['totalPrice'])) {
        //         echo "Skipping invalid product:<br>";
        //         print_r($product);
        //         continue;
        //     }

        //     $multiplier = isset($product['multiplier']) ? intval($product['multiplier']) : 1;
        //     $adjusted_quantity = $product['quantity'];  // quantity already includes multiplier from our code
        //     $adjusted_total_price = $product['totalPrice'];  // totalPrice already is the final price

        //     // ✅ Insert into `pending_orders` with default `approved` value of 0
        //     $stmt = $mysqli->prepare("INSERT INTO pending_orders (order_id, customer_id, product_id, product_name, quantity, original_price, custom_price, multiplier, approved)
        //                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
        //     $stmt->bind_param("iiisidid", $order_id, $customer_id, $product['id'], $product['name'], $adjusted_quantity, $product['unitPrice'], $adjusted_total_price, $multiplier);
        //     $stmt->execute();
        //     $stmt->close();
        //     echo "Product inserted: {$product['name']}<br>";
        // }

        /* ---------- STEP-4 : push every product into pending_orders ---------- */

        
// $products = json_decode($_POST['products'], true);


foreach ($products as $p) {

    // minimal validation
    if (
        empty($p['id'])        || empty($p['name'])       ||
        empty($p['quantity'])  || empty($p['unitPrice'])
    ) {
        // skip bad rows but keep the transaction alive
        error_log('⚠️  BAD PRODUCT ROW: ' . print_r($p, true));
        continue;
    }

    // typed values
    $product_id        = (int)   $p['id'];
    $product_name      =         $p['name'];
    $qty               = (int)   $p['quantity'];
    $original_price    = (float) $p['unitPrice'];
    $custom_price      = (float) ($p['totalPrice'] ?? 0);
    $multiplier        = (int)   ($p['multiplier'] ?? 1);   // default 1

  
$user_id = $_SESSION['user_id'];

    // Insert into pending_orders
    $stmt = $mysqli->prepare(
      "INSERT INTO pending_orders
       (order_id, customer_id, product_id, product_name,
        quantity, original_price, custom_price, multiplier, approved)
       VALUES (?,?,?,?,?,?,?, ?,0)
       ON DUPLICATE KEY UPDATE
           quantity      = quantity + VALUES(quantity),
           original_price= VALUES(original_price),
           custom_price  = custom_price + VALUES(custom_price)"
    );
    $stmt->bind_param(
        "iiisiddi",
        $order_id,
        $customer_id,
        $product_id,
        $product_name,
        $qty,
        $original_price,
        $custom_price,
        $multiplier
    );
    $stmt->execute();
    $stmt->close();


    // Insert into pending_orders_estimate (new table)
    // $stmt2 = $mysqli->prepare(
    //   "INSERT INTO pending_orders_estimate
    //    (order_id, customer_id, product_id, product_name, quantity, unit_price, multiplier, user_id)
    //    VALUES (?,?,?,?,?,?,?,?)"
    // );
    // $stmt2->bind_param(
    //   "iiisidii",
    //     $order_id,
    //     $customer_id,
    //     $product_id,
    //     $product_name,
    //     $qty,
    //     $original_price, // this is unit_price
    //     $multiplier,
    //     $user_id
    // );
    // $stmt2->execute();
    // $stmt2->close();
}


        // // Update final amount in orders table
        // $sql = "UPDATE orders SET final_amount = ? WHERE order_id = ?";
        // $stmt = $mysqli->prepare($sql);
        // $stmt->bind_param("di", $final_amount, $order_id);
        // $stmt->execute();
        // $stmt->close();

        // // Update final amount in orders table
        // $sql = "UPDATE orders SET final_amount = ? WHERE order_id = ?";
        // $stmt = $mysqli->prepare($sql);
        // $stmt->bind_param("di", $final_amount, $order_id);
        // $stmt->execute();
        // $stmt->close();

        // Commit transaction
        $mysqli->commit();
        // echo "Transaction committed successfully.<br>";

        // Display success message
        // echo "<div style='text-align:center; margin-top:50px;'>
        //       <h2>Order Submitted Successfully!</h2>
        //       <p><a href='new_order.php'>Create New Order</a></p>
        //       <p><a href='admin_orders.php'>View Orders</a></p>
        //       </div>";
    } catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();
        die("Error: " . $e->getMessage());
    }
} else {
    die("Error: Invalid request method.");
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
        <?php include "admin_header.php";  ?>
        <div id="layoutSidenav_content">
                <main>
                    <div class="d-flex justify-content-center align-items-center vh-100">
                        <div class="text-center">
                        <h2>🎉 Order Submitted Successfully!</h2>
                                <p>Your order has been recorded and sent for approval.</p>
                                <!-- <p><strong>Order ID:</strong>  -->
                        </div>
                    </div>
                </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script> -->
        <!-- <script src="../assets/demo/chart-area-demo.js"></script> -->
        <!-- <script src="../assets/demo/chart-bar-demo.js"></script> -->
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>
