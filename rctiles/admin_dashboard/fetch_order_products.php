<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
include '../db_connect.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

$sql = "SELECT order_id, customer_id, product_name, quantity, unit_price, multiplier
        FROM pending_orders_estimate
        WHERE order_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$html = '';
while ($row = $result->fetch_assoc()) {
  $html .= '<tr>
        <td>'.htmlspecialchars($row['product_name']).'</td>
        <td>'.htmlspecialchars($row['quantity']).'</td>
        <td>₹'.number_format($row['unit_price'], 2).'</td>
        <td>'.htmlspecialchars($row['multiplier']).'</td>
    </tr>';
}
echo $html;
$stmt->close();
?>
