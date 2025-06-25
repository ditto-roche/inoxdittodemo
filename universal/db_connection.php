<?php
$host = "127.0.0.1";
$port = 3307;
$dbname = "ditto";
$db_user = "root";
$db_pass = "";

// Create connection
$conn = new mysqli($host, $db_user, $db_pass, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
