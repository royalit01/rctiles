<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}

include '../db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli->set_charset('utf8mb4');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST['customer_name']) || empty($_POST['phone_no']) ||
        empty($_POST['address']) || empty($_POST['city']) || empty($_POST['order_id'])
    ) {
        die("Error: Missing required fields.");
    }

    $customer_name = $_POST['customer_name'];
    $phone_no = $_POST['phone_no'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $final_amount = floatval($_POST['final_price'] ?? 0);
    $rent_amount = floatval($_POST['rent_amount'] ?? 0);
    $order_id = (int)$_POST['order_id'];

    if (empty($_POST['products'])) {
        die("Error: No products selected.");
    }

    $products = json_decode($_POST['products'], true);
    if (!is_array($products)) {
        die("Error: Invalid products data.");
    }

    $mysqli->begin_transaction();

    try {
        // 1. Get or create customer
        $stmt = $mysqli->prepare("SELECT customer_id FROM customers WHERE phone_no = ?");
        $stmt->bind_param("s", $phone_no);
        $stmt->execute();
        $stmt->bind_result($customer_id);
        $stmt->fetch();
        $stmt->close();

        if (!$customer_id) {
            $stmt = $mysqli->prepare("INSERT INTO customers (name, phone_no, address, city) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $customer_name, $phone_no, $address, $city);
            $stmt->execute();
            $customer_id = $stmt->insert_id;
            $stmt->close();
        }

        // 2. Calculate total amount
        $total_amount = 0;
        foreach ($products as $product) {
            $total_amount += floatval($product['unitPrice']) * intval($product['quantity']);
        }

        // 3. Update existing order
        $stmt = $mysqli->prepare("UPDATE orders SET customer_id = ?, total_amount = ?, final_amount = ?, rent_amount = ?, order_date = NOW() WHERE order_id = ?");
        $stmt->bind_param("idddi", $customer_id, $total_amount, $final_amount, $rent_amount, $order_id);
        $stmt->execute();
        $stmt->close();

        // 4. Delete old pending_orders
        $stmt = $mysqli->prepare("DELETE FROM pending_orders WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();

        // 5. Insert new pending_orders
        foreach ($products as $p) {
            if (
                empty($p['id']) || empty($p['name']) ||
                empty($p['quantity']) || empty($p['unitPrice'])
            ) {
                continue;
            }

            $product_id = (int)$p['id'];
            $product_name = $p['name'];
            $qty = (int)$p['quantity'];
            $original_price = (float)$p['unitPrice'];
            $custom_price = (float)($p['totalPrice'] ?? 0);
            $multiplier = (int)($p['multiplier'] ?? 1);

            $stmt = $mysqli->prepare(
                "INSERT INTO pending_orders
                 (order_id, customer_id, product_id, product_name, quantity, original_price, custom_price, multiplier, approved)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)"
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
        }

        $mysqli->commit();

    } catch (Exception $e) {
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
    <title>Order Submitted</title>
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <?php include "admin_header.php"; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="d-flex justify-content-center align-items-center vh-100">
                <div class="text-center">
                    <h2>🎉 Order Updated Successfully!</h2>
                    <p>Your order has been updated and saved.</p>
                    <p><a class="btn btn-primary" href="admin_orders.php">Back to Orders</a></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
