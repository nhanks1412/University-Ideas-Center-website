<?php
session_start();
require 'db_conn.php';

// Security: Only QA Coordinator (Role = 3) can access
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    die("🚫 Access denied! Only QA Coordinators can access this page.");
}

$qa_user_id = $_SESSION['user_id'];

try {
    $stmtDept = $conn->prepare("SELECT department_id FROM Users WHERE user_id = ?");
    $stmtDept->execute([$qa_user_id]);
    $qa_dept_id = $stmtDept->fetchColumn();

    // If this account has not been assigned to any department
    if (!$qa_dept_id) {
        die("⚠️ ERROR: Your QA Coordinator account has not been assigned to any department. Please ask the Admin to update your department!");
    }

    // 1. FETCH LIST OF USERS IN THE SAME DEPARTMENT
    $sqlMembers = "
        SELECT u.user_id, u.fullname, u.email, r.name as role_name,
        (SELECT COUNT(*) FROM Ideas i WHERE i.user_id = u.user_id) as total_posts
        FROM Users u
        LEFT JOIN Roles r ON u.role_id = r.role_id
        WHERE u.department_id = ?
        ORDER BY u.role_id ASC, u.fullname ASC
    ";
    $stmtMembers = $conn->prepare($sqlMembers);
    $stmtMembers->execute([$qa_dept_id]);
    $members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);

    // 2. FETCH LIST OF POSTS FROM THE DEPARTMENT
    $view_user_id = $_GET['view_user'] ?? null;
    $selected_user_name = "All members";

    if ($view_user_id) {
        $checkDept = $conn->prepare("SELECT department_id, fullname FROM Users WHERE user_id = ?");
        $checkDept->execute([$view_user_id]);
        $targetUser = $checkDept->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser || $targetUser['department_id'] != $qa_dept_id) {
            header("Location: qa_dashboard.php?error=" . urlencode("Error: This user does not belong to your department!"));
            exit();
        }

        $selected_user_name = $targetUser['fullname'];

        $sqlIdeas = "SELECT i.*, u.fullname FROM Ideas i JOIN Users u ON i.user_id = u.user_id WHERE i.user_id = ? ORDER BY i.created_at DESC";
        $stmtIdeas = $conn->prepare($sqlIdeas);
        $stmtIdeas->execute([$view_user_id]);
    } else {
        $sqlIdeas = "SELECT i.*, u.fullname FROM Ideas i JOIN Users u ON i.user_id = u.user_id WHERE u.department_id = ? ORDER BY i.created_at DESC";
        $stmtIdeas = $conn->prepare($sqlIdeas);
        $stmtIdeas->execute([$qa_dept_id]);
    }
    
    $ideas = $stmtIdeas->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch current department name
    $stmtDeptName = $conn->prepare("SELECT name FROM Departments WHERE department_id = ?");
    $stmtDeptName->execute([$qa_dept_id]);
    $dept_name = $stmtDeptName->fetchColumn();

} catch (Exception $e) {
    die("System error: " . $e->getMessage());
}

// Load View file
require 'views/qa_dashboard_view.php';
?>