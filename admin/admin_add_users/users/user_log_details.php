<?php
// ===============================================
// Script: user_log_details.php
// Purpose: Fetches the update logs (audit trail) of a specific user
// called in: $scope.loadLogs in scripts
// Features:
//   - Authenticates the admin via session
//   - Validates the requested user ID
//   - Retrieves logs from `updatelogtable` related to the user
//   - Returns user info and associated update logs as JSON
// Author: DITTO
// Created on: 20-06-2025
// ===============================================
//
// DATABASE:
//   - user --> Used for session auth and to fetch target user info
//   - updatelogtable --> Contains field-wise change history (audit logs)
//
// ACCESS CONTROL:
//   - level-0 (super admin) and level-1 (admin) --> Can view logs of any user
//
// RESPONSE FORMAT:
//   JSON object containing:
//     - user: { id, name }
//     - logs: array of log entries (if any)
//     - message: optional string if no logs exist
// ===============================================

require_once '../../auth_admin.php';
require_once '../../../universal/db_connection.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate request
if (empty($data['userid'])) {
    http_response_code(400);
    echo json_encode(["error" => "User ID not provided"]);
    exit();
}

$username = $_SESSION['username'] ?? '';
if (!$username) {
    http_response_code(401);
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

// Fetch admin ID and type
$stmt = $conn->prepare("SELECT id, type FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($adminId, $adminType);
if (!$stmt->fetch()) {
    echo json_encode(["error" => "Admin user not found"]);
    $stmt->close();
    exit();
}
$stmt->close();

// Get user info
$userId = (int) $data['userid'];
$stmtUser = $conn->prepare("SELECT id, name FROM user WHERE id = ?");
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();
$stmtUser->close();

if (!$user) {
    echo json_encode(["error" => "User not found"]);
    exit();
}

// Fetch logs
$stmtLogs = $conn->prepare("
    SELECT userid, operation, fieldname, oldvalue, newvalue, updatedt, updatedby, createdby
    FROM updatelogtable
    WHERE userid = ?
    ORDER BY updatedt DESC
");
$stmtLogs->bind_param("i", $userId);
$stmtLogs->execute();
$resultLogs = $stmtLogs->get_result();

$logs = [];
while ($row = $resultLogs->fetch_assoc()) {
    $logs[] = $row;
}

$actionType = 'fetch';
$entityType = 'users';
$field = "logs";
$oldValue = '0';
$newValue = '1';
$creatorId = '-';
$oldData = json_encode(['id' => $userId, 'log-fetch' => '0']);
$newData = json_encode(['id' => $userId, 'log-fetch' => '1']);

$stmtLogJson = $conn->prepare("
            INSERT INTO user_admin_log (user_id, action_type, entity_type, old_data, new_data, performed_by) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
$stmtLogJson->bind_param("isssss", $userId, $actionType, $entityType, $oldData, $newData, $adminId);
$stmtLogJson->execute();
$stmtLogJson->close();


$stmtLogs->close();

if (empty($logs)) {
    echo json_encode([
        "user" => ["id" => $user['id'], "name" => $user['name']],
        "logs" => [],
        "message" => "There are no updates for this user."
    ]);
    exit();
}


// Return logs
echo json_encode([
    "user" => ["id" => $user['id'], "name" => $user['name']],
    "logs" => $logs
]);
$conn->close();
