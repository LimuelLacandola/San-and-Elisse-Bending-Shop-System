<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "saeb_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->query("SET time_zone = '+08:00'");

?>