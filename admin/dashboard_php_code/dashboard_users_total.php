<?php
// ===============================================
// Script: add_user.php
// Purpose: TReturns count of no of agents, customer and admins in the database
// Features:
//   - Authentication Check:
//   - Database Connection
//   - Fetch Login Records
//   - Determine Last Login (Before Current):
//   - Add Today's Date
//   - Return as JSON
// Author: DITTO
// Created on: 17-06-2025
// LAST UPDATE:17-06-2025
// =============================================== 

header('Content-Type: application/json');
require_once '../auth_admin.php';
require_once 'db_connection.php'; 

if (!isset($conn)) {
    echo json_encode(["error" => "Database connection not found"]);
    exit();
}

$counts = [
  'admin' => 0,
  'agent' => 0,
  'customer' => 0
];

$query = "SELECT role, COUNT(*) as count FROM user GROUP BY role";
$result = mysqli_query($conn, $query);

if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $role = strtolower($row['role']);
    if (isset($counts[$role])) {
      $counts[$role] = (int)$row['count'];
    }
  }
  echo json_encode($counts);
} else {
  echo json_encode(["error" => "Query failed"]);
}
?>
