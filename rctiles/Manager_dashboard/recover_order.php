<?php
include '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = intval($_POST['order_id']);

    // Recover data
    $mysqli->query("INSERT INTO orders SELECT * FROM recycle_bin_orders WHERE order_id = $order_id");
    $mysqli->query("INSERT INTO delivery_orders SELECT * FROM recycle_bin_delivery_orders WHERE order_id = $order_id");
    $mysqli->query("INSERT INTO delivery_items 
                  SELECT di.* FROM recycle_bin_delivery_items di 
                  JOIN recycle_bin_delivery_orders do2 ON di.delivery_id = do2.delivery_id 
                  WHERE do2.order_id = $order_id");
    $mysqli->query("INSERT INTO pending_orders SELECT * FROM recycle_bin_pending_orders WHERE order_id = $order_id");

    // Delete from recycle bin
    $mysqli->query("DELETE FROM recycle_bin_delivery_items WHERE delivery_id IN 
                  (SELECT delivery_id FROM recycle_bin_delivery_orders WHERE order_id = $order_id)");
    $mysqli->query("DELETE FROM recycle_bin_delivery_orders WHERE order_id = $order_id");
    $mysqli->query("DELETE FROM recycle_bin_pending_orders WHERE order_id = $order_id");
    $mysqli->query("DELETE FROM recycle_bin_orders WHERE order_id = $order_id");

    header("Location: recycle_bin.php");
}
?>
