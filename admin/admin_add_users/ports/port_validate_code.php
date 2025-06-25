<?php
$host = "127.0.0.1";
$port = 3307;
$dbname = "ditto";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = isset($_GET['portcode']) ? trim($_GET['portcode']) : '';
$current = isset($_GET['currentportcode']) ? trim($_GET['currentportcode']) : '';


if (!empty($user)) {
    $stmt = $conn->prepare("SELECT id FROM ports WHERE port_code = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();

    if ($user === $current) {
        echo "same";
    } else if ($stmt->num_rows > 0) {
        echo "taken";
    } else {
        echo "available";
    }

    $stmt->close();
    $conn->close();
    exit; // Important to stop here
}
$conn->close();
?>