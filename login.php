<?php
session_start();
include "db.php";

$error = '';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - BlackMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0a0a;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 30% 50%, rgba(192,57,43,0.08) 0%, transparent 60%),
                        radial-gradient(ellipse at 70% 80%, rgba(192,57,43,0.05) 0%, transparent 50%);
            animation: floatBg 20s ease-in-out infinite alternate;
            z-index: 0;
        }
        
        @keyframes floatBg {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(2%, 2%) rotate(3deg); }
        }
        
        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            padding: 1rem;
        }
        
        .login-box {
            background: rgba(26, 26, 26, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 3rem 2.5rem;
            border-radius: 24px;
            border: 1px solid rgba(192, 57, 43, 0.3);
            box-shadow: 0 25px 80px rgba(0,0,0,0.8), 0 0 60px rgba(192,57,43,0.05);
            transition: all 0.3s ease;
        }
        
        .login-box:hover {
            border-color: rgba(192, 57, 43, 0.5);
            box-shadow: 0 30px 90px rgba(0,0,0,0.9), 0 0 80px rgba(192,57,43,0.08);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1rem;
            box-shadow: 0 8px 30px rgba(192,57,43,0.3);
        }
        
        .logo-text {
            font-family: 'Oswald', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.06em;
        }
        
        .logo-text span { color: #c0392b; }
        
        .logo-sub {
            color: rgba(255,255,255,0.4);
            font-size: 0.8rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            margin-top: 0.25rem;
        }
        
        .login-title {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .login-subtitle {
            color: rgba(255,255,255,0.4);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        
        .form-floating-custom {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .form-floating-custom label {
            color: rgba(255,255,255,0.5);
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            display: block;
            transition: all 0.3s ease;
        }
        
        .form-floating-custom .input-wrapper {
            position: relative;
        }
        
        .form-floating-custom .input-wrapper .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.2);
            font-size: 1.1rem;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        
        .form-floating-custom input {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 3rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }
        
        .form-floating-custom input:focus {
            outline: none;
            border-color: #c0392b;
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 4px rgba(192,57,43,0.1);
        }
        
        .form-floating-custom input:focus + .input-icon,
        .form-floating-custom input:focus ~ .input-icon {
            color: #c0392b;
        }
        
        .form-floating-custom input::placeholder {
            color: rgba(255,255,255,0.2);
        }
        
        .btn-login {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.03em;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(192,57,43,0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.5s ease;
            pointer-events: none;
        }
        
        .btn-login:hover::after {
            opacity: 1;
        }
        
        .error {
            color: #e74c3c;
            font-size: 0.85rem;
            padding: 0.75rem 1rem;
            background: rgba(231,76,60,0.1);
            border-radius: 10px;
            border-left: 3px solid #e74c3c;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .error i {
            font-size: 1.1rem;
        }
        
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: rgba(255,255,255,0.4);
            font-size: 0.9rem;
        }
        
        .register-link a {
            color: #c0392b;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .register-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #c0392b;
            transition: width 0.3s ease;
        }
        
        .register-link a:hover::after {
            width: 100%;
        }
        
        .decorative-line {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .decorative-line::before,
        .decorative-line::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.08), transparent);
        }
        
        .decorative-line span {
            color: rgba(255,255,255,0.15);
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        
        @media (max-width: 480px) {
            .login-box { padding: 2rem 1.5rem; }
            .logo-text { font-size: 1.7rem; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="logo-section">
                <div class="logo-icon">🔫</div>
                <div class="logo-text">Black<span>Market</span></div>
                <div class="logo-sub">Premium Firearms</div>
            </div>
            
            <div class="login-title">Welcome Back</div>
            <div class="login-subtitle">Sign in to continue shopping</div>
            
            <?php if ($error): ?>
                <div class="error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-floating-custom">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                        <span class="input-icon"><i class="bi bi-envelope"></i></span>
                    </div>
                </div>
                
                <div class="form-floating-custom">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <span class="input-icon"><i class="bi bi-lock"></i></span>
                    </div>
                </div>
                
                <button type="submit" name="login" class="btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>
            
            <div class="decorative-line">
                <span>New here?</span>
            </div>
            
            <div class="register-link">
                Don't have an account? <a href="signup.php">Create Account</a>
            </div>
        </div>
    </div>
</body>
</html>