<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Ditto/universal/db_connection.php';

// Check if session exists
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || !isset($_SESSION['login_status'])) {
    die("<script>alert('Session expired. Please login again.'); window.location.href='login.html';</script>");
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$alogin_status = $_SESSION['login_status'];

// Check login_status in database
$stmt = $conn->prepare("SELECT login_status FROM user WHERE username = ? AND role = ?");
$stmt->bind_param("ss", $username, $role);
$stmt->execute();
$stmt->bind_result($login_status);
$stmt->fetch();
$stmt->close();

if ($login_status !== $alogin_status) {
    session_destroy();
    die("<script>alert('You have been logged out. Please login again.'); window.location.href='../login.html';</script>");
}
?>
