<?php
require_once '../../auth_admin.php';
require_once '../../../universal/db_connection.php';

header("Content-Type: application/json");

$query = "SELECT id, username, login_time FROM user_logins WHERE current = 1";
$result = $conn->query($query);

$data = [];

$updateQuery = "UPDATE user_logins SET duration = ? WHERE id = ?";
$stmt = $conn->prepare($updateQuery);

if (!$stmt) {
    // Return JSON error instead of crashing
    echo json_encode(["error" => "Failed to prepare statement"]);
    http_response_code(500);
    exit;
}

while ($row = $result->fetch_assoc()) {
    $login_time = strtotime($row['login_time']);
    $now = time();
    $duration = $now - $login_time;

    $hours = floor($duration / 3600);
    $minutes = floor(($duration % 3600) / 60);
    $seconds = $duration % 60;

    $formatted = "{$hours}h {$minutes}m {$seconds}s";

    $stmt->bind_param("si", $formatted, $row['id']);
    $stmt->execute();

    $data[] = [
        "id" => $row['id'],
        "username" => $row['username'],
        "duration" => $formatted
    ];
}

echo json_encode(["sessions" => $data]);
$conn->close();
