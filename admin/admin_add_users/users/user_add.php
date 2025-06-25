<?php
// ===============================================
// Script: user_add.php
// called in: admin_add_users section add
// Purpose: Form that Handles the creation of new users by admin
// Features:
//   - Authenticates the admin using session
//   - Fetches admin's ID based on session username
//   - Validates required fields: name, username, password, role, email, phone
//   - Inserts the new user into the `user` table
//   - Logs the creation event in `user_admin_log`
//   - Redirects with success alert after insertion
// Author: DITTO
// Created on: 12-06-2025

// DATABASE: 
//   - user --> GET ID OF USER ADDING, ADD THE USER
//   - user_admin_log --> DATA STORED TO TRACK ADMIN ACTIVITY

// REDIRECTED USER TO:
// sessionStorage.setItem('showSection', 'add');
// sessionStorage.setItem('showSection1', 'users');
// ===============================================

require_once '../../auth_admin.php'; // Ensure admin is authenticated
require_once '../../../universal/db_connection.php'; // DB connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name     = trim($_POST['name'] ?? '');
    $user     = trim($_POST['username'] ?? '');
    $pass     = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? '';
    $type     = $_POST['type'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');

    $createdt = date('Y-m-d H:i:s');
    $update   = '';
    $updateby = '';
    $createby = null;

    $sessionUsername = $_SESSION['username'];

    // Fetch admin ID who is performing the add operation
    $stmtAdmin = $conn->prepare("SELECT id FROM user WHERE username = ?");
    $stmtAdmin->bind_param("s", $sessionUsername);
    $stmtAdmin->execute();
    $stmtAdmin->bind_result($adminId);
    if ($stmtAdmin->fetch()) {
        $createby = $adminId;
    }
    $stmtAdmin->close();

    // Check if required fields are filled
    if ($name && $user && $pass && $role && $email && $phone) {

        // Prepare insert statement for new user
        $stmt = $conn->prepare("INSERT INTO user (name, username, password, role, type, email, phone, createdt, createby, updatedt, updateby) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["error" => "Prepare failed: " . $conn-> error]);
            exit();
        }

        // Bind and execute the insert statement
        $stmt->bind_param(
            "sssssssssss",
            $name, $user, $pass, $role, $type,
            $email, $phone, $createdt, $createby,
            $update, $updateby
        );

        if ($stmt->execute()) {
            // Fetch the newly inserted user ID
            $stmtuser = $conn->prepare("SELECT id FROM user WHERE username = ?");
            $stmtuser->bind_param("s", $user);
            $stmtuser->execute();
            $stmtuser->bind_result($userId);
            $stmtuser->fetch();
            $stmtuser->close();

            // Prepare data for logging user creation
            $actionType = 'add';
            $entityType = 'users';
            $oldData    = '';
            $newValue   = json_encode([
                'id' => $userId,
                'status' => '1'
            ]);

            // Insert creation log into user_admin_log
            $stmtLog = $conn->prepare("INSERT INTO user_admin_log (user_id, action_type, entity_type, old_data, new_data, performed_by) 
                                       VALUES (?, ?, ?, ?, ?, ?)");

            if ($stmtLog) {
                $stmtLog->bind_param("isssss", $userId, $actionType, $entityType, $oldData, $newValue, $adminId);
                $stmtLog->execute();
                $stmtLog->close();
            }

            // Show success alert and redirect to admin panel with section remembered
            echo "<script>
                    alert('USER $userId added successfully.'); 
                    sessionStorage.setItem('showSection', 'add');
                    sessionStorage.setItem('showSection1', 'users');
                    window.location.href='../admin_add_users.php';
                </script>";

        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error adding user: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        // Required fields missing
        http_response_code(400);
        echo json_encode(["error" => "Please fill in all required fields."]);
    }
}

$conn->close(); // Close DB connection
?>
