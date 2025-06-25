<?php
// ===============================================
// Script: port_delete.php
// Purpose: Soft-deletes a port (sets status = 0) based on ID
// Features:
//   - Authenticates the admin session
//   - Retrieves and validates target port ID
//   - Soft deletes the port by updating its status
//   - Logs the delete action in both user_admin_log and updatelogtable
// Author: DITTO
// Created on: 20-06-2025
// ===============================================
//
// DATABASE:
//   - user                 --> retrieves admin ID using session username
//   - ports                --> target table; status field set to 0 for soft delete
//   - user_admin_log       --> logs delete action with before and after status
//   - updatelogtable       --> logs field-wise update history (audit trail)
//
// ACCESS CONTROL:
//   - Admin must be authenticated
//
// LOGGING:
//   - Logs delete action in both admin log and audit log (field change)
//
// RESPONSE:
//   - Alerts user on success or failure and redirects accordingly
// ===============================================
require_once '../../auth_admin.php';
require_once '../../../universal/db_connection.php';

if (empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "User ID not provided"]);
    exit();
}

$userId = (int) $_GET['id'];
$adminId = null;
$createdId = null;

$sessionUsername=$_SESSION['username'];

$stmtAdmin = $conn->prepare("SELECT id FROM user WHERE username = ?");
$stmtAdmin->bind_param("s", $sessionUsername);
$stmtAdmin->execute();
$stmtAdmin->bind_result($adminId);
$stmtAdmin->fetch();
$stmtAdmin->close();

if (!$adminId) {
    http_response_code(403);
    echo json_encode(["error" => "Admin user not found"]);
    $conn->close();
    exit();
}

// Fetch creator of the user
$stmtOld = $conn->prepare("SELECT created_by FROM ports WHERE id = ?");
$stmtOld->bind_param("i", $userId);
$stmtOld->execute();
$stmtOld->bind_result($createdId);
$stmtOld->fetch();  // ✅ this was missing!
$stmtOld->close();

$stmt = $conn->prepare("UPDATE ports SET `status` = 0 WHERE id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    echo "<script>
        alert('Port $userId deleted successfully.'); 
        sessionStorage.setItem('showSection', 'viewports');
        window.location.href='../../admin_add_users.php';
    </script>";
} else {
    echo "<script>alert('ERROR DELETING USER');</script>";
}

// Log the delete action - admin activity
$actionType = 'delete';
$entityType = 'ports';
$oldData = json_encode(['id' => $userId, 'status' => '1']);
$newData = json_encode(['id' => $userId, 'status' => '0']);

$stmtLog = $conn->prepare("INSERT INTO user_admin_log (user_id, action_type, entity_type, old_data, new_data, performed_by) 
                           VALUES (?, ?, ?, ?, ?, ?)");
if ($stmtLog) {
    $stmtLog->bind_param("isssss", $userId, $actionType, $entityType, $oldData, $newData, $adminId);
    $stmtLog->execute();
    $stmtLog->close();
}

// Log update in updatelogtable
$entity="ports";
$operation = "DELETE";
$fieldname = "status";
$oldvalue = "1";
$newvalue = "0";  

$stmtupdateLog = $conn->prepare("INSERT INTO updatelogtable 
    (userid, entity, operation, fieldname, oldvalue, newvalue, updatedby, createdby, updatedt) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

if ($stmtupdateLog) {
    $stmtupdateLog->bind_param("isssssss", $userId, $entity, $operation, $fieldname, $oldvalue, $newvalue, $adminId, $createdId);
    $stmtupdateLog->execute();
    $stmtupdateLog->close();
}

$stmt->close();
$conn->close();

?>
