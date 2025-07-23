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
  

    // if (
    //     empty($_POST['customer_name']) || empty($_POST['phone_no']) ||
    //     empty($_POST['address']) || empty($_POST['city']) || empty($_POST['order_id'])
    // ) {
    //     die("Error: Missing required fields.");
    // }

    $customer_name = $_POST['customer_name'];
    $phone_no = $_POST['phone_no'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $final_amount = floatval($_POST['final_price'] ?? 0);
    $rent_amount = floatval($_POST['rent_amount'] ?? 0);
    $order_id = (int)$_POST['order_id'];
    $discounted_amount = floatval($_POST['grand_amount_paid'] ?? 0);
    $returned_amount = floatval($_POST['return_amount'] ?? 0);

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

        // 3. Update existing order (add discounted_amount and returned_amount)
        $stmt = $mysqli->prepare("UPDATE orders SET customer_id = ?, final_amount = ?, rent_amount = ?, discounted_amount = ?, returned_amount = ?, order_date = NOW() WHERE order_id = ?");
        $stmt->bind_param("iddddi", $customer_id, $final_amount, $rent_amount, $discounted_amount, $returned_amount, $order_id);
        $stmt->execute();
        $stmt->close();


        // 4. Update or insert pending_orders, and collect product_ids
        $product_ids = [];
        foreach ($products as $p) {
            if (
                empty($p['id']) || empty($p['name']) ||
                empty($p['quantity']) || empty($p['unitPrice'])
            ) {
                continue;
            }

            $product_id = (int)$p['id'];
            $product_ids[] = $product_id;
            $product_name = $p['name'];
            $qty = (int)$p['quantity'];
            $original_price = (float)$p['unitPrice'];
            $custom_price = (float)($p['totalPrice'] ?? 0);
            $multiplier = (int)($p['multiplier'] ?? 1);

            // Only update existing row, do not insert new
            $stmt = $mysqli->prepare("SELECT COUNT(*) FROM pending_orders WHERE order_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $order_id, $product_id);
            $stmt->execute();
            $stmt->bind_result($exists);
            $stmt->fetch();
            $stmt->close();

            if ($exists) {
                $stmt = $mysqli->prepare(
                    "UPDATE pending_orders SET customer_id = ?, product_name = ?, quantity = ?,  custom_price = ?, multiplier = ? WHERE order_id = ? AND product_id = ?"
                );
                $stmt->bind_param(
                    "isiddii",
                    $customer_id,
                    $product_name,
                    $qty,
                    $custom_price,
                    $multiplier,
                    $order_id,
                    $product_id
                );
                $stmt->execute();
                $stmt->close();
            }
        }

        // 5. Delete pending_orders rows for this order_id that are not in the new product_ids
        if (!empty($product_ids)) {
            $in = implode(',', array_fill(0, count($product_ids), '?'));
            $types = str_repeat('i', count($product_ids) + 1); // +1 for order_id
            $params = array_merge([$order_id], $product_ids);

            $stmt = $mysqli->prepare(
                "DELETE FROM pending_orders WHERE order_id = ? AND product_id NOT IN ($in)"
            );
            $stmt->bind_param($types, ...$params);
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
