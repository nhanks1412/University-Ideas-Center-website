<?php
session_start();
require 'db_conn.php'; 

// 1. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_user_name = "User";
$avatar_path = null;
$current_role_id = 4; 

// 2. GET USER INFO
try {
    $stmtUser = $conn->prepare("SELECT fullname, avatar_path, role_id FROM Users WHERE user_id = ?");
    $stmtUser->execute([$user_id]);
    $currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($currentUser) {
        $avatar_path = $currentUser['avatar_path'];
        $current_user_name = $currentUser['fullname']; 
        $current_role_id = $currentUser['role_id']; 
    }
} catch (Exception $e) { /* Ignore */ }

$first_letter = strtoupper(substr($current_user_name, 0, 1));

// ==========================================================
// 3. HANDLE FILTERING BY GLOBAL ACADEMIC YEAR (NEW)
// ==========================================================
try {
    // Get the full list of academic years for the Dropdown Menu
    $all_global_years = $conn->query("SELECT * FROM GlobalYears ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    // Check which academic year the user wants to view
    $view_global_year_id = isset($_GET['global_year']) ? $_GET['global_year'] : null;
    $current_global_year = null;

    if ($view_global_year_id) {
        foreach ($all_global_years as $gy) {
            if ($gy['global_year_id'] == $view_global_year_id) {
                $current_global_year = $gy;
                break;
            }
        }
    }

    // If no GET request or invalid ID, get the Active academic year
    if (!$current_global_year) {
        foreach ($all_global_years as $gy) {
            if ($gy['is_active'] == 1) {
                $current_global_year = $gy;
                break;
            }
        }
    }

    // If still null (database has no years), create dummy data to prevent errors
    if (!$current_global_year) {
        $current_global_year = [
            'global_year_id' => 0,
            'year_name' => 'All Time',
            'start_date' => '2000-01-01',
            'end_date' => '2099-12-31',
            'is_active' => 0
        ];
    }

    // Assign variables to handle queries
    $current_global_year_id = $current_global_year['global_year_id'];
    $global_year_display = $current_global_year['year_name'];
    $g_start = $current_global_year['start_date'] . ' 00:00:00';
    $g_end = $current_global_year['end_date'] . ' 23:59:59';
    $is_viewing_active_year = ($current_global_year['is_active'] == 1);

    // Calculate the actual calendar year array (e.g., Academic year 25-26 will have array [2025, 2026])
    $start_y = (int)date('Y', strtotime($current_global_year['start_date']));
    $end_y = (int)date('Y', strtotime($current_global_year['end_date']));
    $valid_years = [];
    for ($y = $start_y; $y <= $end_y; $y++) {
        $valid_years[] = $y;
    }

} catch (Exception $e) {
    die("Error loading Academic Years: " . $e->getMessage());
}

// =======================================================
// 4. GET ACTIVE SURVEYS (EVENTS FROM QA MANAGER)
// =======================================================
try {
    $active_surveys = [];
    // Only display Events/Surveys when the user is VIEWING THE CURRENT ACADEMIC YEAR
    if ($is_viewing_active_year) {
        $today = date('Y-m-d');
        $stmtEvents = $conn->prepare("
            SELECT * FROM AcademicYears 
            WHERE start_date <= ? AND final_closure_date >= ? 
            ORDER BY closure_date ASC
        ");
        $stmtEvents->execute([$today, $today]);
        $active_surveys = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { 
    $active_surveys = []; 
}

// =======================================================
// 5. HANDLE PAGINATION & FETCH DATA
// =======================================================
try {
    $limit = 5; 
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    $current_tab = $_GET['tab'] ?? 'latest'; 
    
    // CONDITION: Only fetch posts WITHIN THE SELECTED ACADEMIC YEAR
    $where_sql = "i.is_hidden = 0 AND i.created_at BETWEEN ? AND ?"; 
    $params = [$g_start, $g_end];

    if ($current_tab == 'my_ideas') {
        $where_sql .= " AND i.user_id = ?";
        $params[] = $user_id;
    }

    if (!empty($_GET['filter_year'])) {
        $where_sql .= " AND YEAR(i.created_at) = ?";
        $params[] = $_GET['filter_year'];
    }
    if (!empty($_GET['filter_month'])) {
        $where_sql .= " AND MONTH(i.created_at) = ?";
        $params[] = $_GET['filter_month'];
    }

    $sqlCount = "SELECT COUNT(*) FROM Ideas i WHERE $where_sql";
    $stmtCount = $conn->prepare($sqlCount);
    $stmtCount->execute($params);
    $total_records = $stmtCount->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    $sql = "
        SELECT i.*, u.fullname, u.avatar_path as author_avatar, c.name as category_name, a.name as year_name
        FROM Ideas i
        JOIN Users u ON i.user_id = u.user_id
        JOIN Categories c ON i.category_id = c.category_id
        LEFT JOIN AcademicYears a ON i.academic_year_id = a.academic_year_id
        WHERE $where_sql
    ";

    if ($current_tab == 'popular') {
        $sql .= " ORDER BY i.upvotes DESC, i.created_at DESC";
    } else {
        $sql .= " ORDER BY i.created_at DESC";
    }

    $sql .= " LIMIT $limit OFFSET $offset";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $ideas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($ideas as &$idea) {
        $stmtDocs = $conn->prepare("SELECT * FROM Documents WHERE idea_id = ?");
        $stmtDocs->execute([$idea['idea_id']]);
        $idea['documents'] = $stmtDocs->fetchAll(PDO::FETCH_ASSOC); 
    }
    unset($idea);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// 6. CALL VIEW
require 'views/index_view.php'; 
?>