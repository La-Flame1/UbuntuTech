<?php
$host = 'shuttle.proxy.rlwy.net';
$port = 36908;
$user = 'root';
$password = 'LyblViFjqLUWBTzglddGbgkmiPDGiOAR';
$database = 'railway';

// Create connection
$conn = new mysqli($host, $user, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
