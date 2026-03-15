<?php
session_start();
require 'db_conn.php'; // Database connection
// 1. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_academic_year = null;

try {
    // 2. GET CURRENT OPEN ACADEMIC YEAR (To display notification banner)
    // Logic: Get the one with closure_date greater than current time, sort by closest
    $stmtYear = $conn->prepare("SELECT * FROM AcademicYears WHERE closure_date > NOW() ORDER BY closure_date ASC LIMIT 1");
    $stmtYear->execute();
    $current_academic_year = $stmtYear->fetch(PDO::FETCH_ASSOC);

    // 3. GET CATEGORY LIST (To show in Select box)
    $categories = $conn->query("SELECT * FROM Categories")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("System error: " . $e->getMessage());
}

// 4. CALL THE VIEW
// Variables $current_academic_year and $categories will be automatically passed to the View file
require 'views/create_idea.php';
?>