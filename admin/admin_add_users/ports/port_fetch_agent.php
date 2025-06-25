<?php
header('Content-Type: application/json');

$host = "127.0.0.1";
$port = 3307;
$dbname = "ditto";
$username = "root";
$password = "";

// Connect to MySQL
$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Accept GET request instead of POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request method']);
    echo json_encode(['method' => $_SERVER['REQUEST_METHOD']]);
    exit;
}

// Read country from query parameter
$country = $_GET['country'] ?? '';

if (!$country) {
    echo json_encode(['error' => 'Country not provided']);
    exit;
}

// Prepare response arrays
$indiaAgents = [];
$foreignAgents = [];

// First, try to fetch agent and overseas agent assigned for this country
$sql = "
    SELECT u_agent.id AS agent_id, u_agent.username AS agent_name,
           u_over.id AS overseas_id, u_over.username AS overseas_name
    FROM country_assignments ca
    JOIN ditto.user u_agent ON u_agent.id = ca.agent_id AND u_agent.type = 'operations'
    JOIN ditto.user u_over ON u_over.id = ca.overseas_id AND u_over.type = 'operations'
    WHERE ca.country = ?
    LIMIT 5
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $country);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $agentIds = [];
    $overseasIds = [];
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['agent_id'], $agentIds)) {
            $indiaAgents[] = ['id' => $row['agent_id'], 'username' => $row['agent_name']];
            $agentIds[] = $row['agent_id'];
        }
        if (!in_array($row['overseas_id'], $overseasIds)) {
            $foreignAgents[] = ['id' => $row['overseas_id'], 'username' => $row['overseas_name']];
            $overseasIds[] = $row['overseas_id'];
        }
    }
} else {
    // If no assigned agents, fetch all agents and overseas agents of type 'operations'
    $sql_agents = "SELECT id, username FROM ditto.user WHERE type='operations' AND username LIKE 'age%'";
    $sql_overseas = "SELECT id, username FROM ditto.user WHERE type='operations' AND username LIKE 'ovs%'";

    $res_agents = $conn->query($sql_agents);
    while ($row = $res_agents->fetch_assoc()) {
        $indiaAgents[] = ['id' => $row['id'], 'username' => $row['username']];
    }

    $res_overseas = $conn->query($sql_overseas);
    while ($row = $res_overseas->fetch_assoc()) {
        $foreignAgents[] = ['id' => $row['id'], 'username' => $row['username']];
    }
}
echo json_encode([
    'indiaAgents' => $indiaAgents,
    'foreignAgents' => $foreignAgents
]);

$conn->close();



