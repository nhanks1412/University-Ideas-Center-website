<?php
session_start();
require 'db_conn.php';

// --- PART 1: PROCESS REGISTER API (JSON) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);
    
    $fullname = trim($input['fullname']);
    $email = trim($input['email']);
    $password = $input['password'];
    $department_id = $input['department_id'];
    $role_id = $input['role_id']; // Get Role ID from form instead of hardcoding

    // Level 2 Security: Prevent hackers using Postman to register as role_id = 1 (Admin)
    if ($role_id == 1) {
        http_response_code(403); 
        echo json_encode(["message" => "Security Alert: You cannot register as an Admin!"]);
        exit();
    }

    // Check for duplicate Email
    $check = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        http_response_code(400); 
        echo json_encode(["message" => "This email address is already in use!"]);
        exit();
    }

    try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO Users (fullname, email, password_hash, role_id, department_id) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$fullname, $email, $hashed_password, $role_id, $department_id])) {
            
            // =========================================================
            // SEND EMAIL NOTIFICATION TO ADMIN ON NEW SELF-REGISTRATION
            // =========================================================
            require_once 'mail_helper.php';
            
            // Fetch Role Name and Department Name for a better email template
            $role_name = $conn->query("SELECT name FROM Roles WHERE role_id = " . (int)$role_id)->fetchColumn() ?: 'Unknown';
            $dept_name = $conn->query("SELECT name FROM Departments WHERE department_id = " . (int)$department_id)->fetchColumn() ?: 'Unknown';

            $stmtAdmins = $conn->query("SELECT email FROM Users WHERE role_id = 1");
            $admins = $stmtAdmins->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($admins)) {
                $subject = "🔒 [University Ideas Center] New User Registration Alert";
                $body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                        <div style='background-color: #1a2980; color: white; padding: 15px; text-align: center;'>
                            <h2 style='margin: 0;'>University Ideas Center</h2>
                        </div>
                        <div style='padding: 20px;'>
                            <p>Dear Administrator,</p>
                            <p>A new user has just registered a new account on the system. Here are the details:</p>
                            <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
                                <tbody>
                                    <tr><td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; width: 35%;'>Full Name:</td><td style='padding: 10px; border: 1px solid #ddd;'>{$fullname}</td></tr>
                                    <tr><td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Email:</td><td style='padding: 10px; border: 1px solid #ddd; color: #0056b3;'>{$email}</td></tr>
                                    <tr><td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Role:</td><td style='padding: 10px; border: 1px solid #ddd;'>{$role_name}</td></tr>
                                    <tr><td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Department:</td><td style='padding: 10px; border: 1px solid #ddd;'>{$dept_name}</td></tr>
                                </tbody>
                            </table>
                            <p style='margin-top: 20px;'>Please review this in the Admin Dashboard.</p>
                        </div>
                    </div>
                ";
                foreach ($admins as $admin) {
                    if (!empty($admin['email'])) { sendEmailNotification($admin['email'], $subject, $body); }
                }
            }
            // =========================================================

            echo json_encode(["message" => "Registration successful!"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Unknown error."]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "System error: " . $e->getMessage()]);
    }
    exit();
}

// --- PART 2: FETCH DATA & RENDER VIEW ---
if (isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

try {
    // 1. Fetch Department list
    $departments = $conn->query("SELECT * FROM Departments ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Fetch Roles list (SECURITY: HIDE ADMIN - role_id = 1)
    $roles = $conn->query("SELECT * FROM Roles WHERE role_id != 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $departments = [];
    $roles = [];
}

require 'views/register.php';
?>