<?php
session_start();
include "db.php";

// If already logged in as admin, redirect to admin dashboard
if (isset($_SESSION['admin_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if (isset($_POST['admin_login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' AND is_admin=1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['fullname'];
            $_SESSION['is_admin'] = 1;
            
            // Log admin login
            $ip = $_SERVER['REMOTE_ADDR'];
            mysqli_query($conn, "INSERT INTO activity_log (admin_id, action, details, ip_address) 
                                 VALUES ('{$user['id']}', 'admin_login', 'Admin logged in', '$ip')");
            
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Admin account not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - BlackMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background: #0a0a0a; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            font-family: 'Inter', sans-serif;
        }
        .admin-login-box { 
            background: #1a1a1a; 
            padding: 2.5rem; 
            border-radius: 10px; 
            width: 100%; 
            max-width: 400px; 
            border: 2px solid #c0392b; 
            box-shadow: 0 0 40px rgba(192,57,43,0.15);
        }
        .admin-login-box h2 { 
            color: #fff; 
            font-family: 'Oswald', sans-serif; 
            margin-bottom: 0.5rem; 
        }
        .admin-login-box .subtitle {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }
        .admin-login-box label { 
            color: #aaa; 
            font-size: 0.85rem; 
        }
        .admin-login-box input { 
            background: #0a0a0a; 
            border: 1px solid #333; 
            color: #fff; 
        }
        .admin-login-box input:focus { 
            border-color: #c0392b; 
            box-shadow: 0 0 0 0.2rem rgba(192,57,43,0.25); 
        }
        .btn-red { 
            background: #c0392b; 
            color: #fff; 
            border: none;
            padding: 0.6rem;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-red:hover { 
            background: #96281b; 
            color: #fff; 
        }
        .btn-outline-red {
            background: transparent;
            color: #c0392b;
            border: 1px solid #c0392b;
            padding: 0.6rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-outline-red:hover {
            background: #c0392b;
            color: #fff;
        }
        .error { 
            color: #e74c3c; 
            font-size: 0.9rem; 
            margin-bottom: 1rem; 
            padding: 0.75rem;
            background: rgba(231,76,60,0.1);
            border-radius: 6px;
            border-left: 3px solid #e74c3c;
        }
        .admin-badge {
            display: inline-block;
            background: #c0392b;
            color: #fff;
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }
        .back-link {
            color: #666;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #c0392b;
        }
        .register-toggle {
            color: #888;
            font-size: 0.85rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        .register-toggle:hover {
            color: #c0392b;
        }
        .code-hint {
            color: #444;
            font-size: 0.7rem;
            text-align: center;
            margin-top: 0.5rem;
        }
        .code-hint span {
            color: #666;
            font-family: monospace;
            background: #0a0a0a;
            padding: 0.1rem 0.4rem;
            border-radius: 3px;
            border: 1px solid #222;
        }
    </style>
</head>
<body>
    <div class="admin-login-box">
        <span class="admin-badge">🔐 Admin Access</span>
        <h2>Black<span style="color:#c0392b;">Market</span></h2>
        <p class="subtitle">Admin Control Panel</p>
        
        <?php if ($error): ?>
            <div class="error"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" id="loginForm">
            <div class="mb-3">
                <label class="form-label">Admin Email</label>
                <input type="email" name="email" class="form-control" placeholder="admin@blackmarket.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter admin password" required>
            </div>
            <button type="submit" name="admin_login" class="btn btn-red w-100">
                <i class="bi bi-shield-lock-fill me-2"></i>Login as Admin
            </button>
        </form>
        
        <div class="mt-3 text-center">
            <span class="register-toggle" onclick="toggleRegister()">
                <i class="bi bi-person-plus me-1"></i>Don't have admin access? Register here
            </span>
        </div>
        
        <!-- Register Form (Hidden by default) -->
        <div id="registerForm" style="display: none; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #2a2a2a;">
            <h6 class="text-white mb-3">📝 Register Admin Account</h6>
            <form method="POST" action="admin_register.php">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="fullname" class="form-control" placeholder="Admin User" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@example.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create a strong password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Admin Registration Code <span class="text-danger">*</span></label>
                    <input type="password" name="admin_code" class="form-control" placeholder="Enter the admin secret code" required>
                    <small class="text-muted">You need the admin secret code to register</small>
                </div>
                <button type="submit" name="register_admin" class="btn btn-outline-red w-100">
                    <i class="bi bi-person-plus me-2"></i>Register Admin
                </button>
            </form>
            <div class="code-hint mt-2">
                💡 Hint: The admin code is <span>BLACKMARKET2024</span>
            </div>
        </div>
        
        <div class="mt-3 text-center">
            <a href="index.php" class="back-link"><i class="bi bi-arrow-left me-1"></i>Back to Store</a>
        </div>
    </div>

    <script>
        function toggleRegister() {
            const registerForm = document.getElementById('registerForm');
            const loginForm = document.getElementById('loginForm');
            if (registerForm.style.display === 'none') {
                registerForm.style.display = 'block';
                loginForm.style.display = 'none';
            } else {
                registerForm.style.display = 'none';
                loginForm.style.display = 'block';
            }
        }
    </script>
</body>
</html>