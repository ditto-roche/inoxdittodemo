<?php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    echo json_encode([
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'],
        'disabled_sections' => $_SESSION['disabled_sections'] ?? []
    ]);
} else {
    echo json_encode(['error' => 'No session found']);
}
?>
