<?php
session_start();
require 'db_conn.php';

// Role ID 2 = QA Manager
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    die("Access denied! You are not a QA Manager.");
}

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    try {
        // --- PROCESS 1: ADD CATEGORY ---
        if ($action == 'add_category') {
            $name = trim($_POST['name']);
            $desc = trim($_POST['description']);
            
            $stmt = $conn->prepare("INSERT INTO Categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $desc]);
            
            header("Location: mana_dashboard.php?tab=categories&msg=New category added successfully!");
        }

        // --- PROCESS 2: DELETE CATEGORY ---
        elseif ($action == 'delete_cat') {
            $id = $_GET['id'];
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM Ideas WHERE category_id = ?");
            $stmtCheck->execute([$id]);
            $count = $stmtCheck->fetchColumn();

            if ($count > 0) {
                header("Location: mana_dashboard.php?tab=categories&error=Category currently in use (Contains $count articles)");
            } else {
                $conn->prepare("DELETE FROM Categories WHERE category_id = ?")->execute([$id]);
                header("Location: mana_dashboard.php?tab=categories&msg=Category deleted successfully!");
            }
        }

        // --- PROCESS 3: ADD ACADEMIC YEAR / SURVEY ---
        elseif ($action == 'add_academic_year') {
            $name = $_POST['name'];
            $description = $_POST['description']; 
            $survey_type = $_POST['survey_type']; 
            $start = $_POST['start_date'];
            $close = $_POST['closure_date'];
            $final = $_POST['final_closure_date'];

            if ($close <= $start || $final <= $close) {
                header("Location: mana_dashboard.php?tab=academic&error=Date error: Final > Closure > Start!");
                exit();
            }

            $sql = "INSERT INTO AcademicYears (name, description, survey_type, start_date, closure_date, final_closure_date) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $description, $survey_type, $start, $close, $final]);
            
            // SEND NEW EVENT NOTIFICATION EMAIL TO USERS (EXCEPT QA MANAGER)
            require_once 'mail_helper.php';

            // Get the email list of all users except QA Manager (role_id = 2)
            $stmtUsers = $conn->query("SELECT email, fullname FROM Users WHERE role_id != 2 AND email IS NOT NULL AND email != ''");
            $usersToNotify = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($usersToNotify)) {
                $subject = "🎉 [University Ideas Center] New Event Announced: " . $name;
                
                $body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                        <div style='background-color: #28a745; color: white; padding: 15px; text-align: center;'>
                            <h2 style='margin: 0;'>New Event / Campaign!</h2>
                        </div>
                        
                        <div style='padding: 20px;'>
                            <p>Hello,</p>
                            <p>The Quality Assurance department has just launched a new event on the University Ideas Center system. We are looking forward to your innovative ideas!</p>
                            
                            <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0;'>
                                <h3 style='margin-top: 0; color: #28a745;'>{$name}</h3>
                                <p><strong>Type:</strong> {$survey_type}</p>
                                <p><strong>Description:</strong> " . nl2br(htmlspecialchars($description)) . "</p>
                                <hr style='border: 0; border-top: 1px solid #ddd; margin: 10px 0;'>
                                <p><strong>Start Date:</strong> " . date("d/m/Y", strtotime($start)) . "</p>
                                <p><strong>Submission Deadline:</strong> <span style='color: red; font-weight: bold;'>" . date("d/m/Y", strtotime($close)) . "</span></p>
                            </div>
                            
                            <p>Please log in to the system to prepare and submit your ideas before the deadline.</p>
                            <br>
                            <p>Best Regards,<br><b>University Ideas Center QA Manager</b></p>
                        </div>
                        
                        <div style='background-color: #f4f6f9; padding: 10px; text-align: center; color: #777; font-size: 12px;'>
                            This is an automated message from the University Ideas Center system. Please do not reply.
                        </div>
                    </div>
                ";

                // Send email to each user
                foreach ($usersToNotify as $u) {
                    sendEmailNotification($u['email'], $subject, $body);
                }
            }

            header("Location: mana_dashboard.php?tab=academic&msg=New Survey/Event created successfully!");
        }

       // --- PROCESS 4: DELETE ACADEMIC YEAR (SURVEY/EVENT) ---
        elseif ($action == 'delete_year') {
            $id = $_GET['id'];

            // 1. Check if there are ideas linked to this event
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM Ideas WHERE academic_year_id = ?");
            $stmtCheck->execute([$id]);
            $ideaCount = $stmtCheck->fetchColumn();

            if ($ideaCount > 0) {
                // 2. Block deletion if ideas exist
                $errorMsg = "Cannot delete this event! There are already $ideaCount idea(s) submitted.";
                header("Location: mana_dashboard.php?tab=academic&error=" . urlencode($errorMsg));
                exit();
            } else {
                // 3. Delete safely if no ideas are linked
                $conn->prepare("DELETE FROM AcademicYears WHERE academic_year_id = ?")->execute([$id]);
                header("Location: mana_dashboard.php?tab=academic&msg=Event deleted successfully!");
                exit();
            }
        }

        // --- PROCESS 5: DELETE POST (HARD DELETE) ---
        elseif ($action == 'delete_idea') {
            $id = $_GET['id'];
            $conn->prepare("DELETE FROM Ideas WHERE idea_id = ?")->execute([$id]);
            header("Location: mana_dashboard.php?tab=posts&msg=Post permanently deleted!");
        }

        // --- PROCESS 6: HIDE / SHOW COMMENT ---
        elseif ($action == 'toggle_hide_comment') {
            $cmt_id = $_GET['comment_id'];
            $idea_id = $_GET['idea_id'];
            $source = $_GET['source'] ?? 'details';
            
            $stmt = $conn->prepare("SELECT is_hidden FROM Comments WHERE comment_id = ?");
            $stmt->execute([$cmt_id]);
            $current = $stmt->fetchColumn();
            
            $new_status = $current ? 0 : 1;
            $conn->prepare("UPDATE Comments SET is_hidden = ? WHERE comment_id = ?")->execute([$new_status, $cmt_id]);
            
            if ($source == 'dashboard') {
                $msg = $new_status ? "Comment hidden!" : "Comment visible again!";
                header("Location: mana_dashboard.php?tab=posts&msg=" . urlencode($msg));
            } else {
                header("Location: idea_details.php?id=$idea_id");
            }
        }

        // --- PROCESS 7: HIDE / SHOW IDEA (SOFT DELETE) ---
        elseif ($action == 'toggle_hide_idea') {
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT is_hidden FROM Ideas WHERE idea_id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetchColumn();
            
            $new_status = $current ? 0 : 1;
            $conn->prepare("UPDATE Ideas SET is_hidden = ? WHERE idea_id = ?")->execute([$new_status, $id]);
            
            $msg = $new_status ? "Post HIDDEN from homepage!" : "Post RESTORED to homepage!";
            header("Location: mana_dashboard.php?tab=posts&msg=" . urlencode($msg));
        }

        // --- PROCESS 8: UPDATE ACADEMIC YEAR ---
        elseif ($action == 'update_academic_year') {
            $id = $_POST['academic_year_id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $survey_type = $_POST['survey_type'];
            $start_date = $_POST['start_date']; 
            $closure_date = $_POST['closure_date'];
            $final_closure_date = $_POST['final_closure_date'];

            // Validate date logic
            if ($closure_date <= $start_date) {
                 header("Location: mana_dashboard.php?tab=academic&error=" . urlencode("Date error: Closure Date must be after Start Date!"));
                 exit();
            }
            if ($final_closure_date <= $closure_date) {
                 header("Location: mana_dashboard.php?tab=academic&error=" . urlencode("Date error: Final Closure Date must be after Closure Date!"));
                 exit();
            }

            // Update database
            $stmt = $conn->prepare("UPDATE AcademicYears SET name = ?, description = ?, survey_type = ?, start_date = ?, closure_date = ?, final_closure_date = ? WHERE academic_year_id = ?");
            $stmt->execute([$name, $description, $survey_type, $start_date, $closure_date, $final_closure_date, $id]);

            header("Location: mana_dashboard.php?tab=academic&msg=Event updated successfully!");
        }
        
        // --- PROCESS 9: ADD DEPARTMENT (NEW) ---
        elseif ($action == 'add_department') {
            $name = trim($_POST['name']);
            
            // Check for duplicate name
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM Departments WHERE name = ?");
            $stmtCheck->execute([$name]);
            if ($stmtCheck->fetchColumn() > 0) {
                header("Location: mana_dashboard.php?tab=departments&error=Department name already exists!");
                exit();
            }

            $conn->prepare("INSERT INTO Departments (name) VALUES (?)")->execute([$name]);
            header("Location: mana_dashboard.php?tab=departments&msg=Department created successfully!");
        }

        // --- PROCESS 10: DELETE DEPARTMENT (NEW) ---
        elseif ($action == 'delete_department') {
            $id = $_GET['id'];
            
            // Check if there are any users belonging to this department
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM Users WHERE department_id = ?");
            $stmtCheck->execute([$id]);
            $userCount = $stmtCheck->fetchColumn();

            if ($userCount > 0) {
                header("Location: mana_dashboard.php?tab=departments&error=Cannot delete! There are $userCount users in this department.");
                exit();
            }

            $conn->prepare("DELETE FROM Departments WHERE department_id = ?")->execute([$id]);
            header("Location: mana_dashboard.php?tab=departments&msg=Department deleted successfully!");
        }

    } catch (Exception $e) {
        // Fallback: stay on the tab if provided, otherwise default
        $tab = $_REQUEST['tab'] ?? 'categories';
        header("Location: mana_dashboard.php?tab=$tab&error=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: mana_dashboard.php");
}
?>