<?php
// ===============================================
// Script: user_edit.php
// Purpose: Allows admin to update user details with audit logging
// called in: $scope.updateUser in scripts
// Features:
//   - Authenticates the admin session using session username
//   - Fetches existing user data for comparison
//   - Conditionally updates user fields (password update is optional)
//   - Logs modified fields into `updatelogtable` (field-wise tracking)
//   - Also logs full field-level JSON diffs into `user_admin_log`
//   - Returns JSON response with success or failure message
// Author: DITTO
// Created on: 20-06-2025
// ===============================================
//
// DATABASE:
//   - user --> updates fields like name, username, email, phone, password (if given), and status
//   - updatelogtable --> logs each modified field with before and after values
//   - user_admin_log --> stores JSON-formatted old/new values per field for audit
//
// SECURITY:
//   - Ensures only authenticated admins (via session) can update users
//   - Ensures user must exist before performing update
//
// UPDATE STRATEGY:
//   - If password is passed --> update password along with other fields
//   - If password is blank --> skip password update
//
// CHANGE LOGGING:
//   - Logs only the fields that changed (strict comparison)
//   - Logs to:
//       - updatelogtable (fieldname, oldvalue, newvalue, updatedby, createdby, etc.)
//       - user_admin_log (JSON snapshot of changed field)
//
// RESPONSE FORMAT:
//   JSON: { success: true } or { success: false, message: "..." }
// ===============================================


require_once '../../auth_admin.php';
require_once '../../../universal/db_connection.php';

// Set response type to JSON
header("Content-Type: application/json");

// Get data from the request body
$data = json_decode(file_get_contents("php://input"), true);

// Step 1: Get admin ID from session
$username = $_SESSION['username'] ?? '';
$adminId = null;

$stmt1 = $conn->prepare("SELECT id FROM user WHERE username = ?");
$stmt1->bind_param("s", $username);
$stmt1->execute();
$stmt1->bind_result($adminId);
$stmt1->fetch();
$stmt1->close();

if (!$adminId) {
    echo json_encode(["success" => false, "message" => "Invalid admin session"]);
    exit();
}

// Validate required user ID
if (empty($data['id'])) {
    echo json_encode(["success" => false, "message" => "User ID not provided"]);
    exit();
}

$userId = (int) $data['id'];

// Step 2: Fetch current user data
$stmtOld = $conn->prepare("SELECT name, username, email, phone, password, createby, status FROM user WHERE id = ?");
$stmtOld->bind_param("i", $userId);
$stmtOld->execute();
$resultOld = $stmtOld->get_result();
$oldData = $resultOld->fetch_assoc();
$stmtOld->close();

if (!$oldData) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit();
}

$creatorId = $oldData['createby'];

// Step 3: Extract updated fields safely
$name       = $data['name'] ?? '';
$usernameNew= $data['username'] ?? '';
$email      = $data['email'] ?? '';
$phone      = $data['phone'] ?? '';
$password   = $data['password'] ?? '';
$status     = isset($data['status']) ? (int)$data['status'] : 1;

// Step 4: Prepare update statement
if ($password !== '') {
    $stmtUpdate = $conn->prepare("UPDATE user SET name=?, username=?, email=?, phone=?, password=?, updateby=?, status=? WHERE id=?");
    $stmtUpdate->bind_param("ssssssii", $name, $usernameNew, $email, $phone, $password, $adminId, $status, $userId);
} else {
    $stmtUpdate = $conn->prepare("UPDATE user SET name=?, username=?, email=?, phone=?, updateby=?, status=? WHERE id=?");
    $stmtUpdate->bind_param("ssssiii", $name, $usernameNew, $email, $phone, $adminId, $status, $userId);
}

// Step 5: Execute update
$stmtUpdate->execute();

// Step 6: Compare and log changes
$fields = ['email', 'phone', 'status'];
if ($password !== '') {
    $fields[] = 'password';
}
$entity = 'users';
$operation = 'EDIT';
$actionType = 'edit';
$entityType = 'users';

foreach ($fields as $field) {
    $oldValue = $oldData[$field] ?? '';
    $newValue = $data[$field] ?? '';

    if ((string) $oldValue !== (string) $newValue) {
        // Log to updatelogtable
        $stmtLog = $conn->prepare("
            INSERT INTO updatelogtable 
                (userid, entity, operation, fieldname, oldvalue, newvalue, updatedby, createdby, updatedt) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmtLog->bind_param("isssssss", $userId, $entity, $operation, $field, $oldValue, $newValue, $adminId, $creatorId);
        $stmtLog->execute();
        $stmtLog->close();

        // Log to user_admin_log
        $logOldJson = json_encode(['id' => $userId, $field => $oldValue]);
        $logNewJson = json_encode(['id' => $userId, $field => $newValue]);

        $stmtLogJson = $conn->prepare("
            INSERT INTO user_admin_log (user_id, action_type, entity_type, old_data, new_data, performed_by) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtLogJson->bind_param("isssss", $userId, $actionType, $entityType, $logOldJson, $logNewJson, $adminId);
        $stmtLogJson->execute();
        $stmtLogJson->close();
    }
}

// Step 7: Return result
if ($stmtUpdate->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "User updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "No changes were made"]);
}

$stmtUpdate->close();
$conn->close();
?>
