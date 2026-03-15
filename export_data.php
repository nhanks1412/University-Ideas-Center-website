<?php
session_start();
require 'db_conn.php';

// Security: Only QA Manager (role_id = 2) is allowed to download
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    die("Access denied! QA Managers only.");
}

try {
    // 1. Fetch all post data with Total Upvotes and Total Comments
    $sql = "
        SELECT 
            i.title, 
            i.content, 
            i.created_at, 
            i.upvotes, 
            i.is_anonymous,
            u.fullname,
            (SELECT COUNT(*) FROM Comments c WHERE c.idea_id = i.idea_id) as total_comments
        FROM Ideas i
        LEFT JOIN Users u ON i.user_id = u.user_id
        ORDER BY i.created_at DESC
    ";
    $stmt = $conn->query($sql);
    $ideas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Create CSV data in memory buffer (RAM)
    $csv_memory = fopen('php://temp', 'w');
    
    // Add BOM (Byte Order Mark) so Excel opens UTF-8 encoded Vietnamese/Special chars without font errors
    fputs($csv_memory, chr(0xEF).chr(0xBB).chr(0xBF)); 
    
    // Write Header row (Column titles)
    fputcsv($csv_memory, ['Author Name', 'Idea Title', 'Content', 'Posted Date', 'Total Likes', 'Total Comments']);
    
    // Populate CSV with each idea's data
    foreach ($ideas as $row) {
        // Handle Anonymity logic
        $author = $row['is_anonymous'] ? 'Anonymous User' : $row['fullname'];
        
        fputcsv($csv_memory, [
            $author,
            $row['title'],
            $row['content'],
            date('Y-m-d H:i:s', strtotime($row['created_at'])),
            $row['upvotes'],
            $row['total_comments']
        ]);
    }
    
    // Read CSV content back from memory
    rewind($csv_memory);
    $csv_content = stream_get_contents($csv_memory);
    fclose($csv_memory);

    // 3. Initialize ZIP file
    $zip = new ZipArchive();
    $zipFileName = 'UniIdeaHub_Export_' . date('Ymd_His') . '.zip';
    $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName; // Save temporarily on server

    if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
        // Add the CSV file into the ZIP archive
        $zip->addFromString('ideas_data.csv', $csv_content);
        $zip->close();

        // 4. Force browser to download the ZIP file
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
        header('Content-Length: ' . filesize($zipFilePath));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($zipFilePath);
        
        // 5. Cleanup: Delete temporary ZIP file from server to save disk space
        unlink($zipFilePath);
        exit();
    } else {
        die("Error: Could not create ZIP file. Please check server permissions.");
    }

} catch (Exception $e) {
    die("System Error: " . $e->getMessage());
}
?>