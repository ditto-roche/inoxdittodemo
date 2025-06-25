<?php
$data = json_decode(file_get_contents("php://input"), true);

$country = $data['country'];
$agents = $data['agents'];

$conn = new mysqli("127.0.0.1", "root", "", "ditto", 3307);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO ports_countries (country, india_agent_id, foreign_agent_id) VALUES (?, ?, ?)");

foreach ($agents as $agentPair) {
    $stmt->bind_param("sii", $country, $agentPair['portindag'], $agentPair['portforag']);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo json_encode(["success" => true]);
?>
