<?php
session_start();
require 'db_conn.php';

// 1. SECURITY CHECK: Only Admin can access this file
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    die("🚫 Access denied!");
}

// Check if any action is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' || (isset($_GET['action']) && $_GET['action'] == 'set_active_global_year')) {
    
    $action = $_POST['action'] ?? $_GET['action'];

    try {
        // --- PROCESS 1: UPDATE TERMS ---
        if ($action == 'update_terms') {
            $content = $_POST['terms_content'];
            
            $sql = "UPDATE Settings SET setting_value = ? WHERE setting_key = 'terms_content'";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$content]);

            if ($stmt->rowCount() == 0) {
                $sqlInsert = "INSERT IGNORE INTO Settings (setting_key, setting_value) VALUES ('terms_content', ?)";
                $conn->prepare($sqlInsert)->execute([$content]);
            }

            header("Location: admin_dashboard.php?tab=terms&msg=updated_terms");
            exit();
        }

        // --- PROCESS 2: CREATE NEW USER ---
        elseif ($action == 'create_user') {
            $fullname = trim($_POST['fullname']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $role_id = $_POST['role_id'];
            $dept_id = $_POST['department_id'];

            // Check for duplicate email
            $stmtCheck = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
            $stmtCheck->execute([$email]);
            if($stmtCheck->rowCount() > 0) {
                header("Location: admin_dashboard.php?tab=users&error=Email already exists!"); 
                exit();
            }

            // Hash password and save
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO Users (fullname, email, password_hash, role_id, department_id) VALUES (?, ?, ?, ?, ?)";
            $conn->prepare($sql)->execute([$fullname, $email, $hashed_pass, $role_id, $dept_id]);

            // SEND EMAIL NOTIFICATION TO ADMIN WHEN A NEW ACCOUNT IS CREATED
            require_once 'mail_helper.php';

            // 1. Get Role and Department name to display as text (instead of numeric ID)
            $role_name = $conn->query("SELECT name FROM Roles WHERE role_id = " . (int)$role_id)->fetchColumn();
            $dept_name = $conn->query("SELECT name FROM Departments WHERE department_id = " . (int)$dept_id)->fetchColumn();

            // 2. Find all Admins (role_id = 1) to send mail
            $stmtAdmins = $conn->query("SELECT email, fullname FROM Users WHERE role_id = 1");
            $admins = $stmtAdmins->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($admins)) {
                $subject = "🔒 [University Ideas Center] New User Registration Alert";
                
                // 3. Design the Information Table using HTML
                $body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                        <div style='background-color: #1a2980; color: white; padding: 15px; text-align: center;'>
                            <h2 style='margin: 0;'>University Ideas Center</h2>
                        </div>
                            <div style='padding: 20px;'>
                                <p>Dear Administrator,</p>
                                <p>The system has recorded a new user account successfully created. Below are the details:</p>
                                
                                <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
                                    <thead>
                                        <tr>
                                            <th colspan='2' style='background-color: #f4f6f9; padding: 12px; border: 1px solid #ddd; text-align: left; font-size: 16px; color: #1a2980;'>
                                                📋 New Account Details
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold; width: 35%;'>Full Name:</td>
                                            <td style='padding: 10px; border: 1px solid #ddd;'>{$fullname}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Email:</td>
                                            <td style='padding: 10px; border: 1px solid #ddd; color: #0056b3;'>{$email}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Role:</td>
                                            <td style='padding: 10px; border: 1px solid #ddd;'>{$role_name}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Department:</td>
                                            <td style='padding: 10px; border: 1px solid #ddd;'>{$dept_name}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 10px; border: 1px solid #ddd; font-weight: bold;'>Created At:</td>
                                            <td style='padding: 10px; border: 1px solid #ddd;'>" . date('d/m/Y H:i:s') . "</td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <p style='margin-top: 20px;'>Please log in to the Admin Dashboard to review or assign permissions if necessary.</p>
                            </div>

                            <div style='background-color: #f4f6f9; padding: 10px; text-align: center; color: #777; font-size: 12px;'>
                                This is an automated email from the system. Please do not reply to this email.
                            </div>
                ";
                
                // 4. Send email to each Admin
                foreach ($admins as $admin) {
                    if (!empty($admin['email'])) {
                        sendEmailNotification($admin['email'], $subject, $body);
                    }
                }
            }
            // =========================================================

            header("Location: admin_dashboard.php?tab=users&msg=created_user");
            exit();
        }

        // --- PROCESS 3: UPDATE ROLE & DEPARTMENT ---
        elseif ($action == 'update_role') {
            $user_id = $_POST['user_id'];
            $role_id = $_POST['role_id'];
            // Lấy thêm dept_id, nếu không chọn thì lưu là NULL
            $dept_id = !empty($_POST['department_id']) ? $_POST['department_id'] : null;
            $conn->prepare("UPDATE Users SET role_id = ?, department_id = ? WHERE user_id = ?")
                 ->execute([$role_id, $dept_id, $user_id]);
            header("Location: admin_dashboard.php?tab=users&msg=" . urlencode("User Role and Department updated successfully!"));
            exit();
        }

        // --- PROCESS 4: RESET PASSWORD ---
        elseif ($action == 'reset_password') {
            $user_id = $_POST['user_id'];
            $new_pass = $_POST['new_password'];

            // Backend validation
            if (strlen($new_pass) < 8 || !preg_match('/[A-Z]/', $new_pass)) {
                header("Location: admin_dashboard.php?tab=users&error=weak_password"); 
                exit();
            }

            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $conn->prepare("UPDATE Users SET password_hash = ? WHERE user_id = ?")->execute([$hashed_pass, $user_id]);
            header("Location: admin_dashboard.php?tab=users&msg=reset_pass");
            exit();
        }

        // --- PROCESS 5: DELETE USER ---
        elseif ($action == 'delete_user') {
            $user_id = $_POST['user_id'];

            if ($user_id == $_SESSION['user_id']) {
                header("Location: admin_dashboard.php?tab=users&error=Cannot delete yourself!"); 
                exit();
            }

            $conn->prepare("DELETE FROM Users WHERE user_id = ?")->execute([$user_id]);
            header("Location: admin_dashboard.php?tab=users&msg=deleted_user");
            exit();
        }

        // NEW PROCESSES: GLOBAL ACADEMIC YEARS (ADMIN STATS TRACKING)

        // --- PROCESS 6: ADD GLOBAL YEAR ---
        elseif ($action == 'add_global_year') {
            $name = trim($_POST['year_name']);
            $start = $_POST['start_date'];
            $end = $_POST['end_date'];

            if ($end <= $start) {
                header("Location: admin_dashboard.php?tab=stats&error=" . urlencode("End date must be after Start date!"));
                exit();
            }

            // If this is the first year created, automatically set it as active
            $checkActive = $conn->query("SELECT COUNT(*) FROM GlobalYears WHERE is_active = 1")->fetchColumn();
            $is_active = ($checkActive == 0) ? 1 : 0;

            $stmt = $conn->prepare("INSERT INTO GlobalYears (year_name, start_date, end_date, is_active) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $start, $end, $is_active]);

            header("Location: admin_dashboard.php?tab=stats&msg=" . urlencode("New Academic Year added!"));
            exit();
        }

        // --- PROCESS 7: EDIT GLOBAL YEAR ---
        elseif ($action == 'edit_global_year') {
            $id = $_POST['global_year_id'];
            $name = trim($_POST['year_name']);
            $start = $_POST['start_date'];
            $end = $_POST['end_date'];

            if ($end <= $start) {
                header("Location: admin_dashboard.php?tab=stats&error=" . urlencode("End date must be after Start date!"));
                exit();
            }

            $stmt = $conn->prepare("UPDATE GlobalYears SET year_name = ?, start_date = ?, end_date = ? WHERE global_year_id = ?");
            $stmt->execute([$name, $start, $end, $id]);

            header("Location: admin_dashboard.php?tab=stats&msg=" . urlencode("Academic Year updated successfully!"));
            exit();
        }

        // --- PROCESS 8: SET ACTIVE GLOBAL YEAR ---
        elseif ($action == 'set_active_global_year') {
            $id = $_GET['id'];
            
            // Disable active for all academic years
            $conn->query("UPDATE GlobalYears SET is_active = 0");
            
            // Enable active for the selected ID
            $stmt = $conn->prepare("UPDATE GlobalYears SET is_active = 1 WHERE global_year_id = ?");
            $stmt->execute([$id]);

            header("Location: admin_dashboard.php?tab=stats&msg=" . urlencode("Changed current Active Academic Year successfully!"));
            exit();
        }

    } catch (Exception $e) {
        $tab = $_POST['tab'] ?? $_GET['tab'] ?? 'users';
        header("Location: admin_dashboard.php?tab=$tab&error=" . urlencode($e->getMessage()));
        exit();
    }

} else {
    header("Location: admin_dashboard.php");
    exit();
}
?>