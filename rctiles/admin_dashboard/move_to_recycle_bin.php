<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
include '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = intval($_POST['order_id']);

    // Move order
    $mysqli->query("INSERT INTO recycle_bin_orders SELECT * FROM orders WHERE order_id = $order_id");
    $mysqli->query("INSERT INTO recycle_bin_delivery_orders SELECT * FROM delivery_orders WHERE order_id = $order_id");
    $mysqli->query("INSERT INTO recycle_bin_delivery_items 
                  SELECT di.* FROM delivery_items di 
                  JOIN delivery_orders do2 ON di.delivery_id = do2.delivery_id 
                  WHERE do2.order_id = $order_id");
    $mysqli->query("INSERT INTO recycle_bin_pending_orders SELECT * FROM pending_orders WHERE order_id = $order_id");

    // Delete from original tables
    $mysqli->query("DELETE FROM delivery_items WHERE delivery_id IN (SELECT delivery_id FROM delivery_orders WHERE order_id = $order_id)");
    $mysqli->query("DELETE FROM delivery_orders WHERE order_id = $order_id");
    $mysqli->query("DELETE FROM pending_orders WHERE order_id = $order_id");
    $mysqli->query("DELETE FROM orders WHERE order_id = $order_id");

    header("Location: delete_orders.php");
}
?>
