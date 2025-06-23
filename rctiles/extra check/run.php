<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tile_shop_crm";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example user details
$users = [
    ['admin', 'admin', 'Admin', 'User', 'admin@example.com', '1234567890', 1, '1234-5678-9012'],
    ['secondary_admin', 'Sadmin', 'Secondary', 'Admin', 'secondary_admin@example.com', '2345678901', 2, '2345-6789-0123'],
    ['manager', 'Manager', 'Manager', 'User', 'manager@example.com', '3456789012', 3, '3456-7890-1234'],
    ['salesman1', 'Salesman1', 'Sales', 'Man1', 'salesman1@example.com', '4567890123', 4, '4567-8901-2345'],
    ['salesman2', 'Salesman2', 'Sales', 'Man2', 'salesman2@example.com', '5678901234', 4, '5678-9012-3456'],
    ['delivery_boy', 'Dboy_password', 'Delivery', 'Boy', 'delivery_boy@example.com', '6789012345', 5, '6789-0123-4567']
];

// Prepare SQL statement
$stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, phone_no, role_id, aadhar_id_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($users as $user) {
    $username = $user[0];
    $password = password_hash($user[1], PASSWORD_DEFAULT); // Hash the password
    $first_name = $user[2];
    $last_name = $user[3];
    $email = $user[4];
    $phone_no = $user[5];
    $role_id = $user[6];
    $aadhar_id_no = $user[7];

    $stmt->bind_param("ssssssis", $username, $password, $first_name, $last_name, $email, $phone_no, $role_id, $aadhar_id_no);
    $stmt->execute();
}

echo "Users inserted successfully";

$stmt->close();
$conn->close();
?>
