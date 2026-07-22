<?php
session_start();
include "db.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

// Get all users (except admins)
$users = mysqli_query($conn, "SELECT * FROM users WHERE is_admin=0 ORDER BY created_at DESC");

// Get user stats
$stats_query = "SELECT COUNT(*) as total FROM users WHERE is_admin=0";
$stats_result = mysqli_query($conn, $stats_query);
$total_users = mysqli_fetch_assoc($stats_result)['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users - BlackMarket Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --bm-red: #c0392b; }
        body { background: #0a0a0a; font-family: 'Inter', sans-serif; }
        .sidebar { background: #111; min-height: 100vh; border-right: 1px solid #222; padding: 1rem; }
        .sidebar .nav-link { color: #888; padding: 0.75rem 1rem; border-radius: 6px; transition: all 0.2s; }
        .sidebar .nav-link:hover { background: #1a1a1a; color: #fff; }
        .sidebar .nav-link.active { background: #c0392b; color: #fff; }
        .sidebar .nav-link i { margin-right: 10px; width: 20px; text-align: center; }
        .main-content { padding: 2rem; }
        .stat-card { background: #1a1a1a; padding: 1.5rem; border-radius: 8px; border: 1px solid #2a2a2a; text-align: center; }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #fff; }
        .stat-card .label { color: #888; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .table-dark { background: #111; }
        .table-dark th { border-bottom: 2px solid #c0392b; color: #aaa; }
        .table-dark td { border-color: #222; vertical-align: middle; }
        .btn-red { background: #c0392b; color: #fff; border: none; }
        .btn-red:hover { background: #96281b; color: #fff; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 col-lg-2 d-md-block sidebar">
                <h5 class="text-white mb-4" style="font-family:'Oswald',sans-serif;">
                    Black<span style="color:#c0392b;">Market</span>
                    <small class="d-block text-muted" style="font-size:0.5rem;">Admin</small>
                </h5>
                <nav class="nav flex-column">
                    <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i>Dashboard</a>
                    <a href="admin_orders.php" class="nav-link"><i class="bi bi-box-seam"></i>Orders</a>
                    <a href="admin_inventory.php" class="nav-link"><i class="bi bi-clipboard-data"></i>Inventory</a>
                    <a href="admin_suppliers.php" class="nav-link"><i class="bi bi-truck"></i>Suppliers</a>
                    <a href="admin_users.php" class="nav-link active"><i class="bi bi-people"></i>Users</a>
                    <a href="admin_logs.php" class="nav-link"><i class="bi bi-clock-history"></i>Logs</a>
                    <hr class="border-secondary">
                    <a href="admin_logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i>Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="text-white">👥 User Management</h4>
                    <a href="admin_dashboard.php" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left"></i> Back</a>
                </div>

                <!-- Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="number"><?php echo $total_users; ?></div>
                            <div class="label">Total Users</div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <td>#<?php echo $user['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($user['fullname']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><small><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($users) == 0): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">No users registered yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>