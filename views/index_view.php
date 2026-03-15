<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - University Ideas Center</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm navbar-custom">
        <div class="container">
            <div class="d-flex align-items-center">
                <a class="navbar-brand fw-bold d-flex align-items-center text-white me-3" href="index.php">
                    <img src="img/greenwichlogo.png" alt="Logo" width="150" class="d-inline-block align-text-top me-2" style="mix-blend-mode: multiply;"> 
                    Ideas Center
                </a>
                
                <form action="index.php" method="GET" class="d-none d-md-block">
                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($current_tab); ?>">
                    <select name="global_year" class="form-select form-select-sm fw-bold bg-light text-primary border-0 shadow-sm" style="font-size: 0.8rem; cursor: pointer;" onchange="this.form.submit()">
                        <?php foreach($all_global_years as $gy): ?>
                            <option value="<?php echo $gy['global_year_id']; ?>" <?php echo ($current_global_year_id == $gy['global_year_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($gy['year_name']); ?> <?php echo $gy['is_active'] ? '(Current)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                
                <?php if ($current_role_id == 1): ?>
                    <a href="admin_dashboard.php" class="btn btn-dark btn-sm d-none d-md-flex align-items-center shadow-sm">
                        <i class="bi bi-shield-lock-fill text-warning me-1"></i> Admin Portal
                    </a>
                <?php elseif ($current_role_id == 2): ?>
                    <a href="mana_dashboard.php" class="btn btn-primary btn-sm d-none d-md-flex align-items-center shadow-sm" style="background: linear-gradient(to right, #1a2980, #26d0ce); border: none;">
                        <i class="bi bi-briefcase-fill me-1"></i> QA Manager
                    </a>
                <?php elseif ($current_role_id == 3): ?>
                    <a href="qa_dashboard.php" class="btn btn-info text-white btn-sm d-none d-md-flex align-items-center shadow-sm" style="background-color: #0056b3; border: none;">
                        <i class="bi bi-person-workspace me-1"></i> QA Dashboard
                    </a>
                <?php endif; ?>
                <div class="dropdown">
                    <button class="btn btn-dark dropdown-toggle d-flex align-items-center py-1 pe-3" type="button" data-bs-toggle="dropdown">
                        <div class="me-2">
                            <?php if (!empty($avatar_path) && file_exists($avatar_path)): ?>
                                <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Avatar" class="user-avatar-img">
                            <?php else: ?>
                                <div class="user-avatar-placeholder"><?php echo $first_letter; ?></div>
                            <?php endif; ?>
                        </div>
                        <span class="d-none d-md-block"><?php echo htmlspecialchars($current_user_name); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle me-2"></i>My Profile</a></li>
                        
                        <?php if ($current_role_id == 1): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item fw-bold text-danger" href="admin_dashboard.php"><i class="bi bi-shield-lock-fill me-2"></i>Admin Dashboard</a></li>
                        <?php endif; ?>

                        <?php if ($current_role_id == 2): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item fw-bold text-primary" href="mana_dashboard.php"><i class="bi bi-briefcase-fill me-2"></i>QA Manager Dashboard</a></li>
                        <?php endif; ?>

                        <?php if ($current_role_id == 3): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item fw-bold" style="color: #0056b3;" href="qa_dashboard.php"><i class="bi bi-person-workspace me-2"></i>QA Dashboard</a></li>
                        <?php endif; ?>

                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-secondary" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <?php if (!$is_viewing_active_year): ?>
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
                <i class="bi bi-clock-history fs-3 me-3"></i>
                <div>
                    <h6 class="mb-0 fw-bold">Historical View Mode</h6>
                    <small>You are viewing ideas from <b><?php echo htmlspecialchars($global_year_display); ?></b>. Submitting new ideas to past terms is restricted.</small>
                </div>
                <a href="index.php" class="btn btn-sm btn-primary ms-auto">Return to Current Year</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($active_surveys)): ?>
            <div class="row mb-4">
                <div class="col-12 mb-3">
                    <h5 class="fw-bold text-dark border-start border-4 border-danger ps-3">
                        <i class="bi bi-calendar-star-fill text-danger"></i> Special Events & Deadlines
                    </h5>
                </div>

                <?php foreach ($active_surveys as $survey): ?>
                    <?php 
                        $today = date('Y-m-d');
                        $is_submission_closed = ($today > $survey['closure_date']);
                        $days_left = ceil((strtotime($survey['closure_date']) - time()) / (60 * 60 * 24));
                    ?>
                    <div class="col-md-12 mb-3">
                        <div class="card shadow-sm border-0 border-start border-5 <?php echo $is_submission_closed ? 'border-secondary' : 'border-success'; ?>">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge <?php echo $is_submission_closed ? 'bg-secondary' : 'bg-success'; ?> me-2">
                                                <?php echo htmlspecialchars($survey['survey_type'] ?? 'Event'); ?>
                                            </span>
                                            <h4 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($survey['name']); ?></h4>
                                        </div>
                                        <p class="text-muted mb-3">
                                            <?php echo nl2br(htmlspecialchars($survey['description'] ?? '')); ?>
                                        </p>
                                        <div class="d-flex flex-wrap gap-4 text-small text-muted">
                                            <span><i class="bi bi-calendar-plus"></i> Start: <b><?php echo date("d/m/Y", strtotime($survey['start_date'])); ?></b></span>
                                            <span class="<?php echo ($days_left <= 3 && !$is_submission_closed) ? 'text-danger fw-bold' : ''; ?>">
                                                <i class="bi bi-calendar-x"></i> Deadline: <b><?php echo date("d/m/Y", strtotime($survey['closure_date'])); ?></b>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-4 text-end mt-3 mt-md-0">
                                        <?php if ($is_submission_closed): ?>
                                            <button class="btn btn-secondary btn-lg disabled w-100 shadow-none" style="opacity: 0.7;">
                                                <i class="bi bi-lock-fill"></i> Submission Closed
                                            </button>
                                        <?php else: ?>
                                            <a href="create_idea.php?academic_year_id=<?php echo $survey['academic_year_id']; ?>" 
                                               class="btn btn-outline-success btn-lg w-100 fw-bold">
                                                Join This Event <i class="bi bi-arrow-right"></i>
                                            </a>
                                            <small class="d-block mt-2 text-success fst-italic">
                                                <i class="bi bi-hourglass-split"></i> <?php echo $days_left; ?> days left
                                            </small>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="row my-4">
            
            <div class="col-md-3">
                <div class="list-group shadow-sm mb-4">
                    <a href="index.php?tab=latest&global_year=<?php echo $current_global_year_id; ?>" class="list-group-item list-group-item-action <?php echo ($current_tab == 'latest') ? 'active fw-bold' : ''; ?>">
                       <i class="bi bi-collection me-2"></i> All Ideas
                    </a>
                    <a href="index.php?tab=popular&global_year=<?php echo $current_global_year_id; ?>" class="list-group-item list-group-item-action <?php echo ($current_tab == 'popular') ? 'active fw-bold' : ''; ?>">
                       <i class="bi bi-fire me-2"></i> Most Popular
                    </a>
                    <a href="index.php?tab=my_ideas&global_year=<?php echo $current_global_year_id; ?>" class="list-group-item list-group-item-action <?php echo ($current_tab == 'my_ideas') ? 'active fw-bold' : ''; ?>">
                       <i class="bi bi-person-heart me-2"></i> My Ideas
                    </a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card shadow-sm border-0 mb-4 bg-white">
                    <div class="card-body py-2">
                        <form action="index.php" method="GET" class="row g-2 align-items-center">
                            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($current_tab); ?>">
                            <input type="hidden" name="global_year" value="<?php echo $current_global_year_id; ?>">
                            
                            <div class="col-auto">
                                <span class="fw-bold text-muted small"><i class="bi bi-funnel-fill"></i> Filter Time:</span>
                            </div>
                            
                            <div class="col-auto">
                                <select name="filter_year" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">-- All Years --</option>
                                    <?php foreach ($valid_years as $y): ?>
                                        <option value="<?php echo $y; ?>" <?php echo (isset($_GET['filter_year']) && $_GET['filter_year'] == $y) ? 'selected' : ''; ?>>
                                            Year <?php echo $y; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-auto">
                                <select name="filter_month" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">-- All Months --</option>
                                    <?php for($m=1; $m<=12; $m++): ?>
                                        <option value="<?php echo $m; ?>" <?php echo (isset($_GET['filter_month']) && $_GET['filter_month'] == $m) ? 'selected' : ''; ?>>
                                            Month <?php echo $m; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <?php if(!empty($_GET['filter_year']) || !empty($_GET['filter_month'])): ?>
                            <div class="col-auto ms-auto">
                                <a href="index.php?tab=<?php echo htmlspecialchars($current_tab); ?>&global_year=<?php echo $current_global_year_id; ?>" class="btn btn-sm text-danger fw-bold">
                                    <i class="bi bi-x-circle"></i> Clear Filters
                                </a>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <h4 class="mb-3 fw-bold text-dark">
                    <?php 
                        if($current_tab == 'my_ideas') echo '<i class="bi bi-person-heart text-primary"></i> My Ideas';
                        elseif($current_tab == 'popular') echo '<i class="bi bi-fire text-danger"></i> Most Popular Ideas';
                        else echo '<i class="bi bi-lightbulb text-warning"></i> Latest Ideas';
                    ?>
                </h4>
                
                <?php if (!empty($ideas)): ?>
                    <?php foreach ($ideas as $idea): ?>
                        <div class="card mb-3 shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title text-primary fw-bold"><?php echo htmlspecialchars($idea['title']); ?></h5>
                                    <div>
                                        <span class="badge bg-info text-dark bg-opacity-25 border border-info me-1"><?php echo htmlspecialchars($idea['category_name']); ?></span>
                                        <?php if(!empty($idea['year_name'])): ?>
                                            <span class="badge bg-warning text-dark bg-opacity-25 border border-warning"><?php echo htmlspecialchars($idea['year_name']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <h6 class="card-subtitle mb-2 text-muted small d-flex align-items-center mt-2">
                                    By: <strong class="ms-1 text-dark"><?php echo $idea['is_anonymous'] ? 'Anonymous' : htmlspecialchars($idea['fullname']); ?></strong>
                                    <?php if (!$idea['is_anonymous'] && !empty($idea['author_avatar'])): ?><img src="<?php echo htmlspecialchars($idea['author_avatar']); ?>" class="rounded-circle ms-2" width="20" height="20" style="object-fit:cover;"><?php endif; ?>
                                    <span class="mx-2">•</span> <i class="bi bi-clock me-1"></i> <?php echo date("d/m/Y H:i", strtotime($idea['created_at'])); ?>
                                </h6>
                                <p class="card-text text-secondary mt-3"><?php echo nl2br(htmlspecialchars($idea['content'])); ?></p>
                                
                                <?php if (!empty($idea['documents'])): ?>
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <h6 class="fw-bold text-muted small"><i class="bi bi-paperclip"></i> Attachments:</h6>
                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            <?php foreach ($idea['documents'] as $doc): ?>
                                                <?php 
                                                    $ext = strtolower(pathinfo($doc['file_path'], PATHINFO_EXTENSION));
                                                    $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                ?>
                                                <?php if ($is_image): ?>
                                                    <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank"><img src="<?php echo htmlspecialchars($doc['file_path']); ?>" class="attachment-img" style="height: 60px; width: auto; border-radius: 5px; border: 1px solid #ddd;"></a>
                                                <?php else: ?>
                                                    <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary d-flex align-items-center"><i class="bi bi-file-earmark-text me-2"></i> File .<?php echo strtoupper($ext); ?></a>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>  
                                    </div>
                                <?php endif; ?>
                                
                                <hr class="text-muted opacity-25">
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="btn-group" role="group">
                                        <button onclick="voteIdea(<?php echo $idea['idea_id']; ?>, 1)" class="btn btn-outline-success btn-sm"><i class="bi bi-hand-thumbs-up-fill"></i> <span id="upvote-count-<?php echo $idea['idea_id']; ?>"><?php echo $idea['upvotes'] ?? 0; ?></span></button>
                                        <button onclick="voteIdea(<?php echo $idea['idea_id']; ?>, -1)" class="btn btn-outline-danger btn-sm"><i class="bi bi-hand-thumbs-down-fill"></i> <span id="downvote-count-<?php echo $idea['idea_id']; ?>"><?php echo $idea['downvotes'] ?? 0; ?></span></button>
                                    </div>
                                    <a href="idea_details.php?id=<?php echo $idea['idea_id']; ?>" class="btn btn-primary btn-sm px-4 rounded-pill">Comments / Details <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (isset($total_pages) && $total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php
                                    // The pagination link function automatically retrieves all current parameters (including global_year)
                                    function getPageLink($pageNum) { $params = $_GET; $params['page'] = $pageNum; return "index.php?" . http_build_query($params); }
                                    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                    if ($currentPage < 1) $currentPage = 1;
                                ?>
                                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="<?php echo getPageLink($currentPage - 1); ?>">Previous</a></li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($currentPage == $i) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo getPageLink($i); ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($currentPage >= $total_pages) ? 'disabled' : ''; ?>"><a class="page-link" href="<?php echo getPageLink($currentPage + 1); ?>">Next</a></li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="alert alert-info text-center py-5">
                        <i class="bi bi-inbox fs-1"></i><br>
                        <?php if($current_tab == 'my_ideas'): ?> You haven't posted any ideas in this academic year yet.
                        <?php else: ?> No ideas found for this academic year matching your criteria. <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>