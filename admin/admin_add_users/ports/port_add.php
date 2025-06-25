<?php
require_once '../../auth_admin.php';
require_once '../../../universal/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $portName = trim($_POST['portname'] ?? '');
    $country = trim($_POST['selectedCountry'] ?? '');
    $portCode = trim($_POST['portcode'] ?? '');
    $portHead = trim($_POST['porthead'] ?? '');
    $portContact = trim($_POST['portcontact'] ?? '');
    $portIndiaAgent = $_POST['portindia'] ?? null;
    $portForeignAgent = $_POST['portforiegn'] ?? null;
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

    // Basic validation
    if ($portName && $country && $portCode && $portIndiaAgent && $portForeignAgent) {
        $stmt = $conn->prepare("INSERT INTO ports (
            port_name, country, port_code, port_head, port_contact,
            port_india_agent, port_country_agent, created_dt, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssss",
            $portName,
            $country,
            $portCode,
            $portHead,
            $portContact,
            $portIndiaAgent,
            $portForeignAgent,
            $createdt,
            $createby
        );

        if ($stmt->execute()) {
            $stmtuser = $conn->prepare("SELECT id FROM ports WHERE port_name = ?");
            $stmtuser->bind_param("s", $portName);
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
                    alert('PORT added successfully.'); 
                    sessionStorage.setItem('showSection', 'addports');
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