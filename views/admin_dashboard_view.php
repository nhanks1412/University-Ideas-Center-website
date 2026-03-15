<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - University Ideas Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark shadow mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-shield-lock-fill text-warning"></i> ADMIN PORTAL</a>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-light btn-sm">Home</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
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

        <?php $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'users'; ?>

        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link <?php echo ($active_tab == 'users') ? 'active fw-bold' : 'fw-bold'; ?>" data-bs-toggle="tab" data-bs-target="#users">
                    <i class="bi bi-people-fill"></i> User Management
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?php echo ($active_tab == 'stats') ? 'active fw-bold' : 'fw-bold'; ?>" data-bs-toggle="tab" data-bs-target="#stats">
                    <i class="bi bi-bar-chart-line-fill"></i> System Statistics & Academic Year
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?php echo ($active_tab == 'terms') ? 'active fw-bold' : 'fw-bold'; ?>" data-bs-toggle="tab" data-bs-target="#terms">
                    <i class="bi bi-file-earmark-text"></i> Terms & Conditions
                </button>
            </li>
        </ul>

        <div class="tab-content">
            
            <div class="tab-pane fade <?php echo ($active_tab == 'users') ? 'show active' : ''; ?>" id="users">
                <div class="card border-0 shadow-sm"> 
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary">Account List</h5>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-plus-fill"></i> Add New</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Department</th> <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td class="ps-4">#<?php echo $u['user_id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width:30px; height:30px; font-size:12px;">
                                                    <?php echo substr($u['fullname'],0,1); ?>
                                                </div>
                                                <?php echo htmlspecialchars($u['fullname']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><span class="badge <?php echo ($u['role_id']==1)?'bg-danger':(($u['role_id']==4)?'bg-primary':'bg-info text-dark'); ?>"><?php echo htmlspecialchars($u['role_name'] ?? 'N/A'); ?></span></td>
                                        
                                        <td>
                                            <?php if(!empty($u['dept_name'])): ?>
                                                <span class="badge bg-light text-dark border border-secondary"><?php echo htmlspecialchars($u['dept_name']); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border border-light fst-italic">No Dept</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#roleModal<?php echo $u['user_id']; ?>"><i class="bi bi-pencil-square"></i></button>
                                            <button class="btn btn-sm btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#passModal<?php echo $u['user_id']; ?>"><i class="bi bi-key"></i></button>
                                            <?php if($u['user_id'] != $_SESSION['user_id']): ?><button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $u['user_id']; ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade <?php echo ($active_tab == 'stats') ? 'show active' : ''; ?>" id="stats">
                <div class="card border-0 shadow-sm mb-5">
                    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-globe"></i> Global Academic Years</h5>
                        <button class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addGlobalYearModal"><i class="bi bi-plus-lg"></i> Add New Year</button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr><th class="ps-4">Academic Year Name</th><th>Start Date</th><th>End Date</th><th>Status (On Index)</th><th class="text-end pe-4">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($global_years as $gy): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?php echo htmlspecialchars($gy['year_name']); ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($gy['start_date'])); ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($gy['end_date'])); ?></td>
                                    <td>
                                        <?php if($gy['is_active']): ?><span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Current Active</span>
                                        <?php else: ?><span class="badge bg-secondary opacity-50">Hidden</span><?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if(!$gy['is_active']): ?><a href="admin_actions.php?action=set_active_global_year&id=<?php echo $gy['global_year_id']; ?>" class="btn btn-sm btn-outline-success me-1" onclick="return confirm('Make this the current active year visible to all users?')"><i class="bi bi-check2-square"></i> Set Active</a><?php endif; ?>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editGlobalYearModal<?php echo $gy['global_year_id']; ?>"><i class="bi bi-pencil-square"></i> Edit</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr class="mb-5 text-muted">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold text-primary mb-0"><i class="bi bi-bar-chart-fill"></i> System Statistics</h4>
                    <form action="admin_dashboard.php" method="GET" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="tab" value="stats">
                        <label class="fw-bold text-muted mb-0 text-nowrap">View Data For:</label>
                        <select name="view_year" class="form-select border-primary fw-bold text-primary" onchange="this.form.submit()">
                            <?php foreach($global_years as $gy): ?>
                                <option value="<?php echo $gy['global_year_id']; ?>" <?php echo ($view_year_id == $gy['global_year_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($gy['year_name']); ?> <?php echo $gy['is_active'] ? '(Current)' : ''; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center mb-4">
                    <i class="bi bi-calendar-check-fill fs-3 me-3 text-primary"></i>
                    <div>
                        <h5 class="mb-0 text-dark">Data tracking for: <strong><?php echo htmlspecialchars($current_view_year['year_name'] ?? 'N/A'); ?></strong></h5>
                        <p class="mb-0 text-muted">From <b><?php echo date("d/m/Y", strtotime($g_start)); ?></b> to <b><?php echo date("d/m/Y", strtotime($g_end)); ?></b></p>
                    </div>
                    <div class="ms-auto text-end">
                        <h2 class="fw-bold text-primary mb-0"><?php echo $total_ideas_in_year; ?></h2>
                        <small class="text-muted text-uppercase fw-bold">Total Ideas Submitted</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5 mb-4"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white py-3"><h5 class="fw-bold mb-0">📊 Contribution Rate by Department</h5></div><div class="card-body"><canvas id="adminDeptChart"></canvas></div></div></div>
                    <div class="col-md-7 mb-4"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-white py-3"><h5 class="fw-bold mb-0">📈 Submission Growth (Daily)</h5></div><div class="card-body"><canvas id="adminTimeChart"></canvas></div></div></div>
                </div>
            </div>

            <div class="tab-pane fade <?php echo ($active_tab == 'terms') ? 'show active' : ''; ?>" id="terms">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-primary mb-0"><i class="bi bi-file-earmark-text"></i> Edit Terms & Conditions</h5>
                    </div>
                    <div class="card-body">
                        <form action="admin_actions.php" method="POST">
                            <input type="hidden" name="action" value="update_terms">
                            <div class="mb-4">
                                <textarea name="terms_content" id="editor" class="form-control" rows="15"><?php echo $current_terms; ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-info text-white fw-bold px-4 shadow-sm" onclick="previewTerms()">
                                    <i class="bi bi-eye-fill"></i> Preview
                                </button>
                                <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm">
                                    <i class="bi bi-save-fill"></i> Save & Publish
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="previewTermsModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-eye"></i> Preview: Terms & Conditions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="p-4 bg-white shadow-sm rounded border" id="previewContentContainer">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close Preview</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="admin_actions.php" method="POST" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Create New Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_user">
                    <div class="mb-2"><label>Full Name</label><input type="text" name="fullname" class="form-control" required></div>
                    <div class="mb-2"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-2"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="row">
                        <div class="col-6"><label>Role</label><select name="role_id" class="form-select"><?php foreach($roles as $r){echo "<option value='{$r['role_id']}'>{$r['name']}</option>";} ?></select></div>
                        <div class="col-6"><label>Dept</label><select name="department_id" class="form-select"><?php foreach($depts as $d){echo "<option value='{$d['department_id']}'>{$d['name']}</option>";} ?></select></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Create Now</button></div>
            </form>
        </div>
    </div>
    
    <?php foreach ($users as $u): ?>
        <div class="modal fade" id="roleModal<?php echo $u['user_id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <form action="admin_actions.php" method="POST" class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Edit Role & Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                        
                        <div class="mb-3">
                            <label class="fw-bold form-label">Role</label>
                            <select name="role_id" class="form-select">
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?php echo $r['role_id']; ?>" <?php echo ($u['role_id'] == $r['role_id']) ? 'selected' : ''; ?>><?php echo $r['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-bold form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">-- No Department --</option>
                                <?php foreach ($depts as $d): ?>
                                    <option value="<?php echo $d['department_id']; ?>" <?php echo ($u['department_id'] == $d['department_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="passModal<?php echo $u['user_id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <form action="admin_actions.php" method="POST" class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Change Password</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reset_password">
                        <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                        <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-warning">Save</button></div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="modal fade" id="addGlobalYearModal" tabindex="-1"><div class="modal-dialog"><form action="admin_actions.php" method="POST" class="modal-content"><div class="modal-header"><h5 class="modal-title">Create New Academic Year</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="add_global_year"><div class="mb-3"><label class="fw-bold">Academic Year Name</label><input type="text" name="year_name" class="form-control" placeholder="e.g. Fall 2026 - Fall 2027" required></div><div class="row"><div class="col-6 mb-3"><label class="fw-bold text-success">Start Date</label><input type="date" name="start_date" class="form-control" required></div><div class="col-6 mb-3"><label class="fw-bold text-danger">End Date</label><input type="date" name="end_date" class="form-control" required></div></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div></form></div></div>
    <?php foreach($global_years as $gy): ?>
    <div class="modal fade" id="editGlobalYearModal<?php echo $gy['global_year_id']; ?>" tabindex="-1"><div class="modal-dialog"><form action="admin_actions.php" method="POST" class="modal-content"><div class="modal-header"><h5 class="modal-title">Edit: <?php echo htmlspecialchars($gy['year_name']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="action" value="edit_global_year"><input type="hidden" name="global_year_id" value="<?php echo $gy['global_year_id']; ?>"><div class="mb-3"><label class="fw-bold">Academic Year Name</label><input type="text" name="year_name" class="form-control" value="<?php echo htmlspecialchars($gy['year_name']); ?>" required></div><div class="row"><div class="col-6 mb-3"><label class="fw-bold text-success">Start Date</label><input type="date" name="start_date" class="form-control" value="<?php echo $gy['start_date']; ?>" required></div><div class="col-6 mb-3"><label class="fw-bold text-danger">End Date</label><input type="date" name="end_date" class="form-control" value="<?php echo $gy['end_date']; ?>" required></div></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div></form></div></div>
    <?php endforeach; ?>

    <form id="deleteForm" action="admin_actions.php" method="POST"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" id="deleteUserId"></form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        // UPDATE SCRIPT FOR CKEDITOR & PREVIEW
        let termsEditorInstance; 

        ClassicEditor
            .create(document.querySelector('#editor'))
            .then(editor => {
                termsEditorInstance = editor;
            })
            .catch(error => { 
                console.error(error); 
            });
        
        function previewTerms() {
            if (termsEditorInstance) {
                const contentHTML = termsEditorInstance.getData();
                document.getElementById('previewContentContainer').innerHTML = contentHTML;
                var previewModal = new bootstrap.Modal(document.getElementById('previewTermsModal'));
                previewModal.show();
            }
        }
        
        function confirmDelete(id) { 
            if(confirm('⚠️ Are you sure you want to delete this USER?')) { 
                document.getElementById('deleteUserId').value = id; 
                document.getElementById('deleteForm').submit(); 
            } 
        }
        
        // JS DATA FOR ADMIN CHART
        const deptLabels = <?php echo json_encode($chart_dept_labels); ?>; 
        const deptData = <?php echo json_encode($chart_dept_data); ?>;
        const timeLabels = <?php echo json_encode($chart_time_labels); ?>; 
        const timeData = <?php echo json_encode($chart_time_data); ?>;
        
        if (deptLabels.length > 0) {
            new Chart(document.getElementById('adminDeptChart'), { 
                type: 'pie', 
                data: { labels: deptLabels, datasets: [{ data: deptData, backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'] }] } 
            });
        }

        if (timeLabels.length > 0) {
            new Chart(document.getElementById('adminTimeChart'), { 
                type: 'line', 
                data: { labels: timeLabels, datasets: [{ label: 'Number of Ideas', data: timeData, borderColor: '#36A2EB', tension: 0.3, fill: true, backgroundColor: 'rgba(54, 162, 235, 0.2)' }] }, 
                options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } } 
            });
        }
    </script>
</body>
</html>