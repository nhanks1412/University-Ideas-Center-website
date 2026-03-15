<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QA Coordinator - <?php echo htmlspecialchars($dept_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark shadow mb-4" style="background-color: #0056b3;">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-person-workspace"></i> QA COORDINATOR: <span class="text-warning"><?php echo mb_strtoupper($dept_name); ?></span>
            </a>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-light btn-sm">Home</a>
                <a href="logout.php" class="btn btn-danger btn-sm fw-bold">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                ✅ <?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                ❌ <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php $active_tab = (isset($_GET['view_user']) || (isset($_GET['tab']) && $_GET['tab'] == 'posts')) ? 'posts' : 'members'; ?>

        <ul class="nav nav-tabs mb-4" id="coordTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link fs-5 fw-bold <?php echo ($active_tab == 'members') ? 'active text-primary' : 'text-muted'; ?>" data-bs-toggle="tab" data-bs-target="#members">
                    <i class="bi bi-people-fill"></i> Department Members
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fs-5 fw-bold <?php echo ($active_tab == 'posts') ? 'active text-primary' : 'text-muted'; ?>" data-bs-toggle="tab" data-bs-target="#posts">
                    <i class="bi bi-file-text-fill"></i> Manage Posts
                </button>
            </li>
        </ul>

        <div class="tab-content">
            
            <div class="tab-pane fade <?php echo ($active_tab == 'members') ? 'show active' : ''; ?>" id="members">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold text-primary mb-0">Personnel List: <?php echo htmlspecialchars($dept_name); ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th class="text-center">Total Posts</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $m): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width:30px; height:30px; font-size:12px;">
                                                <?php echo substr($m['fullname'],0,1); ?>
                                            </div>
                                            <?php echo htmlspecialchars($m['fullname']); ?>
                                            <?php if($m['user_id'] == $_SESSION['user_id']): ?> <span class="badge bg-success ms-2">You</span> <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($m['email']); ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($m['role_name']); ?></span></td>
                                    <td class="text-center fw-bold fs-5 text-primary"><?php echo $m['total_posts']; ?></td>
                                    <td class="text-end pe-4">
                                        <a href="qa_dashboard.php?view_user=<?php echo $m['user_id']; ?>" class="btn btn-sm btn-outline-primary fw-bold">
                                            <i class="bi bi-filter-circle"></i> View Posts
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade <?php echo ($active_tab == 'posts') ? 'show active' : ''; ?>" id="posts">
                
                <div class="alert alert-primary shadow-sm border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-funnel-fill"></i> Showing posts by: <span class="text-danger border-bottom border-danger"><?php echo htmlspecialchars($selected_user_name); ?></span></h5>
                    <?php if (isset($_GET['view_user'])): ?>
                        <a href="qa_dashboard.php?tab=posts" class="btn btn-sm btn-light fw-bold text-primary shadow-sm"><i class="bi bi-x-circle"></i> Clear Filter (View All)</a>
                    <?php endif; ?>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Idea Title</th>
                                    <th>Author</th>
                                    <th>Date Posted</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($ideas)): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted fst-italic">This user/department has no posts yet.</td></tr>
                                <?php endif; ?>

                                <?php foreach ($ideas as $idea): ?>
                                <tr class="<?php echo !empty($idea['is_hidden']) ? 'table-secondary opacity-75' : ''; ?>">
                                    <td class="ps-4">
                                        <a href="idea_details.php?id=<?php echo $idea['idea_id']; ?>" class="fw-bold text-decoration-none text-dark" target="_blank">
                                            <?php echo htmlspecialchars($idea['title']); ?>
                                        </a>
                                        <?php if (!empty($idea['is_hidden'])): ?>
                                            <span class="badge bg-dark ms-2"><i class="bi bi-eye-slash-fill"></i> Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $idea['is_anonymous'] ? '<span class="badge bg-secondary">Anonymous</span>' : htmlspecialchars($idea['fullname']); ?>
                                    </td>
                                    <td><?php echo date("M d, Y H:i", strtotime($idea['created_at'])); ?></td>
                                    <td class="text-end pe-4">
                                        <?php if (!empty($idea['is_hidden'])): ?>
                                            <a href="qa_actions.php?action=toggle_hide_idea&id=<?php echo $idea['idea_id']; ?>" class="btn btn-sm btn-success me-1" title="Unhide Post"><i class="bi bi-eye-fill"></i></a>
                                        <?php else: ?>
                                            <a href="qa_actions.php?action=toggle_hide_idea&id=<?php echo $idea['idea_id']; ?>" class="btn btn-sm btn-outline-dark me-1" title="Hide Post"><i class="bi bi-eye-slash"></i></a>
                                        <?php endif; ?>
                                        <a href="qa_actions.php?action=delete_idea&id=<?php echo $idea['idea_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('⚠️ WARNING: Permanently delete this post?')" title="Delete Post"><i class="bi bi-trash-fill"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>