<?php
session_start();
require 'db_conn.php';

// 1. Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Basic validation ---
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header("Location: profile.php?error=empty_fields"); exit();
    }

    // Check if new passwords match
    if ($new_password !== $confirm_password) {
        header("Location: profile.php?error=pass_mismatch"); exit();
    }

    // Check strength (Same as registration: 8 chars + Uppercase)
    if (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password)) {
        header("Location: profile.php?error=pass_weak"); exit();
    }

    try {
        // 3. Retrieve old password from Database for comparison
        $stmt = $conn->prepare("SELECT password_hash FROM Users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            die("No user found.");
        }

        // 4. Verify if current password is correct
        if (!password_verify($current_password, $user['password_hash'])) {
            header("Location: profile.php?error=wrong_curr_pass"); exit();
        }

        // 5. If all correct -> Hash new password and Update
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        $updateStmt = $conn->prepare("UPDATE Users SET password_hash = ? WHERE user_id = ?");
        $updateStmt->execute([$new_hash, $user_id]);

        // Success!
        header("Location: profile.php?status=pass_changed");
        exit();

    } catch (Exception $e) {
        die("Database Error: " . $e->getMessage());
    }

} else {
    header("Location: profile.php"); exit();
}
?>