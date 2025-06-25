<?php
// ===============================================
// Script: add_user.php
// Purpose: This PHP script provides a JSON API endpoint that returns the recent login history of the currently logged-in user, specifically for admins.
// Features:
//   - Authentication Check:
//   - Database Connection
//   - Fetch Login Records
//   - Determine Last Login (Before Current):
//   - Add Today's Date
//   - Return as JSON
// Author: DITTO
// Created on: 18-06-2025
// LAST UPDATE:18-06-2025
// =============================================== 

header('Content-Type: application/json');
require_once '../auth_admin.php';
require_once 'db_connection.php';

$currentUser = $_SESSION['username'] ?? '';
$logDetails = [];
$lastLogin = '';
$lastplace = '';

if ($currentUser) {
    $stmt = $conn->prepare("SELECT ip_address, user_agent, login_time, location FROM user_logins WHERE username = ? ORDER BY login_time DESC LIMIT 3");
    $stmt->bind_param("s", $currentUser);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $logDetails[] = $row;
    }

    if (count($logDetails) > 1) {
        $lastLogin = $logDetails[1]['login_time'];
        $lastplace = $logDetails[1]['location'];
    } elseif (count($logDetails) === 1) {
        $lastLogin = $logDetails[0]['login_time'];
        $lastplace = $logDetails[0]['location'];
    }

    echo json_encode([
        'last_login' => $lastLogin,
        'last_place' => $lastplace,
        'logs' => $logDetails
    ]);
} else {
    echo json_encode(['error' => 'User not logged in']);
}

$conn->close();
?>

