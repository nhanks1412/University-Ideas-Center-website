<?php
session_start();
require 'db_conn.php';

// 1. Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Check submitted data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bio'])) {
    
    // Get content and trim excess whitespace
    $bio = trim($_POST['bio']);

    // 3. Check length (Max 1000 characters)
    // mb_strlen is used to count accented characters accurately
    if (mb_strlen($bio) > 1000) {
        // If too long, return to profile and report error
        header("Location: profile.php?error=BioTooLong");
        exit();
    }

    try {
        // 4. Update Database
        $stmt = $conn->prepare("UPDATE Users SET bio = ? WHERE user_id = ?");
        $stmt->execute([$bio, $user_id]);

        // Success -> Return to profile
        header("Location: profile.php?status=bio_updated");
        exit();

    } catch (Exception $e) {
        die("Database Error: " . $e->getMessage());
    }

} else {
    header("Location: profile.php");
    exit();
}
?>