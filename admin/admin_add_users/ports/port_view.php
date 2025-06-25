<?php
session_start();
header("Content-Type: application/json");

$conn = new mysqli("127.0.0.1", "root", "", "ditto", 3307);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}


$allPorts = [];
$result2 = $conn->query("SELECT id, port_name, country, port_code, port_india_agent, port_country_agent, status,port_head, port_contact from ports");
while ($row = $result2->fetch_assoc()) {
    $allPorts[] = $row;
}

echo json_encode([
    "allPorts" => $allPorts
]);

$conn->close();
?>
