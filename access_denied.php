<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Access Denied - BlackMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: #1a1a1a; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            font-family: 'Inter', sans-serif;
        }
        .error-box { 
            background: #2a2a2a; 
            padding: 3rem; 
            border-radius: 10px; 
            border: 1px solid #c0392b; 
            text-align: center;
            max-width: 500px;
        }
        .error-box .icon { font-size: 4rem; margin-bottom: 1rem; }
        .error-box h2 { color: #c0392b; font-family: 'Oswald', sans-serif; }
        .error-box p { color: #aaa; }
        .btn-red { background: #c0392b; color: #fff; border: none; padding: 0.5rem 2rem; }
        .btn-red:hover { background: #96281b; color: #fff; }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="icon">🚫</div>
        <h2>Access Denied</h2>
        <p>You don't have permission to access this page. This area is restricted to administrators only.</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-red">← Return to Store</a>
        </div>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <p class="mt-3 small text-muted">
                <a href="login.php" class="text-danger">Login</a> or 
                <a href="signup.php" class="text-danger">Register</a> as a regular user.
            </p>
        <?php endif; ?>
    </div>
</body>
</html>