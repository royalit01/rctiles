<?php 
if(session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // Force JSON output

include '../db_connect.php';

// Check database connection
if (!$mysqli) {
    die(json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]));
}

// Get category_id and total_area
$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$totalArea = isset($_GET['total_area']) ? floatval($_GET['total_area']) : 0;

if ($categoryId <= 0 || $totalArea <= 0) {
    die(json_encode(["error" => "Invalid category or area"]));
}

// Query to fetch products
$query = "SELECT product_id, product_name, description, area,price, product_image FROM products WHERE category_id = ? AND status = 'Active'";

$stmt = $mysqli->prepare($query);

if (!$stmt) {
    die(json_encode(["error" => "SQL Prepare Error: " . $mysqli->error]));
}

$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die(json_encode(["error" => "SQL Execution Error: " . $stmt->error]));
}

// Prepare product list
$products = [];
while ($row = $result->fetch_assoc()) {
    $area_per_unit = !empty($row['area']) ? $row['area'] : "N/A"; // Replace empty values

    $products[] = [
        'id' => $row['product_id'], 
        'name' => $row['product_name'], 
        'description' => $row['description'],
        'area_per_unit' => $area_per_unit,
        'price' => !empty($row['price']) ? $row['price']:0,
        //'image' => !empty($row['product_image']) ? str_replace("\\", "/", $row['product_image']) : "default_img.jpg"
        'image' =>!empty($row['product_image']) 
    ? str_replace("\\", "/", $row['product_image']) 
    : "../assets/img/default_img.jpg" // Corrected path
// Fix image path
    ];
}


// Close resources
$stmt->close();
$mysqli->close();

// Output JSON response
echo json_encode(["category" => $categoryId, "total_area" => $totalArea, "products" => $products], JSON_PRETTY_PRINT);
?>
