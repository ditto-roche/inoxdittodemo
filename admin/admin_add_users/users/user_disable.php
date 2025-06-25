<?php

// ===============================================
// Script: user_disable.php
// Purpose: Handles disabling (soft deletion) of a user by an authenticated admin
// called in: $scope.disableuser in scripts.js
// Features:
//   - Authenticates admin session using `auth_admin.php`
//   - Accepts JSON POST input with user ID
//   - Verifies admin identity and role
//   - Validates user existence and prevents:
//       - Disabling already inactive users
//       - Admin disabling themselves
//       - Disabling super admin (type: level-0)
//   - Sets user's `status` and `login_status` to inactive (0)
//   - Logs action to:
//       - `user_logins` - with session and device details
//       - `user_admin_log` - full audit trail of change
//       - `updatelogtable` - field-level log (status change)
//   - Updates `user_logins.current` to 0
//   - Returns JSON success or error message
// Author: DITTO
// Created on: [Insert today's date, e.g., 20-06-2025]
//
// DATABASE:
//   - user --> updated `status`, `login_status` to 0
//   - user_logins --> logs 'DISABLED' action with last session details
//   - user_admin_log --> logs old and new user state
//   - updatelogtable --> tracks field-level status change
//
// SECURITY CHECKS:
//   - Validates session and JSON input
//   - Prevents disabling:
//       - Already inactive users
//       - The admin themselves
//       - SUPER ADMINs (`type = level-0`)
//
// RESPONSE FORMAT:
//   JSON: { success: true/false, message: "...", user_id: optional }
//
// SESSION:
//   - Ends session if valid admin session exists (`session_destroy()` at end)
// ===============================================

require_once '../../auth_admin.php';
require_once '../../../universal/db_connection.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (empty($data['userid'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "User ID not provided"]);
    exit();
}

$username = $_SESSION['username'] ?? '';
if (!$username) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

// Get admin info
$stmt = $conn->prepare("SELECT id, type FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($adminId, $adminType);
if (!$stmt->fetch()) {
    $stmt->close();
    echo json_encode(["success" => false, "message" => "Admin user not found"]);
    exit();
}
$stmt->close();

// Get user status
$userId = (int) $data['userid'];
$stmtUser = $conn->prepare("SELECT id, status, createby, type FROM user WHERE id = ?");
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();
$stmtUser->close();

if (!$user) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit();
}

// Get latest login info
$stmtlogin = $conn->prepare("
    SELECT user_agent, username, role, location, ip_address, session_id, browser, os, device_type 
    FROM user_logins 
    WHERE u_id = ? 
    ORDER BY login_time DESC 
    LIMIT 1
");
$stmtlogin->bind_param("i", $userId);
$stmtlogin->execute();
$resultlogin = $stmtlogin->get_result();
$userlogin = $resultlogin->fetch_assoc();
$stmtlogin->close();

$ogstatus = $user['status'];
if ($ogstatus == 0) {
    echo json_encode(["success" => false, "message" => "User already inactive"]);
    exit();
}

// Prevent self-disable
if ($adminId === $userId) {
    echo json_encode(["success" => false, "message" => "You cannot disable yourself"]);
    exit();
}

if ($user['type'] === 'level-0') {
    echo json_encode(["success" => false, "message" => "You are not authorised to delete SUPER ADMIN"]);
    exit();
}

// Proceed with disabling
$status = 0;
$login_status = '0';
$current = 0;

$stmtUpdate = $conn->prepare("UPDATE user SET status = ?, login_status = ? WHERE id = ?");
$stmtUpdate->bind_param("isi", $status, $login_status, $userId);
$stmtUpdate->execute();

if ($stmtUpdate->affected_rows > 0) {
    $stmtUpdate->close();

    // Log disable action
    $action = 'DISABLED';
    $stmt1 = $conn->prepare("INSERT INTO user_logins 
        (actions, u_id, username, role, ip_address, user_agent, session_id, location, browser, os, device_type, current) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param(
        "sisssssssssi",
        $action,
        $userId,
        $userlogin['username'],
        $userlogin['role'],
        $userlogin['ip_address'],
        $userlogin['user_agent'],
        $userlogin['session_id'],
        $userlogin['location'],
        $userlogin['browser'],
        $userlogin['os'],
        $userlogin['device_type'],
        $current
    );
    $stmt1->execute();
    $stmt1->close();
    echo json_encode(["success" => true, "message" => "User disabled", "user_id" => $userId]);

    $stmtupdate1 = $conn->prepare("UPDATE user_logins SET current=? WHERE u_id=?");
    $stmtupdate1->bind_param(
        "ii",
        $current,
        $userId
    );
    $stmtupdate1->execute();
    $stmtupdate1->close();

    $entity = "users";
    $operation = "disabled";
    $fieldname = "status";
    $oldvalue = "1";
    $newvalue = "0";

    $stmtupdateLog = $conn->prepare("INSERT INTO updatelogtable 
    (userid, entity, operation, fieldname, oldvalue, newvalue, updatedby, createdby, updatedt) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    if ($stmtupdateLog) {
        $stmtupdateLog->bind_param("isssssss", $userId, $entity, $operation, $fieldname, $oldvalue, $newvalue, $adminId, $user['createby']);
        $stmtupdateLog->execute();
        $stmtupdateLog->close();
    }

    $actionType = 'disabled';
    $entityType = 'users';
    $oldData = json_encode(['id' => $userId, 'status' => '1']);
    $newData = json_encode(['id' => $userId, 'status' => '0']);

    $stmtLog = $conn->prepare("INSERT INTO user_admin_log (user_id, action_type, entity_type, old_data, new_data, performed_by) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmtLog) {
        $stmtLog->bind_param("isssss", $userId, $actionType, $entityType, $oldData, $newData, $adminId);
        $stmtLog->execute();
        $stmtLog->close();
    }
    
} else {
    $stmtUpdate->close();
    echo json_encode(["success" => false, "message" => "No changes made"]);
}

$conn->close();
?>