<?php
session_start();
include "db.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Get statistics
$stats = [];

// Total orders
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders");
$stats['total_orders'] = mysqli_fetch_assoc($result)['total'];

// Pending orders
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='pending'");
$stats['pending_orders'] = mysqli_fetch_assoc($result)['total'];

// Total revenue
$result = mysqli_query($conn, "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status='delivered'");
$stats['total_revenue'] = mysqli_fetch_assoc($result)['total'];

// Total users
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE is_admin=0");
$stats['total_users'] = mysqli_fetch_assoc($result)['total'];

// Total suppliers
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM suppliers");
$stats['total_suppliers'] = mysqli_fetch_assoc($result)['total'];

// Inventory stats
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM inventory WHERE quantity <= reorder_level");
$stats['low_stock'] = mysqli_fetch_assoc($result)['total'];

// Total inventory items
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM inventory");
$stats['total_inventory'] = mysqli_fetch_assoc($result)['total'];

// Recent orders
$recent_orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");

// Recent activity
$recent_activity = mysqli_query($conn, "SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - BlackMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --bm-red: #c0392b; --bm-red-dark: #96281b; }
        body { background: #0a0a0a; font-family: 'Inter', sans-serif; }
        .sidebar { background: #111; min-height: 100vh; border-right: 1px solid #222; }
        .sidebar .nav-link { color: #888; padding: 0.75rem 1rem; border-radius: 6px; transition: all 0.2s; }
        .sidebar .nav-link:hover { background: #1a1a1a; color: #fff; }
        .sidebar .nav-link.active { background: #c0392b; color: #fff; }
        .sidebar .nav-link i { margin-right: 10px; width: 20px; text-align: center; }
        .main-content { padding: 2rem; }
        .stat-card { background: #1a1a1a; padding: 1.5rem; border-radius: 10px; border: 1px solid #2a2a2a; }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #fff; }
        .stat-card .label { color: #888; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-card .icon { font-size: 2.5rem; opacity: 0.2; }
        .stat-card:hover { border-color: #c0392b; transition: border-color 0.3s; }
        .btn-red { background: #c0392b; color: #fff; border: none; }
        .btn-red:hover { background: #96281b; color: #fff; }
        .btn-outline-red { border: 1px solid #c0392b; color: #c0392b; }
        .btn-outline-red:hover { background: #c0392b; color: #fff; }
        .table-dark { background: #111; }
        .table-dark th { border-bottom: 2px solid #c0392b; color: #aaa; }
        .table-dark td { border-color: #222; vertical-align: middle; }
        .status-badge { padding: 0.2rem 0.75rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #f39c12; color: #fff; }
        .status-processing { background: #3498db; color: #fff; }
        .status-shipped { background: #9b59b6; color: #fff; }
        .status-delivered { background: #27ae60; color: #fff; }
        .status-cancelled { background: #e74c3c; color: #fff; }
        .admin-header { border-bottom: 2px solid #c0392b; padding-bottom: 1rem; margin-bottom: 2rem; }
        .admin-header h2 { font-family: 'Oswald', sans-serif; color: #fff; }
        .admin-header .admin-name { color: #888; }
        .activity-item { padding: 0.5rem 0; border-bottom: 1px solid #1a1a1a; }
        .activity-item:last-child { border-bottom: none; }
        .activity-item .time { color: #666; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 col-lg-2 d-md-block sidebar p-3">
                <h4 class="text-white mb-4" style="font-family:'Oswald',sans-serif;">
                    Black<span style="color:#c0392b;">Market</span>
                    <small class="d-block text-muted" style="font-size:0.6rem;font-family:'Inter',sans-serif;">Admin Panel</small>
                </h4>
                <nav class="nav flex-column">
                    <a href="admin_dashboard.php" class="nav-link active">
                        <i class="bi bi-speedometer2"></i>Dashboard
                    </a>
                    <a href="admin_orders.php" class="nav-link">
                        <i class="bi bi-box-seam"></i>Orders
                    </a>
                    <a href="admin_inventory.php" class="nav-link">
                        <i class="bi bi-clipboard-data"></i>Inventory
                    </a>
                    <a href="admin_suppliers.php" class="nav-link">
                        <i class="bi bi-truck"></i>Suppliers
                    </a>
                    <a href="admin_users.php" class="nav-link">
                        <i class="bi bi-people"></i>Users
                    </a>
                    <a href="admin_logs.php" class="nav-link">
                        <i class="bi bi-clock-history"></i>Activity Logs
                    </a>
                    <hr class="border-secondary">
                    <a href="admin_logout.php" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right"></i>Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 col-lg-10 main-content">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2>Dashboard</h2>
                        <span class="admin-name">Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</span>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-sm btn-outline-light me-2">
                            <i class="bi bi-eye"></i> View Store
                        </a>
                        <span class="text-muted small"><?php echo date('F d, Y h:i A'); ?></span>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="number"><?php echo $stats['total_orders']; ?></div>
                                    <div class="label">Total Orders</div>
                                </div>
                                <div class="icon bi bi-box-seam"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="number text-warning"><?php echo $stats['pending_orders']; ?></div>
                                    <div class="label">Pending Orders</div>
                                </div>
                                <div class="icon bi bi-clock-history"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="number" style="color:#2ecc71;">$<?php echo number_format($stats['total_revenue'], 0); ?></div>
                                    <div class="label">Revenue</div>
                                </div>
                                <div class="icon bi bi-currency-dollar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="number"><?php echo $stats['total_users']; ?></div>
                                    <div class="label">Users</div>
                                </div>
                                <div class="icon bi bi-people"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="number text-info"><?php echo $stats['total_inventory']; ?></div>
                                    <div class="label">Inventory Items</div>
                                </div>
                                <div class="icon bi bi-clipboard-data"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="number text-danger"><?php echo $stats['low_stock']; ?></div>
                                    <div class="label">Low Stock Items</div>
                                </div>
                                <div class="icon bi bi-exclamation-triangle"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="number text-success"><?php echo $stats['total_suppliers']; ?></div>
                                    <div class="label">Suppliers</div>
                                </div>
                                <div class="icon bi bi-truck"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Recent Orders -->
                    <div class="col-md-7">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-white mb-0">📦 Recent Orders</h6>
                                <a href="admin_orders.php" class="btn btn-sm btn-red">View All</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-dark table-sm">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                                            <tr>
                                                <td><?php echo $order['order_number']; ?></td>
                                                <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <?php if (mysqli_num_rows($recent_orders) == 0): ?>
                                            <tr><td colspan="4" class="text-center text-muted">No orders yet</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="col-md-5">
                        <div class="stat-card">
                            <h6 class="text-white mb-3">🔄 Recent Activity</h6>
                            <?php while ($log = mysqli_fetch_assoc($recent_activity)): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="text-white small"><?php echo htmlspecialchars($log['action']); ?></span>
                                            <div class="text-muted small"><?php echo htmlspecialchars($log['details']); ?></div>
                                        </div>
                                        <span class="time"><?php echo date('h:i A', strtotime($log['created_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($recent_activity) == 0): ?>
                                <div class="text-muted small">No recent activity</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>