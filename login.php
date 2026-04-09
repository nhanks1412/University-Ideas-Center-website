<?php
session_start();
require 'db_conn.php';

// --- PART 1: HANDLE LOGIN API (JSON) ---
// If POST request received -> Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); // Return JSON
    
    // Read submitted JSON data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Please fill in all fields!"]);
        exit();
    }

    try {
        $stmt = $conn->prepare("SELECT user_id, fullname, password_hash, role_id FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful -> Save Session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role_id'] = $user['role_id'];

            echo json_encode(["status" => "success", "message" => "Login successful!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect Email or Password!"]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "System error: " . $e->getMessage()]);
    }
    exit(); // Stop script execution here
}

// --- PART 2: HANDLE UI DISPLAY (HTML) ---
// If GET request (Normal access)

// If already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Call the view
require 'views/login.php';
?>