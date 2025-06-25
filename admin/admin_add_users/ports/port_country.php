<?php
// ===============================================
// Script: port_country.php
// Purpose: Assign India and overseas agents to a selected country
// Features:
//   - Authenticates admin session
//   - Updates `country_assignments` table with selected agents
//   - Logs the assignment to `user_admin_log`
//   - Redirects with success/failure alert
// Author: DITTO
// Created on: 20-06-2025
// ===============================================
//
// DATABASE:
//   - user                 --> used to fetch current admin ID
//   - country_assignments  --> stores agent mappings for countries
//   - user_admin_log       --> logs update actions with before/after values
//
// ===============================================

require_once '../../auth_admin.php';
require_once '../../../universal/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $country = trim($_POST['selectednonCountry'] ?? '');
    $portIndiaAgent = $_POST['portindiacountry'] ?? null;
    $portForeignAgent = $_POST['portforiegncountry'] ?? null;
    $status = 1;
    $createdt = date('Y-m-d H:i:s');
    $createby = null;

    $sessionUsername = $_SESSION['username'];

    $stmtAdmin = $conn->prepare("SELECT id FROM user WHERE username = ?");
    $stmtAdmin->bind_param("s", $sessionUsername);
    $stmtAdmin->execute();
    $stmtAdmin->bind_result($adminId);
    if ($stmtAdmin->fetch()) {
        $createby = $adminId;
    }
    $stmtAdmin->close();

    if ($country && $portIndiaAgent && $portForeignAgent) {
        $stmt = $conn->prepare("UPDATE country_assignments 
    SET agent_id = ?, overseas_id = ?, status = ?, created_on = ?, created_by = ? 
    WHERE country = ?");

        $stmt->bind_param(
            "iiisss",
            $portIndiaAgent,
            $portForeignAgent,
            $status,
            $createdt,
            $createby,
            $country
        );


        if ($stmt->execute()) {
            $stmtuser = $conn->prepare("SELECT id FROM country_assignments WHERE country = ?");
            $stmtuser->bind_param("s", $country);
            $stmtuser->execute();
            $stmtuser->bind_result($userId);
            $stmtuser->fetch();
            $stmtuser->close();

            // Log user creation
            $actionType = 'add';
            $entityType = 'ports';
            $oldData = '';
            $newValue = json_encode([
                'id' => $userId,
                'status' => '1'
            ]);

            $stmtLog = $conn->prepare("INSERT INTO user_admin_log (user_id, action_type, entity_type, old_data, new_data, performed_by) 
                                       VALUES (?, ?, ?, ?, ?, ?)");

            if ($stmtLog) {
                $stmtLog->bind_param("isssss", $userId, $actionType, $entityType, $oldData, $newValue, $adminId);
                $stmtLog->execute();
                $stmtLog->close();
            }
            echo "<script>
                    alert('Country added successfully.'); 
                    sessionStorage.setItem('showSection', 'addcountries'');
                    sessionStorage.setItem('showSection1', 'ports');
                    window.location.href='../../admin_add_users.php';
                </script>";
            exit();
        } else {
            error_log("Error executing statement: " . $stmt->error);
            echo "<script>alert('Error adding port: " . addslashes($stmt->error) . "');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Please fill in all required fields.');</script>";
    }
}

$conn->close();
?>