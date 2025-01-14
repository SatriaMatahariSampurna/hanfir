<?php
$servername = "localhost";
$username = "root";
$password = "locos121";
$database = "hanfi_web";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
