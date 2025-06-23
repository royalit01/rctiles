<?php
include '../db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)$_POST['product_id'];
    $storage_area_id = (int)$_POST['storage_area_id'];

    $stmt = $mysqli->prepare("SELECT quantity, pieces_per_packet FROM product_stock WHERE product_id = ? AND storage_area_id = ?");
    $stmt->bind_param("ii", $product_id, $storage_area_id);
    $stmt->execute();
    $stmt->bind_result($quantity, $ppp);

    if ($stmt->fetch()) {
        $packs = intdiv($quantity, $ppp);
        $pcs = $quantity % $ppp;
        echo "$packs packets / $pcs pieces";
    } else {
        echo "No stock found.";
    }
    $stmt->close();
}
?>
