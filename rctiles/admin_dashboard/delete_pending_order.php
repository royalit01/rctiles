<?php
// delete_pending_order.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../db_connect.php';
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';
    if ($order_id && $product_name) {
        $stmt = $mysqli->prepare("DELETE FROM pending_orders WHERE order_id = ? AND product_name = ?");
        $stmt->bind_param("is", $order_id, $product_name);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
