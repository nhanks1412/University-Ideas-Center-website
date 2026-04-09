<?php
session_start();
require 'db_conn.php';

// 1. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];



// 4. CALL THE VIEW
require 'views/profile.php';
?>