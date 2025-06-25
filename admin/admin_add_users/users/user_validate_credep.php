<?php
// ===============================================
// Script: user_validate_credep.php
// Purpose: Real-time validation for username, email, and phone during edit/registration
// Features:
//   - Checks if username is already taken
//   - Checks if email is already in use, unless it matches the current email
//   - Checks if phone is already in use, unless it matches the current phone
//   - Returns plain text response for each check
// Author: DITTO
// Created on: 20-06-2025
// ===============================================
//
// DATABASE:
//   - user --> queried for existence of username, email, phone
//
// USAGE (via AJAX GET):
//   - ?username=username123
//   - ?email=new@example.com&currentemail=old@example.com
//   - ?phone=9999999999&currentphone=8888888888
//
// RESPONSE FORMAT:
//   - For username: "taken" or "available"
//   - For email: "email_taken", "email_available", or "email_same"
//   - For phone: "phone_taken", "phone_available", or "phone_same"
// ===============================================

require_once '../../auth_admin.php';  
require_once '../../../universal/db_connection.php';

$username = isset($_GET['username']) ? trim($_GET['username']) : '';
$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$currentemail = isset($_GET['currentemail']) ? trim($_GET['currentemail']) : '';
$phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';
$currentphone = isset($_GET['currentphone']) ? trim($_GET['currentphone']) : '';

if(!empty($username)) {
    $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();


    if ($stmt->num_rows > 0) {
        echo "taken";
    } else {
        echo "available";
    }

    $stmt->close();
    $conn->close();
    exit; // Stop after response
}

if (!empty($email)) {
    $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($email === $currentemail) {
        echo "email_same";
    } else if ($stmt->num_rows > 0) {
        echo "email_taken";
    } else {
        echo "email_available";
    }

    $stmt->close();
    $conn->close();
    exit; // Stop after response
}

if (!empty($phone)) {
    $stmt = $conn->prepare("SELECT id FROM user WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($phone === $currentphone) {
        echo "phone_same";
    } else if ($stmt->num_rows > 0) {
        echo "phone_taken";
    } else {
        echo "phone_available";
    }

    $stmt->close();
    $conn->close();
    exit; // Stop after response
}

$conn->close();
?>