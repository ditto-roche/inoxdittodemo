<?php
// ===============================================
// Script: user_view_login.php
// Purpose: Retrieves login history of all users
// called in: $scope.loadhistory in scripts
// Features:
//   - Authenticates admin session
//   - Optionally fetches admin ID (not used in current logic)
//   - Retrieves login records from user_logins table
//   - Returns records in JSON format, sorted by latest login time
// Author: DITTO
// Created on: 20-06-2025
// ===============================================
//
// DATABASE:
//   - user         --> used to get admin ID (if needed in future)
//   - user_logins  --> stores login details (username, IP, device info, etc.)
//
// RESPONSE FORMAT:
//   JSON object containing:
//     - alllogin: array of login history entries
//     - or error object in case of failure
// ===============================================
require_once '../../auth_admin.php';  
require_once '../../../universal/db_connection.php';
header("Content-Type: application/json");

// Get the logged-in admin's username
$username = $_SESSION['username'] ?? '';
$adminId = null;

// Get admin ID if needed (optional)
if ($username) {
    $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($adminId);
    $stmt->fetch();
    $stmt->close();
}

// Fetch login history
$alllogin = [];
$query = "SELECT U_id, actions, username, role, login_time, ip_address, session_id, location, browser, os, device_type, current, duration
          FROM user_logins ORDER BY login_time DESC"; // Order by latest

$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $alllogin[] = $row;
    }
} else {
    echo json_encode([
        "error" => true,
        "message" => "Failed to fetch login data.",
        "sql_error" => $conn->error
    ]);
    exit();
}

// Output result
echo json_encode([
    "alllogin" => $alllogin
]);

$conn->close();
?>
