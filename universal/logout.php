<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Set login_status = 0 for the user
    $updateQuery = "UPDATE user SET login_status = 0 WHERE username = ?";
    $stmt = $conn->prepare($updateQuery);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();

        // Optionally destroy session
        session_destroy();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
}
?>
