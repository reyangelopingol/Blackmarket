<?php
session_start();
include "db.php";

// Define the admin registration code
define('ADMIN_REGISTRATION_CODE', 'BLACKMARKET2024');

$error = '';
$success = '';

if (isset($_POST['register_admin'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $admin_code = $_POST['admin_code'];
    
    // Verify admin code
    if ($admin_code !== ADMIN_REGISTRATION_CODE) {
        $error = "❌ Invalid admin registration code!";
    } else {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "❌ Email already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new admin
            $sql = "INSERT INTO users (fullname, email, password, is_admin) 
                    VALUES ('$fullname', '$email', '$hashed_password', 1)";
            
            if (mysqli_query($conn, $sql)) {
                $admin_id = mysqli_insert_id($conn);
                
                // Log activity
                $ip = $_SERVER['REMOTE_ADDR'];
                mysqli_query($conn, "INSERT INTO activity_log (admin_id, action, details, ip_address) 
                                     VALUES ('$admin_id', 'admin_registered', 'New admin account created: $email', '$ip')");
                
                $success = "✅ Admin account created successfully! You can now login.";
                
                // Auto-login after registration
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_name'] = $fullname;
                $_SESSION['is_admin'] = 1;
                
                // Redirect to dashboard after 2 seconds
                header("refresh:2;url=admin_dashboard.php");
            } else {
                $error = "❌ Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Registration - BlackMarket</title>
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
        .register-box { 
            background: #1a1a1a; 
            padding: 2.5rem; 
            border-radius: 10px; 
            width: 100%; 
            max-width: 400px; 
            border: 2px solid #c0392b; 
            box-shadow: 0 0 40px rgba(192,57,43,0.15);
        }
        .register-box h2 { 
            color: #fff; 
            font-family: 'Oswald', sans-serif; 
            margin-bottom: 0.5rem; 
        }
        .register-box .subtitle {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }
        .register-box label { 
            color: #aaa; 
            font-size: 0.85rem; 
        }
        .register-box input { 
            background: #0a0a0a; 
            border: 1px solid #333; 
            color: #fff; 
        }
        .register-box input:focus { 
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
        .success { 
            color: #2ecc71; 
            font-size: 0.9rem; 
            margin-bottom: 1rem; 
            padding: 0.75rem;
            background: rgba(46,204,113,0.1);
            border-radius: 6px;
            border-left: 3px solid #2ecc71;
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
        .code-hint {
            color: #444;
            font-size: 0.75rem;
            text-align: center;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #0a0a0a;
            border-radius: 6px;
            border: 1px dashed #333;
        }
        .code-hint span {
            color: #c0392b;
            font-family: monospace;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <span class="admin-badge">🔐 Admin Registration</span>
        <h2>Black<span style="color:#c0392b;">Market</span></h2>
        <p class="subtitle">Create Admin Account</p>
        
        <?php if ($error): ?>
            <div class="error"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?></div>
            <p class="text-center text-white-50 small mt-2">Redirecting to dashboard...</p>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="fullname" class="form-control" placeholder="Admin User" required value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="admin@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
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
            <button type="submit" name="register_admin" class="btn btn-red w-100">
                <i class="bi bi-person-plus me-2"></i>Register Admin
            </button>
        </form>
        
        <div class="code-hint mt-3">
            💡 Admin Registration Code: <span>BLACKMARKET2024</span>
        </div>
        
        <div class="mt-3 text-center">
            <a href="admin_login.php" class="back-link"><i class="bi bi-arrow-left me-1"></i>Back to Login</a>
            <span class="text-muted mx-2">|</span>
            <a href="index.php" class="back-link"><i class="bi bi-house me-1"></i>Store</a>
        </div>
    </div>
</body>
</html>