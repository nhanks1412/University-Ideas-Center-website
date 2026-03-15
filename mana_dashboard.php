<?php
session_start();
require 'db_conn.php';

// Role ID 2 = QA Manager
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    die("Access denied! You are not a QA Manager.");
}

try {
    // 1. Get Categories list
    $categories = $conn->query("SELECT * FROM Categories ORDER BY category_id DESC")->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get Academic Years list (Events/Surveys)
    $academic_years = $conn->query("SELECT * FROM AcademicYears ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);

    // 3. Get Departments list (NEWLY ADDED)
    $departments = $conn->query("SELECT * FROM Departments ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 4. Get Ideas list (UPDATED: Added department info)
    $ideas = $conn->query("
        SELECT i.*, u.fullname, c.name as category_name, d.name as department_name, d.department_id
        FROM Ideas i
        JOIN Users u ON i.user_id = u.user_id
        JOIN Categories c ON i.category_id = c.category_id
        LEFT JOIN Departments d ON u.department_id = d.department_id
        ORDER BY i.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Get Users list for filtering
    $users_list = $conn->query("SELECT user_id, fullname, department_id FROM Users ORDER BY fullname ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 5. GET DATA FOR STATISTICAL CHARTS
    $deptStats = $conn->query("
        SELECT d.name, COUNT(i.idea_id) as idea_count 
        FROM Departments d 
        LEFT JOIN Ideas i ON d.department_id = i.department_id 
        GROUP BY d.department_id
    ")->fetchAll(PDO::FETCH_ASSOC);

    $dept_labels = [];
    $dept_data = [];
    foreach ($deptStats as $stat) {
        $dept_labels[] = $stat['name'];
        $dept_data[] = $stat['idea_count'];
    }

    $topIdeas = $conn->query("
        SELECT title, (upvotes + downvotes) as total_votes 
        FROM Ideas 
        ORDER BY total_votes DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    $idea_labels = [];
    $idea_data = [];
    foreach ($topIdeas as $idea) {
        $title = mb_strlen($idea['title']) > 20 ? mb_substr($idea['title'], 0, 20) . '...' : $idea['title'];
        $idea_labels[] = $title;
        $idea_data[] = $idea['total_votes'];
    }

} catch (Exception $e) {
    die("Error loading data: " . $e->getMessage());
}

require 'views/mana_dashboard_view.php';
?>