<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

$conn = new mysqli("127.0.0.1", "root", "", "ditto", 3307);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}

$sql = "SELECT DISTINCT TRIM(country) AS country FROM country_assignments WHERE country IS NOT NULL AND country != '' AND status = 0 ORDER BY country ASC";
$result = $conn->query($sql);

$countries = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $countries[] = $row['country'];
    }
    echo json_encode($countries);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Query failed."]);
}

$conn->close();
?>
