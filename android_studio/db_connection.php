<?php
// Database connection
$servername = "127.0.0.1";
$username = "saeb_root";
$password = "DFAqynMeGeK!CmDL";
$dbname = "saeb_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>