<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
include '../db_connect.php';

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);

    $sql = "SELECT product_name, quantity, original_price, custom_price 
            FROM pending_orders 
            WHERE order_id = ?";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    

    while ($row = $result->fetch_assoc()) {

         // per-unit custom price
        $unit_price = $row['quantity'] ? $row['custom_price'] / $row['quantity'] : 0;

        echo "<tr>
                <td>{$row['product_name']}</td>
                <td>{$row['quantity']}</td>
                <td>₹{$row['original_price']}</td>
                 <td>₹{$unit_price}</td>
              </tr>";
    }

    $stmt->close();
}
?>
