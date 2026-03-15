<?php
session_start();
require 'db_conn.php';

// Role ID 1 = Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die("Access denied! Admins only.");
}

try {
    $users = $conn->query("SELECT u.*, r.name as role_name, d.name as dept_name FROM Users u LEFT JOIN Roles r ON u.role_id = r.role_id LEFT JOIN Departments d ON u.department_id = d.department_id ORDER BY u.user_id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $roles = $conn->query("SELECT * FROM Roles")->fetchAll(PDO::FETCH_ASSOC);
    $depts = $conn->query("SELECT * FROM Departments")->fetchAll(PDO::FETCH_ASSOC);

    $stmtTerms = $conn->query("SELECT setting_value FROM Settings WHERE setting_key = 'terms_content'");
    $current_terms = $stmtTerms->fetchColumn() ?: '';

    // 1. MANAGE GLOBAL YEARS & FILTER STATISTICS

    // Retrieve the complete Global Years list
    $global_years = $conn->query("SELECT * FROM GlobalYears ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
    
   // Identify the currently ACTIVATED school year (to display in the Index)
    $active_year = null;
    foreach ($global_years as $gy) {
        if ($gy['is_active'] == 1) {
            $active_year = $gy;
            break;
        }
    }
    // Specify the school year you want to VIEW STATISTICS for (Default is the year currently active)
    $view_year_id = isset($_GET['view_year']) ? $_GET['view_year'] : ($active_year['global_year_id'] ?? null);
    
    $current_view_year = null;
    if ($view_year_id) {
        $stmtView = $conn->prepare("SELECT * FROM GlobalYears WHERE global_year_id = ?");
        $stmtView->execute([$view_year_id]);
        $current_view_year = $stmtView->fetch(PDO::FETCH_ASSOC);
    }
    
    // If no data is available, use temporary data
    if (!$current_view_year) {
        $current_view_year = ['year_name' => 'No Data', 'start_date' => date('Y-01-01'), 'end_date' => date('Y-12-31')];
    }

    $g_start = $current_view_year['start_date'];
    $g_end = $current_view_year['end_date'];

    // 2. CALCULATE CHART DATA BASED ON THE CURRENT SCHOOL YEAR
    
    $stmtTotal = $conn->prepare("SELECT COUNT(*) FROM Ideas WHERE created_at BETWEEN ? AND ?");
    $stmtTotal->execute([$g_start . ' 00:00:00', $g_end . ' 23:59:59']);
    $total_ideas_in_year = $stmtTotal->fetchColumn();

    $stmtDept = $conn->prepare("
        SELECT d.name, COUNT(i.idea_id) as count 
        FROM Departments d 
        LEFT JOIN Ideas i ON d.department_id = i.department_id AND i.created_at BETWEEN ? AND ?
        GROUP BY d.department_id
    ");
    $stmtDept->execute([$g_start . ' 00:00:00', $g_end . ' 23:59:59']);
    $deptStats = $stmtDept->fetchAll(PDO::FETCH_ASSOC);
    
    $chart_dept_labels = []; $chart_dept_data = [];
    foreach ($deptStats as $row) {
        $chart_dept_labels[] = $row['name'];
        $chart_dept_data[] = $row['count'];
    }

    $stmtTime = $conn->prepare("
        SELECT DATE(created_at) as submit_date, COUNT(idea_id) as count 
        FROM Ideas 
        WHERE created_at BETWEEN ? AND ?
        GROUP BY DATE(created_at) 
        ORDER BY submit_date ASC
    ");
    $stmtTime->execute([$g_start . ' 00:00:00', $g_end . ' 23:59:59']);
    $timeStats = $stmtTime->fetchAll(PDO::FETCH_ASSOC);

    $chart_time_labels = []; $chart_time_data = [];
    foreach ($timeStats as $row) {
        $chart_time_labels[] = date("d/m/Y", strtotime($row['submit_date']));
        $chart_time_data[] = $row['count'];
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

require 'views/admin_dashboard_view.php';
?>