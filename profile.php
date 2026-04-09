<?php
session_start();
require 'db_conn.php';

// 1. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 2. GET FULL USER INFORMATION
    // Join with Roles and Departments tables to get display names
    // Sub-query to count the number of contributed Ideas
    $sql = "
        SELECT u.*, 
               r.name as role_name, 
               d.name as department_name,
               (SELECT COUNT(*) FROM Ideas WHERE user_id = u.user_id) as idea_count
        FROM Users u
        LEFT JOIN Roles r ON u.role_id = r.role_id
        LEFT JOIN Departments d ON u.department_id = d.department_id
        WHERE u.user_id = ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Error: User information not found!");
    }

    // 3. HANDLE DISPLAY LOGIC (Prepare data for View)
    
    // First letter (used when no avatar is available)
    $first_letter = strtoupper(substr($user['fullname'], 0, 1));

    // Handle Background (Cover photo)
    // Default is Blue Gradient
    $bg_style = "background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);";
    
    // If there is an actual cover photo in DB and file exists -> Use that image
    if (!empty($user['cover_path']) && file_exists($user['cover_path'])) {
        $bg_style = "background-image: url('" . htmlspecialchars($user['cover_path']) . "'); background-size: cover; background-position: center;";
    }

} catch (Exception $e) {
    die("System Error: " . $e->getMessage());
}

// 4. CALL THE VIEW
require 'views/profile.php';
?>