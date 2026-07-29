<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NASACloud - Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="login-page">
    <div class="split-layout">
        <!-- Left Panel: Brand & Visuals -->
        <div class="split-left">
            <div class="mesh-bg"></div>
            <div class="brand-content">
                <h1><i class="ph-fill ph-cloud-moon"></i> NASACloud</h1>
                <p>Manage your premium coworking spaces from a single, powerful dashboard.</p>
            </div>
        </div>
        
        <!-- Right Panel: Login Form -->
        <div class="split-right">
            <div class="login-form-container">
                <div class="form-header">
                    <h2>Welcome Back</h2>
                    <p>Enter your secret password to securely access the system.</p>
                </div>
                
                <form action="dashboard.php" method="POST">
                    <div class="input-wrapper">
                        <i class="ph ph-lock-key input-icon"></i>
                        <input type="password" name="password" id="password" placeholder="Enter Password" required>
                        <button type="button" class="toggle-password" onclick="toggleVisibility()"><i class="ph ph-eye" id="eyeIcon"></i></button>
                    </div>
                    <button type="submit" class="login-btn">Sign In</button>
                </form>
                
                <div class="form-footer">
                    <p>Protected by NASACloud Security</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleVisibility() {
            const pwd = document.getElementById('password');
            const eye = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eye.classList.remove('ph-eye');
                eye.classList.add('ph-eye-slash');
            } else {
                pwd.type = 'password';
                eye.classList.remove('ph-eye-slash');
                eye.classList.add('ph-eye');
            }
        }
    </script>
</body>
</html>