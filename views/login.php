<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - University Idea Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="auth-body">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                
                <div class="card auth-card p-4">
                    <div class="text-center mb-4">
                        <img src="img/greenwichlogo.png" width="200" class="mb-2" alt="Logo">
                        <h3 class="fw-bold text-dark mt-2">Welcome Back</h3>
                        <p class="text-muted">Login to continue</p>
                    </div>

                    <form novalidate>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" placeholder="name@university.edu.vn" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" id="password" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">LOGIN</button>
                    </form>

                    <div class="text-center mt-4 border-top pt-3">
                        <p class="small mb-0">
                            Don't have an account?
                            <a href="register.php" class="fw-bold text-decoration-none">Register here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const loginForm = document.querySelector("form");
        loginForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;

            if (email === "") { alert("⚠️ Please enter your Email!"); return; }
            if (!email.toLowerCase().endsWith("@gmail.com")) {
                alert("⚠️ Please use your official university email (@gmail.com)."); return;
            }
            if (password === "") { alert("⚠️ Please enter your Password!"); return; }

            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.innerText = "Checking..."; submitBtn.disabled = true;

            try {
                const response = await fetch("login.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ email: email, password: password }),
                });
                const data = await response.json();

                if (response.ok && data.status === "success") {
                    window.location.href = "index.php";
                } else {
                    alert("⚠️ Login Failed: " + (data.message || "Unknown error"));
                }
            } catch (error) {
                console.error("Error:", error);
                alert("❌ System Error.");
            } finally {
                submitBtn.innerText = "LOGIN"; submitBtn.disabled = false;
            }
        });
    </script>
    <script src="js/script.js"></script> 

    <script>
        const loginForm = document.querySelector("form");
    </script>
</body>
</html>