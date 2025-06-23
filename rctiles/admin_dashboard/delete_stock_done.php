<?php
require '../db_connect.php';
$id = intval($_POST['order_id'] ?? 0);
if(!$id) exit;
$mysqli->query("DELETE FROM orders WHERE order_id = $id AND stock_done = 1");
echo 'OK';
