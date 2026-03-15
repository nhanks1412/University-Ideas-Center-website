<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - University Idea Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="auth-body">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="card auth-card p-4">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-dark">Create Account</h3>
                        <p class="text-muted">Join the community</p>
                    </div>

                    <form novalidate>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" id="fullname" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">University Email</label>
                            <input type="email" id="email" class="form-control" placeholder="name@university.edu.vn" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select class="form-select" id="role" required>
                                    <option value="" selected disabled>Select role...</option>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo $r['role_id']; ?>">
                                            <?php echo htmlspecialchars($r['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-select" id="department" required>
                                    <option value="" selected disabled>Select dept...</option>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?php echo $d['department_id']; ?>">
                                            <?php echo htmlspecialchars($d['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" id="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" id="confirm-password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-4 form-check">
                            <input class="form-check-input" type="checkbox" id="terms">
                            <label class="form-check-label small" for="terms">
                                I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a>.
                            </label>
                        </div>
                        
                        <button type="submit" id="btnRegister" class="btn btn-success w-100 fw-bold py-2">REGISTER ACCOUNT</button>
                    </form>

                    <div class="text-center mt-4 border-top pt-3">
                        <p class="small mb-0">Already have an account? <a href="login.php" class="fw-bold text-decoration-none">Login instead</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector("form").addEventListener("submit", async function (e) {
            e.preventDefault();
            
            const btnReg = document.getElementById("btnRegister");
            const fullname = document.getElementById("fullname").value.trim();
            const email = document.getElementById("email").value.trim();
            const role = document.getElementById("role").value;
            const dept = document.getElementById("department").value;
            const pass = document.getElementById("password").value;
            const confirm = document.getElementById("confirm-password").value;
            const terms = document.getElementById("terms").checked;

            // Validation
            if (!email.toLowerCase().endsWith("@gmail.com")) { 
                alert("⚠️ Invalid Email Domain! Must end with @gmail.com"); 
                return; 
            }
            if (!role) { 
                alert("⚠️ Please select your Role!"); 
                return; 
            }
            if (!dept) { 
                alert("⚠️ Please select a Department!"); 
                return; 
            }
            if (!terms) { 
                alert("⚠️ Please agree to the Terms and Conditions!"); 
                return; 
            }
            if (pass !== confirm) { 
                alert("⚠️ Passwords do not match!"); 
                return; 
            }
            if (pass.length < 8 || !/[A-Z]/.test(pass)) { 
                alert("⚠️ Weak Password! Minimum 8 characters and 1 uppercase letter required."); 
                return; 
            }

            // Change button text to processing state
            btnReg.innerHTML = "Processing... Please wait";
            btnReg.disabled = true;

            try {
                // Send role_id and other fields to the register API
                const res = await fetch("register.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ 
                        fullname: fullname, 
                        email: email, 
                        password: pass, 
                        department_id: dept,
                        role_id: role 
                    }),
                });
                
                const data = await res.json();
                
                if (res.ok) { 
                    alert("✅ " + data.message); 
                    window.location.href = "login.php"; 
                } else { 
                    alert("⚠️ " + data.message); 
                    btnReg.innerHTML = "REGISTER ACCOUNT";
                    btnReg.disabled = false;
                }
            } catch (err) { 
                console.error(err); 
                alert("❌ System Error occurred. Please try again later."); 
                btnReg.innerHTML = "REGISTER ACCOUNT";
                btnReg.disabled = false;
            }
        });
    </script>

    <script src="js/script.js"></script> 
</body>
</html>