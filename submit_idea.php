<?php
session_start();
require 'db_conn.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.html"); exit(); }

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. CHẶN LỖI SẬP SERVER: Kiểm tra xem POST có bị rỗng do file vượt quá post_max_size không
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
        echo "<script>alert('❌ Lỗi: Tổng dung lượng file tải lên vượt quá giới hạn của máy chủ!'); window.history.back();</script>";
        exit();
    }

    // 2. KIỂM TRA DUNG LƯỢNG 5MB TRƯỚC KHI LƯU VÀO DATABASE
    if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
        $total_files = count($_FILES['documents']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            $file_size = $_FILES['documents']['size'][$i];
            $file_error = $_FILES['documents']['error'][$i];
            
            // Nếu file > 5MB hoặc gặp lỗi dung lượng từ server
            if ($file_size > 5 * 1024 * 1024 || $file_error == UPLOAD_ERR_INI_SIZE) {
                echo "<script>alert('❌ Lỗi: Mỗi file đính kèm không được vượt quá 5MB. Vui lòng kiểm tra lại!'); window.history.back();</script>";
                exit();
            }
        }
    }

    // Bắt đầu lấy dữ liệu an toàn
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    
    // Kiểm tra xem các trường bắt buộc có bị thiếu không
    if (!$category_id) {
        echo "<script>alert('❌ Lỗi: Thiếu thông tin Category!'); window.history.back();</script>";
        exit();
    }
    
    // 🔥 IMPORTANT PROCESSING: IF EMPTY, SET TO NULL
    $academic_year_id = !empty($_POST['academic_year_id']) ? $_POST['academic_year_id'] : null;
    
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0; 
    
    // Get Department ID and Fullname (Get name to use for sending emails)
    $stmtUser = $conn->prepare("SELECT department_id, fullname FROM Users WHERE user_id = ?");
    $stmtUser->execute([$user_id]);
    $userInfo = $stmtUser->fetch(PDO::FETCH_ASSOC);
    
    $dept_id = $userInfo['department_id'];
    $user_fullname = $userInfo['fullname'];

    try {
        // Insert Idea (Accepts academic_year_id as NULL)
        $sql = "INSERT INTO Ideas (title, content, user_id, category_id, academic_year_id, department_id, is_anonymous) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$title, $content, $user_id, $category_id, $academic_year_id, $dept_id, $is_anonymous]);
        
        $idea_id = $conn->lastInsertId();

        // Handle File Upload (Lúc này chắc chắn file đã hợp lệ)
        if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
            $target_dir = "uploads/documents/";
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

            $total_files = count($_FILES['documents']['name']);
            for ($i = 0; $i < $total_files; $i++) {
                $file_name = $_FILES['documents']['name'][$i];
                $file_tmp = $_FILES['documents']['tmp_name'][$i];
                
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
                
                if (in_array($file_ext, $allowed)) {
                    $new_name = 'doc_' . $idea_id . '_' . uniqid() . '.' . $file_ext;
                    $target_file = $target_dir . $new_name;
                    if (move_uploaded_file($file_tmp, $target_file)) {
                        $stmtDoc = $conn->prepare("INSERT INTO Documents (idea_id, file_path, file_type) VALUES (?, ?, ?)");
                        $stmtDoc->execute([$idea_id, $target_file, $file_ext]);
                    }
                }
            }
        }

        // SEND EMAIL NOTIFICATION TO THE DEPARTMENT'S QA COORDINATOR
        require_once 'mail_helper.php';

        // If anonymous, hide the real name
        $author_name = $is_anonymous ? 'An Anonymous User' : $user_fullname; 

        // Find all QA Coordinators (role_id = 3) in the same department as the poster
        $stmtQA = $conn->prepare("SELECT email, fullname FROM Users WHERE department_id = ? AND role_id = 3");
        $stmtQA->execute([$dept_id]);
        $qas = $stmtQA->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($qas)) {
            $subject = "💡 [University Ideas Center] New Idea Submitted in Your Department";
            $body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h3 style='color: #1a2980;'>Hello QA Coordinator,</h3>
                    <p>A new idea has just been submitted in your department by <b>{$author_name}</b>.</p>
                    <div style='background: #f4f6f9; padding: 15px; border-left: 4px solid #26d0ce; margin: 15px 0;'>
                        <strong>Title:</strong> " . htmlspecialchars($title) . "
                    </div>
                    <p>Please log in to the University Ideas Center system to review it.</p>
                    <p>http://localhost/UniversityIdeas/login.php</p>
                    <br>
                    <p>Best Regards,<br><b>University Ideas Center System</b></p>
                </div>
            ";
            
            // Send email to each QA Coordinator found in that department
            foreach ($qas as $qa) {
                sendEmailNotification($qa['email'], $subject, $body);
            }
        }

        echo "<script>alert('✅ Idea submitted successfully!'); window.location.href = 'index.php';</script>";

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
}
?>