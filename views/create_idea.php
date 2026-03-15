<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Idea - Uni-Idea Hub</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4 navbar-custom">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="index.php">
                <i class="bi bi-arrow-left-circle-fill"></i> Back to Home
            </a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <h2 class="fw-bold mb-4 text-dark"><i class="bi bi-lightbulb-fill text-warning"></i> Contribute Idea</h2>

                <?php if ($current_academic_year): ?>
                    <div class="p-4 mb-4 banner-campaign shadow-sm d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="fw-bold mb-1"><i class="bi bi-megaphone-fill"></i> Happening Now: <?php echo htmlspecialchars($current_academic_year['name']); ?></h4>
                            <p class="mb-0 opacity-75">Please submit your idea before <strong class="text-warning border-bottom"><?php echo date("d/m/Y", strtotime($current_academic_year['closure_date'])); ?></strong></p>
                        </div>
                        <i class="bi bi-calendar-check display-4 opacity-50"></i>
                    </div>
                <?php else: ?>
                    <div class="p-4 mb-4 banner-free shadow-sm d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="fw-bold mb-1"><i class="bi bi-stars"></i> Free Idea Space</h4>
                            <p class="mb-0 opacity-75">No specific campaign currently, but we are always listening!</p>
                        </div>
                        <i class="bi bi-send display-4 opacity-50"></i>
                    </div>
                <?php endif; ?>

                <div class="card form-card shadow">
                    <div class="card-body p-4">
                        
                        <form id="ideaForm" action="submit_idea.php" method="POST" enctype="multipart/form-data">
                            
                            <input type="hidden" name="academic_year_id" value="<?php echo $current_academic_year ? $current_academic_year['academic_year_id'] : ''; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Idea Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control form-control-lg" placeholder="Enter a concise title..." required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="" selected disabled>-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Detailed Content <span class="text-danger">*</span></label>
                                <textarea name="content" class="form-control" rows="6" placeholder="Describe your idea..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Attachments</label>
                                <input type="file" name="documents[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                <div class="form-text">Supports images and documents. Max 5MB/file.</div>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_anonymous" id="anonymousCheck">
                                    <label class="form-check-label fw-bold text-secondary" for="anonymousCheck">
                                        <i class="bi bi-incognito"></i> Post anonymously
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" required id="termsCheck">
                                    <label class="form-check-label small" for="termsCheck">
                                        I agree to the <a href="terms.php" target="_blank">Terms of Use</a>.
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" id="btnSubmit" class="btn btn-primary btn-lg fw-bold">
                                    <i class="bi bi-paper-plane-fill"></i> SUBMIT IDEA
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>

    <script>
        document.getElementById("ideaForm").addEventListener("submit", function(e) {
            // Kiểm tra xem các ô required đã được nhập đủ chưa
            if (!this.checkValidity()) {
                return; // Nếu chưa đủ, để trình duyệt tự báo lỗi, không khóa nút
            }

            // Nếu dữ liệu đã hợp lệ, bắt đầu khóa nút và hiện Loading
            let btn = document.getElementById("btnSubmit");
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            btn.disabled = true;
            btn.classList.add("opacity-75");
            btn.style.cursor = "not-allowed";
        });
    </script>
</body>
</html>