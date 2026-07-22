<?php
session_start();
include "db.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

// Handle clearing all logs
if (isset($_POST['clear_all_logs'])) {
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    
    mysqli_query($conn, "INSERT INTO activity_log (admin_id, action, details, ip_address) 
                         VALUES ('$admin_id', 'logs_cleared', 'All activity logs cleared', '$ip')");
    
    mysqli_query($conn, "TRUNCATE TABLE activity_log");
    
    header("Location: admin_logs.php?cleared=1");
    exit();
}

// Handle deleting a single log entry
if (isset($_GET['delete_log']) && is_numeric($_GET['delete_log'])) {
    $log_id = (int)$_GET['delete_log'];
    $log_query = mysqli_query($conn, "SELECT action, details FROM activity_log WHERE id = $log_id");
    $log_data = mysqli_fetch_assoc($log_query);
    
    mysqli_query($conn, "DELETE FROM activity_log WHERE id = $log_id");
    
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $log_info = $log_data ? $log_data['action'] . ' - ' . $log_data['details'] : 'Unknown log entry';
    mysqli_query($conn, "INSERT INTO activity_log (admin_id, action, details, ip_address) 
                         VALUES ('$admin_id', 'log_deleted', 'Deleted log entry: $log_info', '$ip')");
    
    header("Location: admin_logs.php?deleted=1");
    exit();
}

// Get logs
$logs = mysqli_query($conn, "SELECT l.*, u.fullname as admin_name FROM activity_log l 
                             LEFT JOIN users u ON l.admin_id = u.id 
                             ORDER BY l.created_at DESC LIMIT 100");

// Get total log count
$count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM activity_log");
$total_logs = mysqli_fetch_assoc($count_result)['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Activity Logs - BlackMarket Admin</title>
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
        .table-dark { background: #111; }
        .table-dark th { border-bottom: 2px solid #c0392b; color: #aaa; }
        .table-dark td { border-color: #222; vertical-align: middle; }
        .btn-red { background: #c0392b; color: #fff; border: none; }
        .btn-red:hover { background: #96281b; color: #fff; }
        .btn-outline-red { border: 1px solid #c0392b; color: #c0392b; background: transparent; }
        .btn-outline-red:hover { background: #c0392b; color: #fff; }
        .log-action { font-weight: 600; color: #c0392b; }
        .stat-card { background: #1a1a1a; padding: 1rem; border-radius: 8px; border: 1px solid #2a2a2a; text-align: center; }
        .stat-card .number { font-size: 1.5rem; font-weight: 700; color: #fff; }
        .stat-card .label { color: #888; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .btn-sm-delete { padding: 0.1rem 0.4rem; font-size: 0.65rem; }
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
                    <a href="admin_users.php" class="nav-link"><i class="bi bi-people"></i>Users</a>
                    <a href="admin_logs.php" class="nav-link active"><i class="bi bi-clock-history"></i>Logs</a>
                    <hr class="border-secondary">
                    <a href="admin_logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i>Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h4 class="text-white mb-0">🔄 Activity Logs</h4>
                        <span class="text-muted small">Total logs: <?php echo $total_logs; ?></span>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if ($total_logs > 0): ?>
                            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                                <i class="bi bi-trash3"></i> Clear All Logs
                            </button>
                        <?php endif; ?>
                        <a href="admin_dashboard.php" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left"></i> Back</a>
                    </div>
                </div>

                <?php if (isset($_GET['cleared'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">🗑️ All activity logs cleared!</div>
                <?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show">🗑️ Log entry deleted!</div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="number"><?php echo $total_logs; ?></div>
                            <div class="label">Total Entries</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="number"><?php echo min(100, $total_logs); ?></div>
                            <div class="label">Showing (Last 100)</div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($log = mysqli_fetch_assoc($logs)): ?>
                                <tr>
                                    <td>#<?php echo $log['id']; ?></td>
                                    <td><?php echo htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?></td>
                                    <td><span class="log-action"><?php echo htmlspecialchars($log['action']); ?></span></td>
                                    <td><?php echo htmlspecialchars($log['details']); ?></td>
                                    <td><small><?php echo htmlspecialchars($log['ip_address']); ?></small></td>
                                    <td><small><?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?></small></td>
                                    <td>
                                        <a href="?delete_log=<?php echo $log['id']; ?>" class="btn btn-sm btn-outline-danger btn-sm-delete" onclick="return confirm('Delete this log entry?')">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($logs) == 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <div class="fs-1 mb-2">📭</div>
                                        No activity logs found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear All Logs Confirmation Modal -->
    <div class="modal fade" id="clearLogsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-danger">⚠️ Clear All Logs?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-white-50">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        This action will permanently delete <strong class="text-white"><?php echo $total_logs; ?></strong> log entries.
                        <br><br>
                        <span class="text-danger">This action cannot be undone!</span>
                    </p>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display:inline;">
                        <button type="submit" name="clear_all_logs" class="btn btn-danger">Yes, Clear All Logs</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>