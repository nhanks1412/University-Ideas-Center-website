<?php
session_start();
require 'db_conn.php';

header('Content-Type: application/json');

// 1. Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You need to login to vote!']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $idea_id = $_POST['idea_id'];
    $vote_type = $_POST['vote_type']; // 1: Like, -1: Dislike

    try {
        // 2. Check if user has already voted
        $stmt = $conn->prepare("SELECT vote_type FROM Votes WHERE user_id = ? AND idea_id = ?");
        $stmt->execute([$user_id, $idea_id]);
        $existing_vote = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_vote) {
            // If already voted
            if ($existing_vote['vote_type'] == $vote_type) {
                // Clicked the same button -> Cancel vote (Delete from DB)
                $conn->prepare("DELETE FROM Votes WHERE user_id = ? AND idea_id = ?")->execute([$user_id, $idea_id]);
            } else {
                // Clicked different button -> Switch vote (e.g., Like to Dislike)
                $conn->prepare("UPDATE Votes SET vote_type = ? WHERE user_id = ? AND idea_id = ?")->execute([$vote_type, $user_id, $idea_id]);
            }
        } else {
            // Never voted before -> Create new
            $conn->prepare("INSERT INTO Votes (user_id, idea_id, vote_type) VALUES (?, ?, ?)")->execute([$user_id, $idea_id, $vote_type]);
        }

        // 3. Recalculate total Like/Dislike to return for UI update
        $stmtUp = $conn->prepare("SELECT COUNT(*) FROM Votes WHERE idea_id = ? AND vote_type = 1");
        $stmtUp->execute([$idea_id]);
        $upvotes = $stmtUp->fetchColumn();

        $stmtDown = $conn->prepare("SELECT COUNT(*) FROM Votes WHERE idea_id = ? AND vote_type = -1");
        $stmtDown->execute([$idea_id]);
        $downvotes = $stmtDown->fetchColumn();
        
        // Update Ideas table (Cache data for faster query if needed)
        $conn->prepare("UPDATE Ideas SET upvotes = ?, downvotes = ? WHERE idea_id = ?")->execute([$upvotes, $downvotes, $idea_id]);

        echo json_encode([
            'status' => 'success', 
            'upvotes' => $upvotes, 
            'downvotes' => $downvotes
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>