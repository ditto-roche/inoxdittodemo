<?php
// ===============================================
// Script: user_view.php
// called in:  $scope.loadUsers defined in scripts
// Purpose: Fetch a list of users based on admin level
// Features:
//   - Authenticates admin via session
//   - Retrieves user ID and type from session user
//   - level-1 admins can view only non-admin users
//   - level-0 (super admins) can view all users
//   - Returns user list as JSON
// Author: DITTO
// Created on: 20-06-2025
// ===============================================
//
// DATABASE:
//   - user --> provides all registered users' info
//
// ACCESS CONTROL:
//   - level-0 (super admin) --> Can view all users
//   - level-1 (admin)       --> Can view all users except role='admin'
//
// RESPONSE FORMAT:
//   JSON object containing:
//     - allUsers: array of user records
// ===============================================
require_once '../../auth_admin.php';
require_once '../../../universal/db_connection.php';
header("Content-Type: application/json");

$username = $_SESSION['username'] ?? '';
$adminId = null;

// Get current user's ID
$stmt = $conn->prepare("SELECT id, type FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($adminId, $type);
$stmt->fetch();
$stmt->close();

// Get users added by current admin

// Get all users
$allUsers = [];
if ($type === "level-1") {
    $result2 = $conn->query("SELECT id, name, username, email, phone,  role, type, status, createdt FROM user where role!='admin'");
    while ($row = $result2->fetch_assoc()) {
        $allUsers[] = $row;
    }
} else {
    $result2 = $conn->query("SELECT id, name, username, email, phone,  role, type, status, createdt FROM user");
    while ($row = $result2->fetch_assoc()) {
        $allUsers[] = $row;
    }
}


echo json_encode([
    "allUsers" => $allUsers
]);

$conn->close();
?>