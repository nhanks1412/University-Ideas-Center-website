<?php
session_start();
require 'db_conn.php';

// 1. Allow only logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); exit();
}

$user_id = $_SESSION['user_id'];

// 2. Check if a file was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar_file'])) {
    $file = $_FILES['avatar_file'];

    // Check basic upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("File upload error. Error code: " . $file['error']);
    }

    // 3. Validate image format (Important security!)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_info = getimagesize($file['tmp_name']); // Check if it's a real image
    
    if (!$file_info || !in_array($file_info['mime'], $allowed_types)) {
        die("Error: Only image files (JPG, PNG, GIF, WEBP) are allowed to be uploaded.");
    }
    
    // Check file size (Example: 5MB limit)
    if ($file['size'] > 5 * 1024 * 1024) {
        die("Error: Image file is too large! Please select an image under 5MB.");
    }

    // 4. Generate new filename (Unique to avoid conflicts)
    // Filename will be: avatar_UserID_Time.extension
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
    $target_dir = 'uploads/avatars/';
    $target_file = $target_dir . $new_filename;

    // 5. Move file from temp folder to destination folder
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        
        // 6. If move successful -> Update Database
        try {
            // Get old image path to delete (server cleanup)
            $stmt = $conn->prepare("SELECT avatar_path FROM Users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $old_avatar = $stmt->fetchColumn();
            if ($old_avatar && file_exists($old_avatar)) {
                unlink($old_avatar); // Delete old image
            }

            // Update new path
            $updateStmt = $conn->prepare("UPDATE Users SET avatar_path = ? WHERE user_id = ?");
            $updateStmt->execute([$target_file, $user_id]);

            // Success! Return to profile page
            header("Location: profile.php?status=success");
            exit();

        } catch (Exception $e) {
             // If DB error, delete the uploaded file to avoid junk
            unlink($target_file);
            die("Database Error: " . $e->getMessage());
        }

    } else {
        die("Error: Unable to move file to uploads folder. Check folder write permissions.");
    }

} else {
    // If trying to access this file directly without submitting form
    header("Location: profile.php"); exit();
}
?>