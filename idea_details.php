<?php
session_start();
require 'db_conn.php';

if (!isset($_GET['id'])) {
    header("Location: index.php"); exit();
}

$idea_id = $_GET['id'];
$user_id = $_SESSION['user_id'] ?? 0;

// --- HANDLE COMMENT SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_content'])) {
    if (!$user_id) { header("Location: login.php"); exit(); }
    
    // 🔥 SECURITY STEP: CHECK IF FINAL CLOSURE DATE HAS PASSED
    $stmtCheckDate = $conn->prepare("
        SELECT a.final_closure_date 
        FROM Ideas i 
        LEFT JOIN AcademicYears a ON i.academic_year_id = a.academic_year_id 
        WHERE i.idea_id = ?
    ");
    $stmtCheckDate->execute([$idea_id]);
    $final_date = $stmtCheckDate->fetchColumn();

    if ($final_date && date('Y-m-d') > $final_date) {
        // If current date > final_closure_date -> Block and redirect to details page
        header("Location: idea_details.php?id=$idea_id&error=" . urlencode("This event is closed, no more comments allowed!")); 
        exit();
    }
    
    $content = trim($_POST['comment_content']);
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    
    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO Comments (idea_id, user_id, content, is_anonymous) VALUES (?, ?, ?, ?)");
        $stmt->execute([$idea_id, $user_id, $content, $is_anonymous]);
        
        // SEND EMAIL NOTIFICATION TO THE IDEA AUTHOR
        require_once 'mail_helper.php';

        $commenter_name = $is_anonymous ? 'An anonymous user' : ($_SESSION['fullname'] ?? 'A user');

        $stmtAuthor = $conn->prepare("
            SELECT u.email, u.fullname, u.user_id, i.title 
            FROM Ideas i 
            JOIN Users u ON i.user_id = u.user_id 
            WHERE i.idea_id = ?
        ");
        $stmtAuthor->execute([$idea_id]);
        $author = $stmtAuthor->fetch(PDO::FETCH_ASSOC);

        // Send mail if author exists and IS NOT COMMENTING ON THEIR OWN POST
        if ($author && !empty($author['email']) && $author['user_id'] != $user_id) {
            $subject = "💬 [University Ideas Center] New comment on your idea";
            $body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h3 style='color: #d9534f;'>Hello {$author['fullname']},</h3>
                    <p><b>{$commenter_name}</b> has just left a new comment on your idea:</p>
                    <div style='background: #f9f2f4; padding: 15px; border-left: 4px solid #d9534f; margin: 15px 0;'>
                        <strong>{$author['title']}</strong>
                    </div>
                    <p>Please log in to the University Ideas Center system to read and respond!</p>
                    <br>
                    <p>Best regards,<br><b>University Ideas Center System</b></p>
                </div>
            ";
            sendEmailNotification($author['email'], $subject, $body);
        }
        // =========================================================
    }
    header("Location: idea_details.php?id=$idea_id"); 
    exit();
}

// --- FETCH IDEA DATA ---
try {
    // 🔥 Fetch additional column a.final_closure_date from AcademicYears table
    $sqlIdea = "SELECT i.*, u.fullname, u.avatar_path, c.name as category_name, d.name as dept_name, a.final_closure_date 
                FROM Ideas i 
                JOIN Users u ON i.user_id = u.user_id 
                JOIN Categories c ON i.category_id = c.category_id
                LEFT JOIN Departments d ON i.department_id = d.department_id
                LEFT JOIN AcademicYears a ON i.academic_year_id = a.academic_year_id
                WHERE i.idea_id = ?";
    $stmtIdea = $conn->prepare($sqlIdea);
    $stmtIdea->execute([$idea_id]);
    $idea = $stmtIdea->fetch(PDO::FETCH_ASSOC);

    if (!$idea) { die("Post does not exist!"); }

    // Increase view count
    $conn->prepare("UPDATE Ideas SET view_count = view_count + 1 WHERE idea_id = ?")->execute([$idea_id]);

    // Fetch list of attachments
    $docs = $conn->prepare("SELECT * FROM Documents WHERE idea_id = ?");
    $docs->execute([$idea_id]);
    $documents = $docs->fetchAll(PDO::FETCH_ASSOC);

    // Fetch list of comments
    $sqlCmt = "SELECT cm.*, u.fullname, u.avatar_path 
               FROM Comments cm 
               JOIN Users u ON cm.user_id = u.user_id 
               WHERE cm.idea_id = ? 
               ORDER BY cm.created_at DESC";
    $stmtCmt = $conn->prepare($sqlCmt);
    $stmtCmt->execute([$idea_id]);
    $comments = $stmtCmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("System Error: " . $e->getMessage());
}

// Load View file
require 'views/idea_details_view.php';
?>