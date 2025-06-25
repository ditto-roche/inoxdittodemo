<?php
header('Content-Type: application/json');

$host = "127.0.0.1";
$port = 3307;
$dbname = "ditto";
$username = "root";
$password = "";

// Get the search query
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// Connect to MySQL
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

// Escape user input for safety
$search = $conn->real_escape_string($q);
$sql = "
    SELECT port_name, country, port_code 
    FROM ports 
    WHERE port_name LIKE '%$search%' 
       OR country LIKE '%$search%' 
       OR port_code LIKE '%$search%' 
    LIMIT 10
";

$result = $conn->query($sql);

$ports = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ports[] = $row;
    }
}

echo json_encode($ports);
$conn->close();
