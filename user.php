<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);

// Get total order count
$total_orders_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = $user_id";
$total_result = mysqli_query($conn, $total_orders_query);
$total_orders = mysqli_fetch_assoc($total_result)['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Account - BlackMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; font-family: 'Inter', sans-serif; }
        .profile-box { background: #2a2a2a; padding: 2rem; border-radius: 10px; border: 1px solid #c0392b; }
        .btn-red { background: #c0392b; color: #fff; }
        .btn-red:hover { background: #96281b; color: #fff; }
        .btn-outline-red { border: 1px solid #c0392b; color: #c0392b; background: transparent; }
        .btn-outline-red:hover { background: #c0392b; color: #fff; }
        .btn-outline-light { border-color: #555; color: #aaa; }
        .btn-outline-light:hover { background: #333; color: #fff; }
        .status-badge { padding: 0.2rem 0.75rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #f39c12; color: #fff; }
        .status-processing { background: #3498db; color: #fff; }
        .status-shipped { background: #9b59b6; color: #fff; }
        .status-delivered { background: #27ae60; color: #fff; }
        .status-cancelled { background: #e74c3c; color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Black<span style="color:#c0392b;">Market</span></a>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-sm btn-outline-light">← Back to Store</a>
                <a href="logout.php" class="btn btn-sm btn-outline-red">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Profile Section -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-box">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3 class="text-white mb-1">👤 My Account</h3>
                            <p class="text-white-50 small">Welcome back, <strong class="text-white"><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong>!</p>
                        </div>
                        <a href="edit.php" class="btn btn-sm btn-red">✏️ Edit Profile</a>
                    </div>
                    
                    <hr class="border-secondary">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-white-50 small text-uppercase" style="letter-spacing:0.05em;">Full Name</label>
                            <p class="text-white fw-semibold"><?php echo htmlspecialchars($user['fullname']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white-50 small text-uppercase" style="letter-spacing:0.05em;">Email Address</label>
                            <p class="text-white fw-semibold"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white-50 small text-uppercase" style="letter-spacing:0.05em;">Member Since</label>
                            <p class="text-white fw-semibold"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-white-50 small text-uppercase" style="letter-spacing:0.05em;">Total Orders</label>
                            <p class="text-white fw-semibold"><?php echo $total_orders; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row justify-content-center mt-4 mb-5">
            <div class="col-lg-8">
                <div class="profile-box">
                    <h5 class="text-white mb-3">⚡ Quick Actions</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="index.php#products" class="btn btn-red">🛒 Continue Shopping</a>
                        <a href="user_orders.php" class="btn btn-outline-red">📦 My Orders</a>
                        <a href="edit.php" class="btn btn-outline-red">✏️ Edit Profile</a>
                        <a href="logout.php" class="btn btn-outline-red">🚪 Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>