<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QA Manager Dashboard - University Ideas Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary shadow mb-4" style="background: linear-gradient(to right, #1a2980, #26d0ce);">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-briefcase-fill"></i> QA MANAGER
            </a>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-light btn-sm">Home</a>
                <a href="logout.php" class="btn btn-warning btn-sm fw-bold text-dark">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 pb-5">

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                ✅ <?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                ❌ <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'statistics'; ?>

        <ul class="nav nav-tabs mb-4" id="qaTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link fw-bold <?php echo ($active_tab == 'statistics') ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#statistics">
                    <i class="bi bi-bar-chart-fill"></i> Statistics
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold <?php echo ($active_tab == 'departments') ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#departments">
                    <i class="bi bi-building"></i> Departments
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold <?php echo ($active_tab == 'categories') ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#categories">
                    <i class="bi bi-tags-fill"></i> Categories
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold <?php echo ($active_tab == 'academic') ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#academic">
                    <i class="bi bi-calendar-event-fill"></i> Events
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold text-danger <?php echo ($active_tab == 'posts') ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#posts">
                    <i class="bi bi-shield-check"></i> Moderation
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <div class="tab-pane fade <?php echo ($active_tab == 'statistics') ? 'show active' : ''; ?>" id="statistics">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3"><h5 class="fw-bold mb-0 text-primary"><i class="bi bi-pie-chart-fill"></i> Ideas by Department</h5></div>
                            <div class="card-body d-flex justify-content-center"><div style="width: 80%;"><canvas id="qaDeptChart"></canvas></div></div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3"><h5 class="fw-bold mb-0 text-danger"><i class="bi bi-bar-chart-line-fill"></i> Top 5 Most Voted Ideas</h5></div>
                            <div class="card-body"><canvas id="qaTopIdeasChart"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade <?php echo ($active_tab == 'departments') ? 'show active' : ''; ?>" id="departments">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between">
                        <h5 class="fw-bold text-primary mb-0">Department Management</h5>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addDeptModal">
                            <i class="bi bi-plus-lg"></i> Create Department
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Department Name</th>
                                    <th>Created At</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departments as $dept): ?>
                                <tr>
                                    <td class="ps-4">#<?php echo $dept['department_id']; ?></td>
                                    <td class="fw-bold text-primary"><?php echo htmlspecialchars($dept['name']); ?></td>
                                    <td class="text-muted"><?php echo date("d/m/Y", strtotime($dept['created_at'])); ?></td>
                                    <td class="text-end pe-4">
                                        <a href="mana_actions.php?action=delete_department&id=<?php echo $dept['department_id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('⚠️ Only empty departments can be deleted. Are you sure?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade <?php echo ($active_tab == 'categories') ? 'show active' : ''; ?>" id="categories">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between">
                        <h5 class="fw-bold text-primary mb-0">Category List</h5>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addCatModal"><i class="bi bi-plus-lg"></i> Add New</button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr><th class="ps-4">ID</th><th>Category Name</th><th>Description</th><th class="text-end pe-4">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="ps-4">#<?php echo $cat['category_id']; ?></td><td class="fw-bold"><?php echo htmlspecialchars($cat['name']); ?></td><td class="text-muted small"><?php echo htmlspecialchars($cat['description']); ?></td>
                                    <td class="text-end pe-4"><a href="mana_actions.php?action=delete_cat&id=<?php echo $cat['category_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('⚠️ Warning: Deleting this category will delete all ideas within it! Are you sure?')"><i class="bi bi-trash"></i> Delete</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade <?php echo ($active_tab == 'academic') ? 'show active' : ''; ?>" id="academic">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between">
                        <h5 class="fw-bold text-primary mb-0">Survey & Event Management</h5>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addYearModal"><i class="bi bi-calendar-plus"></i> Create New Event</button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr><th class="ps-4">Event Name / Type</th><th>Start Date</th><th>Submission Deadline</th><th>Final Closure</th><th class="text-end pe-4" style="width: 120px;">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($academic_years as $year): ?>
                                <tr>
                                    <td class="ps-4"><div class="fw-bold text-primary"><?php echo htmlspecialchars($year['name']); ?></div><small class="badge bg-light text-dark border"><?php echo htmlspecialchars($year['survey_type'] ?? 'General'); ?></small></td>
                                    <td><?php echo date("d/m/Y", strtotime($year['start_date'])); ?></td><td class="text-danger fw-bold"><?php echo date("d/m/Y", strtotime($year['closure_date'])); ?></td><td class="text-muted"><?php echo date("d/m/Y", strtotime($year['final_closure_date'])); ?></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#editYearModal<?php echo $year['academic_year_id']; ?>" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <a href="mana_actions.php?action=delete_year&id=<?php echo $year['academic_year_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this event?')" title="Delete"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade <?php echo ($active_tab == 'posts') ? 'show active' : ''; ?>" id="posts">
                
                <div class="card border-0 shadow-sm mb-3 bg-white">
                    <div class="card-body py-2 d-flex gap-3 align-items-center">
                        <span class="fw-bold text-muted"><i class="bi bi-funnel"></i> Filter Ideas:</span>
                        
                        <select id="filterDept" class="form-select form-select-sm w-auto" onchange="filterIdeas()">
                            <option value="all">-- All Departments --</option>
                            <?php foreach($departments as $d): ?>
                                <option value="<?php echo $d['department_id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select id="filterUser" class="form-select form-select-sm w-auto" onchange="filterIdeas()">
                            <option value="all">-- All Members --</option>
                            <?php foreach($users_list as $u): ?>
                                <option value="<?php echo $u['user_id']; ?>" data-dept="<?php echo $u['department_id']; ?>">
                                    <?php echo htmlspecialchars($u['fullname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <a href="export_data.php" class="btn btn-success btn-sm ms-auto fw-bold shadow-sm">
                            <i class="bi bi-file-earmark-zip-fill"></i> Export All Data (ZIP)
                        </a>
                        </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle" id="ideasTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Title</th>
                                    <th>Department</th>
                                    <th>Poster</th>
                                    <th>Date</th>
                                    <th>Votes</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ideas as $idea): ?>
                                <tr class="idea-row <?php echo !empty($idea['is_hidden']) ? 'table-secondary opacity-75' : ''; ?>" 
                                    data-dept="<?php echo $idea['department_id']; ?>" 
                                    data-user="<?php echo $idea['user_id']; ?>">
                                    
                                    <td class="ps-4">
                                        <a href="idea_details.php?id=<?php echo $idea['idea_id']; ?>" class="fw-bold text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($idea['title']); ?>
                                        </a>
                                        <?php if (!empty($idea['is_hidden'])): ?>
                                            <span class="badge bg-dark ms-1"><i class="bi bi-eye-slash-fill"></i> Hidden</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted"><?php echo substr(htmlspecialchars($idea['content']), 0, 50); ?>...</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">
                                            <?php echo htmlspecialchars($idea['department_name'] ?? 'No Dept'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $idea['is_anonymous'] ? '<span class="badge bg-secondary">Anonymous</span>' : htmlspecialchars($idea['fullname']); ?>
                                    </td>
                                    <td><?php echo date("d/m/y", strtotime($idea['created_at'])); ?></td>
                                    <td>👍 <?php echo $idea['upvotes'] ?? 0; ?> | 👎 <?php echo $idea['downvotes'] ?? 0; ?></td>
                                    <td class="text-end pe-4">
                                        <?php if (!empty($idea['is_hidden'])): ?>
                                            <a href="mana_actions.php?action=toggle_hide_idea&id=<?php echo $idea['idea_id']; ?>" class="btn btn-sm btn-dark me-1" title="Unhide post"><i class="bi bi-eye-fill"></i></a>
                                        <?php else: ?>
                                            <a href="mana_actions.php?action=toggle_hide_idea&id=<?php echo $idea['idea_id']; ?>" class="btn btn-sm btn-outline-dark me-1" title="Hide post"><i class="bi bi-eye-slash"></i></a>
                                        <?php endif; ?>
                                        <a href="mana_actions.php?action=delete_idea&id=<?php echo $idea['idea_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('🚫 WARNING: Delete permanently?')"><i class="bi bi-trash-fill"></i></a>
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
    
    <div class="modal fade" id="addDeptModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="mana_actions.php" method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Create Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_department">
                    <div class="mb-3">
                        <label>Department Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Marketing" required>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="addCatModal" tabindex="-1"><div class="modal-dialog"><form action="mana_actions.php" method="POST" class="modal-content"><div class="modal-header"><h5 class="modal-title">Add New Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="add_category"><div class="mb-3"><label>Category Name</label><input type="text" name="name" class="form-control" required></div><div class="mb-3"><label>Short Description</label><textarea name="description" class="form-control" rows="3"></textarea></div></div><div class="modal-footer"><button type="submit" class="btn btn-success">Save Category</button></div></form></div></div>
    
    <div class="modal fade" id="addYearModal" tabindex="-1"><div class="modal-dialog"><form action="mana_actions.php" method="POST" class="modal-content"><div class="modal-header"><h5 class="modal-title">Create New Event / Survey</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="add_academic_year"><div class="mb-3"><label class="fw-bold">Event Name</label><input type="text" name="name" class="form-control" placeholder="e.g. Green Innovation Contest 2026" required></div><div class="row mb-3"><div class="col-md-6"><label class="fw-bold text-primary">Survey Type</label><select name="survey_type" class="form-select"><option value="General Survey">General Survey</option><option value="Contest">Contest</option><option value="Seminar">Seminar</option><option value="Hackathon">Hackathon</option></select></div><div class="col-md-6"><label class="fw-bold">Start Date</label><input type="date" name="start_date" class="form-control" required></div></div><div class="mb-3"><label class="fw-bold">Description / Instructions</label><textarea name="description" class="form-control" rows="3"></textarea></div><div class="mb-3"><label class="fw-bold text-danger">Submission Deadline</label><input type="date" name="closure_date" class="form-control" required></div><div class="mb-3"><label class="fw-bold text-muted">Final Closure Date</label><input type="date" name="final_closure_date" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-success">Create Event</button></div></form></div></div>

    <?php foreach ($academic_years as $year): ?>
    <div class="modal fade" id="editYearModal<?php echo $year['academic_year_id']; ?>" tabindex="-1"><div class="modal-dialog"><form action="mana_actions.php" method="POST" class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit Event: <b><?php echo htmlspecialchars($year['name']); ?></b></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="update_academic_year"><input type="hidden" name="academic_year_id" value="<?php echo $year['academic_year_id']; ?>"><div class="mb-3"><label class="fw-bold">Event Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($year['name']); ?>" required></div><div class="row mb-3"><div class="col-md-6"><label class="fw-bold text-primary">Survey Type</label><select name="survey_type" class="form-select"><option value="General Survey" <?php echo ($year['survey_type'] == 'General Survey') ? 'selected' : ''; ?>>General Survey</option><option value="Contest" <?php echo ($year['survey_type'] == 'Contest') ? 'selected' : ''; ?>>Contest</option><option value="Seminar" <?php echo ($year['survey_type'] == 'Seminar') ? 'selected' : ''; ?>>Seminar</option><option value="Hackathon" <?php echo ($year['survey_type'] == 'Hackathon') ? 'selected' : ''; ?>>Hackathon</option></select></div><div class="col-md-6"><label class="fw-bold text-success">Start Date</label><input type="date" name="start_date" class="form-control" value="<?php echo $year['start_date']; ?>" required></div></div><div class="mb-3"><label class="fw-bold">Description</label><textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($year['description']); ?></textarea></div><div class="mb-3"><label class="fw-bold text-danger">Submission Deadline</label><input type="date" name="closure_date" class="form-control" value="<?php echo $year['closure_date']; ?>" required></div><div class="mb-3"><label class="fw-bold text-muted">Final Closure Date</label><input type="date" name="final_closure_date" class="form-control" value="<?php echo $year['final_closure_date']; ?>" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Save Changes</button></div></form></div></div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function filterIdeas() {
            const deptId = document.getElementById('filterDept').value;
            const userId = document.getElementById('filterUser').value;
            const rows = document.querySelectorAll('.idea-row');

            const userOptions = document.querySelectorAll('#filterUser option');
            userOptions.forEach(opt => {
                if (deptId === 'all' || opt.value === 'all' || opt.getAttribute('data-dept') === deptId) {
                    opt.style.display = 'block';
                } else {
                    opt.style.display = 'none';
                    if (opt.selected) document.getElementById('filterUser').value = 'all';
                }
            });

            rows.forEach(row => {
                const rowDept = row.getAttribute('data-dept');
                const rowUser = row.getAttribute('data-user');
                
                let matchDept = (deptId === 'all' || rowDept === deptId);
                let matchUser = (userId === 'all' || rowUser === userId);

                if (matchDept && matchUser) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>

    <script>
        const deptLabels = <?php echo json_encode($dept_labels); ?>;
        const deptData = <?php echo json_encode($dept_data); ?>;
        const ideaLabels = <?php echo json_encode($idea_labels); ?>;
        const ideaData = <?php echo json_encode($idea_data); ?>;

        new Chart(document.getElementById('qaDeptChart'), {
            type: 'doughnut',
            data: { labels: deptLabels, datasets: [{ data: deptData, backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'] }] }
        });

        new Chart(document.getElementById('qaTopIdeasChart'), {
            type: 'bar',
            data: { labels: ideaLabels, datasets: [{ label: 'Total Votes (Likes + Dislikes)', data: ideaData, backgroundColor: '#36A2EB', borderRadius: 5 }] },
            options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    </script>

    <script>
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                // Kiểm tra xem người dùng đã nhập đủ các trường bắt buộc (required) chưa
                if (!this.checkValidity()) {
                    return; // Nếu chưa đủ, để trình duyệt tự báo lỗi
                }

                // Tìm nút Submit nằm trong Form đang được bấm
                let btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    // Đổi trạng thái thành Processing và khóa nút lại
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                    btn.disabled = true;
                    btn.classList.add("opacity-75");
                    btn.style.cursor = "not-allowed";
                }
            });
        });
    </script>

</body>
</html>