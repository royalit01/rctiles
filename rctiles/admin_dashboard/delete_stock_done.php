<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
require '../db_connect.php';
$id = intval($_POST['order_id'] ?? 0);
if(!$id) exit;
$mysqli->query("DELETE FROM orders WHERE order_id = $id AND stock_done = 1");
echo 'OK';
