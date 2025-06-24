<?php
$servername = "localhost";
$username = "root";         // Use the new user
$password = "";    // Use the correct password
$dbname = "u997998014_rc_ceramic";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
} else {
    // echo "Connection Done";
}
?>