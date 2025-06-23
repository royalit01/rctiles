<?php
header('Content-Type: application/json');

// ✅ Ensure correct path
include '../db_connect.php';

// ✅ Remove unwanted output
ob_start(); // Start output buffering
$query = "SELECT category_id, category_name FROM category";
$result = $mysqli->query($query);

$categories = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// ✅ Clean unwanted output before sending JSON
ob_end_clean();
echo json_encode($categories);
exit;
?>
