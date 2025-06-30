<?php
include "../db_connect.php";
session_start();
if ($_SESSION['role_id'] != 4) { http_response_code(403); exit; }

$id  = (int)($_GET['id'] ?? 0);
$uid = (int)$_SESSION['user_id'];

$sql = "
  SELECT d.*, c.name AS customer
  FROM   delivery_orders d
  JOIN   orders  o ON o.order_id = d.order_id
  JOIN   customers c ON c.customer_id = o.customer_id
  WHERE  d.delivery_id = ? AND d.delivery_user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $id, $uid);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_assoc());
