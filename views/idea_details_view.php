<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($idea['title']); ?> - University Ideas Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </nav>

    <div class="container pb-5">
        
        <?php if (isset($_GET['error'])): ?>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <span class="badge bg-info text-dark mb-2"><?php echo htmlspecialchars($idea['category_name']); ?></span>
                        
                        <h2 class="fw-bold text-primary"><?php echo htmlspecialchars($idea['title']); ?></h2>
                        
                        <div class="d-flex align-items-center mb-3 mt-3">
                            <?php if ($idea['is_anonymous']): ?>
                                <div class="user-avatar-placeholder me-2" style="background: #6c757d;">?</div>
                                <div>
                                    <h6 class="fw-bold mb-0">Anonymous User</h6>
                                    <small class="text-muted"><?php echo date("M d, Y H:i", strtotime($idea['created_at'])); ?></small>
                                </div>
                            <?php else: ?>
                                <?php if (!empty($idea['avatar_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($idea['avatar_path']); ?>" class="user-avatar-img me-2">
                                <?php else: ?>
                                    <div class="user-avatar-placeholder me-2"><?php echo substr($idea['fullname'],0,1); ?></div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($idea['fullname']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($idea['dept_name'] ?? 'Student'); ?> • 
                                        <?php echo date("M d, Y H:i", strtotime($idea['created_at'])); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="content-body mb-4">
                            <?php echo nl2br(htmlspecialchars($idea['content'])); ?>
                        </div>

                        <?php if (!empty($documents)): ?>
                            <div class="bg-light p-3 rounded mb-4">
                                <h6 class="fw-bold small text-muted"><i class="bi bi-paperclip"></i> Attached Documents:</h6>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <?php foreach ($documents as $doc): ?>
                                        <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-white border shadow-sm">
                                            <i class="bi bi-file-earmark-text"></i> View Document
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <hr>

                        <div class="d-flex gap-2">
                            <button onclick="voteIdea(<?php echo $idea['idea_id']; ?>, 1)" class="btn btn-outline-success rounded-pill px-4">
                                <i class="bi bi-hand-thumbs-up-fill"></i> <span id="upvote-count-<?php echo $idea['idea_id']; ?>"><?php echo $idea['upvotes'] ?? 0; ?></span>
                            </button>
                            <button onclick="voteIdea(<?php echo $idea['idea_id']; ?>, -1)" class="btn btn-outline-danger rounded-pill px-4">
                                <i class="bi bi-hand-thumbs-down-fill"></i> <span id="downvote-count-<?php echo $idea['idea_id']; ?>"><?php echo $idea['downvotes'] ?? 0; ?></span>
                            </button>
                            <span class="ms-auto text-muted small mt-2"><i class="bi bi-eye"></i> <?php echo $idea['view_count']; ?> views</span>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-chat-dots-fill text-primary"></i> Comments (<?php echo count($comments); ?>)</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php 
                            // CHECK FINAL CLOSURE CONDITION
                            $today = date('Y-m-d');
                            $is_final_closed = (!empty($idea['final_closure_date']) && $today > $idea['final_closure_date']);
                        ?>

                        <?php if ($is_final_closed): ?>
                            <div class="alert alert-secondary text-center fw-bold shadow-sm mb-5">
                                <i class="bi bi-lock-fill fs-4 d-block mb-2 text-secondary"></i>
                                This event has ended. The comment feature is now locked.
                            </div>
                        
                        <?php elseif (isset($_SESSION['user_id'])): ?>
                            <form action="" method="POST" class="mb-5">
                                <div class="mb-2">
                                    <textarea name="comment_content" class="form-control" rows="3" placeholder="Share your thoughts..." required></textarea>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_anonymous" id="anonCmt">
                                        <label class="form-check-label small text-muted" for="anonCmt">Comment anonymously</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="bi bi-send"></i> Send</button>
                                </div>
                            </form>

                        <?php else: ?>
                            <div class="alert alert-warning text-center">
                                Please <a href="login.php" class="fw-bold">Login</a> to comment.
                            </div>
                        <?php endif; ?>

                        <div class="comment-list">
                            <?php foreach ($comments as $cmt): ?>
                                
                                <?php 
                                    $is_hidden = $cmt['is_hidden'];
                                    $is_qa_manager = (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2);
                                    
                                    // If hidden and user is not QA -> Show notification
                                    if ($is_hidden && !$is_qa_manager) {
                                ?>
                                    <div class="d-flex mb-4 opacity-50">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                                <i class="bi bi-eye-slash"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="bg-light p-3 rounded border border-danger">
                                                <small class="text-danger fw-bold fst-italic">This comment has been hidden by the Administrator.</small>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                        continue; 
                                    } 
                                ?>

                                <div class="d-flex mb-4 <?php echo $is_hidden ? 'opacity-75' : ''; ?>">
                                    <div class="flex-shrink-0">
                                        <?php if ($cmt['is_anonymous']): ?>
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:40px; height:40px;">?</div>
                                        <?php else: ?>
                                            <?php if(!empty($cmt['avatar_path'])): ?>
                                                <img src="<?php echo htmlspecialchars($cmt['avatar_path']); ?>" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:40px; height:40px;"><?php echo substr($cmt['fullname'],0,1); ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex-grow-1 ms-3">
                                        <div class="bg-light p-3 rounded position-relative">
                                            
                                            <?php if ($is_qa_manager): ?>
                                                <a href="mana_actions.php?action=toggle_hide_comment&comment_id=<?php echo $cmt['comment_id']; ?>&idea_id=<?php echo $idea['idea_id']; ?>" 
                                                   class="btn btn-sm position-absolute top-0 end-0 mt-1 me-1 <?php echo $is_hidden ? 'btn-success' : 'btn-outline-secondary'; ?>"
                                                   title="<?php echo $is_hidden ? 'Show comment' : 'Hide this comment'; ?>">
                                                     <?php if ($is_hidden): ?>
                                                        <i class="bi bi-eye-fill"></i> Show
                                                     <?php else: ?>
                                                        <i class="bi bi-eye-slash-fill"></i>
                                                     <?php endif; ?>
                                                </a>
                                            <?php endif; ?>

                                            <div class="d-flex justify-content-between pe-5">
                                                <h6 class="fw-bold mb-1">
                                                    <?php echo $cmt['is_anonymous'] ? 'Anonymous' : htmlspecialchars($cmt['fullname']); ?>
                                                    <?php if ($is_hidden): ?>
                                                        <span class="badge bg-danger ms-2">HIDDEN</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <small class="text-muted"><?php echo date("M d H:i", strtotime($cmt['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-0 small text-dark"><?php echo nl2br(htmlspecialchars($cmt['content'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($comments)): ?>
                                <p class="text-center text-muted fst-italic py-3">No comments yet. Be the first to share!</p>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>