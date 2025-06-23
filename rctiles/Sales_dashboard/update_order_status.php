<?php
include '../db_connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if (!$order_id) {
        echo json_encode(["success" => false, "message" => "Invalid Order ID."]);
        exit;
    }

    // Update only pending_orders table (Do NOT update orders table)
    if ($action === 'approve') {
        // Fetch products, quantities, and pieces_per_packet from pending_orders for the approved order
        $fetch_query = "SELECT po.product_id, po.quantity, ps.pieces_per_packet 
                        FROM pending_orders po
                        JOIN product_stock ps ON po.product_id = ps.product_id
                        WHERE po.order_id = ?";
        $fetch_stmt = $mysqli->prepare($fetch_query);
        $fetch_stmt->bind_param("i", $order_id);
        $fetch_stmt->execute();
        $fetch_result = $fetch_stmt->get_result();

        // Loop through each product in the order
        while ($row = $fetch_result->fetch_assoc()) {
            $product_id = $row['product_id'];
            $quantity_received = $row['quantity']; // Quantity received in the order
            $pieces_per_packet = $row['pieces_per_packet']; // Pieces per packet for the product

            // Calculate the total quantity to subtract
            $total_quantity_to_subtract = $pieces_per_packet * $quantity_received;

            // Subtract the total quantity from product_stock
            // $update_stock_query = "UPDATE product_stock SET quantity = quantity - ? WHERE product_id = ?";
            // $update_stock_stmt = $mysqli->prepare($update_stock_query);
            // $update_stock_stmt->bind_param("ii", $total_quantity_to_subtract, $product_id);
            // $update_stock_stmt->execute();
            // $update_stock_stmt->close();

            // Insert a record into the transactions table
            // $transaction_query = "INSERT INTO transactions (user_id, product_id, storage_area_id, transaction_type, quantity, description) 
            //                      VALUES (?, ?, ?, 'Subtext', ?, 'via order')";
            // $transaction_stmt = $mysqli->prepare($transaction_query);
            // $user_id = 1; // Replace with the actual user ID (admin or system user)
            // $storage_area_id = 1; // Replace with the actual storage area ID
            // $transaction_stmt->bind_param("iiis", $user_id, $product_id, $storage_area_id, $total_quantity_to_subtract);
            // $transaction_stmt->execute();
            // $transaction_stmt->close();
        }

        $fetch_stmt->close();

        // Update the approved status in pending_orders
        $query = "UPDATE pending_orders SET approved = 1 WHERE order_id = ?";
    } elseif ($action === 'reject') {
        $query = "UPDATE pending_orders SET approved = -1 WHERE order_id = ?";
    } else {
        echo json_encode(["success" => false, "message" => "Invalid action."]);
        exit;
    }

    // Update the approved status in pending_orders
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "No changes made."]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>