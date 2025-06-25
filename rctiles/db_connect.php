<?php
<<<<<<< Updated upstream
$servername = "";  // Replace with your server name
$username = "u997998014_rc_ceramic";         // Replace with your database username
$password = "Ayush@1786";             // Replace with your database password
=======
$servername = "localhost";  // Replace with your server name
$username = "root";         // Replace with your database username
$password = "";             // Replace with your database password
>>>>>>> Stashed changes
$dbname = "u997998014_rc_ceramic";       // Your database name

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
else{
    // echo "Connection Done";
}
?>
