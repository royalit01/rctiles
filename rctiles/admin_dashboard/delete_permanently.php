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

    // Clean only from recycle bin
    $mysqli->query("DELETE FROM recycle_bin_delivery_items WHERE delivery_id IN 
                 (SELECT delivery_id FROM recycle_bin_delivery_orders WHERE order_id = $order_id)");
    $mysqli->query("DELETE FROM recycle_bin_delivery_orders WHERE order_id = $order_id");
    $mysqli->query("DELETE FROM recycle_bin_pending_orders WHERE order_id = $order_id");
    $mysqli->query("DELETE FROM recycle_bin_orders WHERE order_id = $order_id");

    header("Location: recycle_bin.php");
}
?>
