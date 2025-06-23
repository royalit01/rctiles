<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require '../db_connect.php';
session_start();

$order_id    = intval($_POST['order_id'] ?? 0);
$prodIds     = $_POST['product_id']  ?? [];
$saChosen    = $_POST['sa']          ?? [];
$minusQtyArr = $_POST['minus_qty']   ?? [];
$user_id     = $_SESSION['user_id'] ?? 4;   // or whoever is logged in

// if(!$order_id || count($prodIds)==0){ die('Bad data'); }
if (!$order_id) {                 // keep this
    die('Bad data');
}

$mysqli->begin_transaction();

try {
    /* loop rows */
    for($i=0; $i<count($prodIds); $i++){
        $pid   = intval($prodIds[$i]);
        $sa    = intval($saChosen[$i]);
        $minus = max(0, intval($minusQtyArr[$i]));
        if(!$minus) continue;

        /* 1) UPDATE product_stock */
        $up = $mysqli->prepare("
        UPDATE product_stock
        SET quantity = quantity - (? * pieces_per_packet)
        WHERE product_id = ? AND storage_area_id = ?");
        $up->bind_param('iii', $minus, $pid, $sa);
        $up->execute();

        /* 2) INSERT into minus_stock to track subtracted quantities */
        $tr = $mysqli->prepare(
            "INSERT INTO minus_stock
               (order_id, product_id, storage_area_id, quantity_subtracted, subtracted_by, subtracted_at)
             VALUES (?,?,?,?,?,NOW())");
        $tr->bind_param('iiiii', $order_id, $pid, $sa, $minus, $user_id);
        $tr->execute();
    }

    /* Check if all quantities have been subtracted */
    $check = $mysqli->prepare("
        SELECT SUM(po.quantity) AS total_ordered,
               SUM(COALESCE(di.qty_delivered, 0)) AS total_delivered,
               SUM(COALESCE(ms.quantity_subtracted, 0)) AS total_subtracted
        FROM pending_orders po
        LEFT JOIN delivery_orders do2 ON do2.order_id = po.order_id
        LEFT JOIN delivery_items di ON di.delivery_id = do2.delivery_id AND di.product_id = po.product_id
        LEFT JOIN minus_stock ms ON ms.order_id = po.order_id AND ms.product_id = po.product_id
        WHERE po.order_id = ?
        GROUP BY po.order_id");
    $check->bind_param('i', $order_id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();
    
    // if ($result['total_ordered'] <= ($result['total_delivered'] + $result['total_subtracted'])) {
    //     // Mark as completed if all quantities are accounted for
    //     $mysqli->query("UPDATE orders SET stock_done = 1 WHERE order_id = $order_id");
    // }
     if ($result
        && $result['total_ordered']
           <= ($result['total_delivered'] + $result['total_subtracted'])) {

        // mark order complete
        $mysqli->query("UPDATE orders
                        SET stock_done = 1
                        WHERE order_id = $order_id");
    }

    $mysqli->commit();
    echo 'OK';
} catch(Exception $e){
    $mysqli->rollback();
    http_response_code(500);
    echo 'Error: '.$e->getMessage();
}