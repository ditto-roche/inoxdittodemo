<?php
session_start();

// Log session start and user info
error_log("fetch_rates_simple.php accessed by user: " . ($_SESSION['username'] ?? 'unknown'));

// Restrict access to only logged-in customers
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'customer') {
    error_log("Unauthorized access attempt detected.");
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$host = "127.0.0.1";
$port = 3307;
$dbname = "ditto";
$db_user = "root";
$db_pass = "";

// Log DB connection attempt
error_log("Attempting DB connection to $host:$port/$dbname");

$conn = new mysqli($host, $db_user, $db_pass, $dbname, $port);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Get and sanitize input parameters from GET
$origin = isset($_GET['origin']) ? $conn->real_escape_string(trim($_GET['origin'])) : '';
$destination = isset($_GET['destination']) ? $conn->real_escape_string(trim($_GET['destination'])) : '';

// Log input parameters received
error_log("Received GET params: origin='$origin', destination='$destination'");

if (empty($origin) || empty($destination)) {
    error_log("Missing origin or destination.");
    echo json_encode(['error' => 'Both origin and destination are required']);
    exit();
}

// Log the query being executed (for debugging, consider sanitizing or removing in production)
$sql = "SELECT vessel_name, voyage, etd, eta, rate_usd, transit_time, remarks 
        FROM shipping_routes
        WHERE origin_port_code = '$origin' AND destination_port_code = '$destination'
        ORDER BY etd ASC
        LIMIT 100";

error_log("Executing query: $sql");

$result = $conn->query($sql);

if (!$result) {
    error_log("Query error: " . $conn->error);
    echo json_encode(['error' => 'Query error: ' . $conn->error]);
    exit();
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

// Log number of results found
error_log("Query returned " . count($rows) . " rows");

echo json_encode($rows);

$conn->close();
