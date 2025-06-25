<?php
session_start();

$host = "127.0.0.1";
$port = 3307;
$dbname = "ditto";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    session_regenerate_id(true);
    // Decode JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    
    $user = $data['USERNAME'] ?? '';
    $pass = $data['PASSWORD'] ?? '';
    $role = $data['ROLE'] ?? '';

    $ip = $_SERVER['REMOTE_ADDR'];
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $location = getLocationFromIP($ip);
    $session = session_id();
    $status = 1;

    // Parse browser, OS, device
    [$browser, $os, $device] = parseUserAgent($agent);

    if (!empty($user) && !empty($pass) && $role !== 'NONE') {
        $stmt = $conn->prepare("SELECT id, disabled_sections FROM user WHERE username = ? AND password = ? AND role = ? AND status = ?");
        $stmt->bind_param("sssi", $user, $pass, $role, $status);
        $stmt->execute();
        $stmt->bind_result($userId, $disabledSections);
        $stmt->fetch();
        $stmt->close();


        if ($userId) {
            // Store in session
            $_SESSION['username'] = $user;
            $_SESSION['role'] = $role;
            $_SESSION['login_status'] = $session;
            $sectionsArray = json_decode($disabledSections, true);
            $_SESSION['disabled_sections'] = $sectionsArray;


            // Track login session
            $stmtlog = $conn->prepare("UPDATE user SET login_status=? WHERE username = ? AND password = ? AND role = ?");
            $stmtlog->bind_param("ssss", $session, $user, $pass, $role);
            $stmtlog->execute();

            // Log user login
            $action = 'LOGIN';
            $current = 1;
            $stmt1 = $conn->prepare("INSERT INTO user_logins 
        (actions, u_id, username, role, ip_address, user_agent, session_id, location, browser, os, device_type, current) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt1->bind_param("sisssssssssi", $action, $userId, $user, $role, $ip, $agent, $session, $location, $browser, $os, $device, $current);
            $stmt1->execute();
            $stmt1->close();

            // Then return it
            echo json_encode([
                "success" => true,
                "role" => $role,
                "username" => $user,
                "disabled_sections" => explode(',', $disabledSections)
            ]);
            exit();
        } else {
            header("Location: login.html?error=inactive");
            exit();
        }


    } else {
        echo "Please fill in all fields.";
    }
}

function getLocationFromIP($ip)
{
    $json = @file_get_contents("http://ip-api.com/json/$ip?fields=country,regionName,city,status");
    if ($json === false)
        return "Unknown Location";

    $data = json_decode($json, true);
    return ($data['status'] === 'success')
        ? "{$data['city']}, {$data['regionName']}, {$data['country']}"
        : "MUMBAI MH INDIA";
}

function parseUserAgent($ua)
{
    $browser = "Unknown Browser";
    $os = "Unknown OS";
    $device = (strpos($ua, 'Mobile') !== false) ? 'Mobile' : 'Desktop';

    // Detect browser
    if (strpos($ua, 'Edge') !== false) {
        $browser = "Edge";
    } elseif (strpos($ua, 'Chrome') !== false) {
        $browser = "Chrome";
    } elseif (strpos($ua, 'Safari') !== false) {
        $browser = "Safari";
    } elseif (strpos($ua, 'Firefox') !== false) {
        $browser = "Firefox";
    } elseif (strpos($ua, 'MSIE') !== false || strpos($ua, 'Trident/') !== false) {
        $browser = "Internet Explorer";
    }

    // Detect OS
    if (preg_match('/Windows NT/', $ua)) {
        $os = "Windows";
    } elseif (preg_match('/Macintosh/', $ua)) {
        $os = "macOS";
    } elseif (preg_match('/Linux/', $ua)) {
        $os = "Linux";
    } elseif (preg_match('/Android/', $ua)) {
        $os = "Android";
    } elseif (preg_match('/iPhone|iPad/', $ua)) {
        $os = "iOS";
    }

    return [$browser, $os, $device];
}

$conn->close();
?>