<?php
session_start();
require 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    die("Access denied!");
}

$qa_user_id = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

try {
    $stmtDept = $conn->prepare("SELECT department_id FROM Users WHERE user_id = ?");
    $stmtDept->execute([$qa_user_id]);
    $qa_dept_id = $stmtDept->fetchColumn();

    if (!$qa_dept_id) {
        die("System error: Account has no assigned department.");
    }

    // --- HIDE / UNHIDE POST ---
    if ($action == 'toggle_hide_idea') {
        $idea_id = $_GET['id'];
        
        $stmtCheck = $conn->prepare("SELECT u.department_id, i.is_hidden FROM Ideas i JOIN Users u ON i.user_id = u.user_id WHERE i.idea_id = ?");
        $stmtCheck->execute([$idea_id]);
        $idea = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if (!$idea || $idea['department_id'] != $qa_dept_id) {
            header("Location: qa_dashboard.php?tab=posts&error=" . urlencode("Action denied: This post does not belong to your department!")); 
            exit();
        }

        $new_status = $idea['is_hidden'] ? 0 : 1;
        $conn->prepare("UPDATE Ideas SET is_hidden = ? WHERE idea_id = ?")->execute([$new_status, $idea_id]);
        
        $msg = $new_status ? "Post successfully HIDDEN!" : "Post successfully UNHIDDEN!";
        header("Location: qa_dashboard.php?tab=posts&msg=" . urlencode($msg));
    }
    
    // --- PERMANENTLY DELETE POST ---
    elseif ($action == 'delete_idea') {
        $idea_id = $_GET['id'];
        
        $stmtCheck = $conn->prepare("SELECT u.department_id FROM Ideas i JOIN Users u ON i.user_id = u.user_id WHERE i.idea_id = ?");
        $stmtCheck->execute([$idea_id]);
        $target_dept = $stmtCheck->fetchColumn();
        
        if ($target_dept != $qa_dept_id) {
            header("Location: qa_dashboard.php?tab=posts&error=" . urlencode("Action denied: This post does not belong to your department!")); 
            exit();
        }

        $conn->prepare("DELETE FROM Ideas WHERE idea_id = ?")->execute([$idea_id]);
        header("Location: qa_dashboard.php?tab=posts&msg=" . urlencode("Post permanently deleted!"));
    }

} catch (Exception $e) {
    header("Location: qa_dashboard.php?tab=posts&error=" . urlencode($e->getMessage()));
}
?>