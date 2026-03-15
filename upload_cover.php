<?php
session_start();
require 'db_conn.php';

// 1. Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); exit();
}

$user_id = $_SESSION['user_id'];

// 2. Check submitted file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cover_file'])) {
    $file = $_FILES['cover_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Upload error. Error code: " . $file['error']);
    }

    // 3. Validate image
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_info = getimagesize($file['tmp_name']);
    
    if (!$file_info || !in_array($file_info['mime'], $allowed_types)) {
        die("Error: Only image files (JPG, PNG, GIF, WEBP) can be uploaded.");
    }
    
    // Limit 10MB for cover photo (since cover photos are usually large)
    if ($file['size'] > 10 * 1024 * 1024) {
        die("Error: Image is too large! Please select an image under 10MB.");
    }

    // 4. Create filename & Directory
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'cover_' . $user_id . '_' . time() . '.' . $ext;
    
    $target_dir = 'uploads/covers/';
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $target_file = $target_dir . $new_filename;

    // 5. Move & Update Database
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        try {
            // Delete old cover photo to clean up
            $stmt = $conn->prepare("SELECT cover_path FROM Users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $old_cover = $stmt->fetchColumn();
            
            if ($old_cover && file_exists($old_cover)) { unlink($old_cover); }

            // Save new path
            $updateStmt = $conn->prepare("UPDATE Users SET cover_path = ? WHERE user_id = ?");
            $updateStmt->execute([$target_file, $user_id]);

            header("Location: profile.php?status=cover_updated");
            exit();

        } catch (Exception $e) {
            unlink($target_file);
            die("Database Error: " . $e->getMessage());
        }
    } else {
        die("File moving error.");
    }
} else {
    header("Location: profile.php"); exit();
}
?>