<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Uni-Idea Hub</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4 navbar-profile">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="index.php">
                <i class="bi bi-arrow-left-circle-fill"></i> Back to Home
            </a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-8"> 
                
                <?php if (isset($_GET['status'])): ?>
                    <?php if ($_GET['status'] == 'success'): ?>
                        <div class="alert alert-success alert-dismissible fade show">✅ Avatar changed successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php elseif ($_GET['status'] == 'cover_updated'): ?>
                        <div class="alert alert-success alert-dismissible fade show">🖼️ Cover photo changed successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php elseif ($_GET['status'] == 'bio_updated'): ?>
                        <div class="alert alert-success alert-dismissible fade show">📝 Bio updated successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php elseif ($_GET['status'] == 'pass_changed'): ?>
                        <div class="alert alert-success alert-dismissible fade show">🔑 Password changed successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php 
                            if ($_GET['error'] == 'wrong_curr_pass') echo "⚠️ Incorrect current password!";
                            elseif ($_GET['error'] == 'pass_mismatch') echo "⚠️ New passwords do not match!";
                            elseif ($_GET['error'] == 'pass_weak') echo "⚠️ Password too weak (Need 8 chars + Uppercase)!";
                            elseif ($_GET['error'] == 'BioTooLong') echo "⚠️ Bio too long (>1000 characters)!";
                            else echo "⚠️ An error occurred!";
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card card-profile mt-4">
                    
                    <div class="profile-header" style="<?php echo $bg_style; ?>">
                        <form action="upload_cover.php" method="POST" enctype="multipart/form-data">
                            <label for="cover_file" class="btn btn-sm change-cover-btn shadow-sm" style="cursor: pointer;">
                                <i class="bi bi-camera"></i> Change Cover
                            </label>
                            <input type="file" name="cover_file" id="cover_file" accept="image/*" style="display: none;" onchange="this.form.submit()">
                        </form>
                        
                        <div class="avatar-circle">
                            <?php if (!empty($user['avatar_path']) && file_exists($user['avatar_path'])): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar_path']); ?>" alt="Avatar" class="avatar-img">
                            <?php else: ?>
                                <?php echo $first_letter; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="upload-avatar-btn">
                            <form action="upload_avatar.php" method="POST" enctype="multipart/form-data">
                                <label for="avatar_file" class="btn btn-sm btn-light border shadow-sm" style="cursor: pointer;">
                                    <i class="bi bi-person-bounding-box"></i> Change Avatar
                                </label>
                                <input type="file" name="avatar_file" id="avatar_file" accept="image/*" style="display: none;" onchange="this.form.submit()">
                            </form>
                        </div>
                    </div>

                    <div class="card-body pt-5 mt-5 text-center">
                        <h2 class="fw-bold mb-1 mt-3"><?php echo htmlspecialchars($user['fullname']); ?></h2>
                        <span class="badge bg-primary rounded-pill px-3 py-2 mb-3">
                            <?php echo htmlspecialchars($user['role_name']); ?>
                        </span>

                        <div class="text-start px-4">
                            <div class="bio-section position-relative">
                                <h6 class="fw-bold text-primary"><i class="bi bi-person-vcard"></i> Introduction</h6>
                                <p class="text-muted mb-0 fst-italic">
                                    <?php 
                                        if (!empty($user['bio'])) { echo nl2br(htmlspecialchars($user['bio'])); } 
                                        else { echo "This user hasn't written anything about themselves..."; }
                                    ?>
                                </p>
                                <button class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 mt-2 me-2" data-bs-toggle="modal" data-bs-target="#bioModal">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <div class="row text-start px-4">
                            <div class="col-md-6 mb-3">
                                <label class="info-label"><i class="bi bi-envelope-fill text-muted me-2"></i>Email</label>
                                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="info-label"><i class="bi bi-building-fill text-muted me-2"></i>Department</label>
                                <div class="info-value"><?php echo htmlspecialchars($user['department_name'] ?? 'Not updated'); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="info-label"><i class="bi bi-calendar-check-fill text-muted me-2"></i>Joined Date</label>
                                <div class="info-value"><?php echo date("d/m/Y", strtotime($user['created_at'])); ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="info-label"><i class="bi bi-lightbulb-fill text-warning me-2"></i>Contributions</label>
                                <div class="info-value text-success fw-bold"><?php echo $user['idea_count']; ?> ideas</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button class="btn btn-outline-warning fw-bold" data-bs-toggle="modal" data-bs-target="#passwordModal">
                                <i class="bi bi-key-fill"></i> Change Password
                            </button>
                            <a href="logout.php" class="btn btn-outline-danger fw-bold">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="bioModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Update Introduction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="update_bio.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <textarea class="form-control" name="bio" id="bioInput" rows="5" maxlength="1000" placeholder="Write something about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            <div class="text-end mt-1"><span class="small text-muted" id="charCount">0</span>/1000</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-warning"><i class="bi bi-shield-lock"></i> Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="change_password.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Min 8 chars, 1 Uppercase">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter new password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning fw-bold">Save New Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="js/script.js"></script>
    
    <script>
        const bioInput = document.getElementById('bioInput');
        const charCount = document.getElementById('charCount');
        if(bioInput){
            function updateCount() { charCount.textContent = bioInput.value.length; }
            updateCount();
            bioInput.addEventListener('input', updateCount);
        }
    </script>
    
</body>
</html>