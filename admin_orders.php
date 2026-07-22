<?php
session_start();
include "db.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($status, $allowed_statuses)) {
        mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $order_id");
        
        $admin_id = $_SESSION['admin_id'];
        $ip = $_SERVER['REMOTE_ADDR'];
        mysqli_query($conn, "INSERT INTO activity_log (admin_id, action, details, ip_address) 
                             VALUES ('$admin_id', 'order_status_update', 'Order #$order_id status updated to $status', '$ip')");
        
        header("Location: admin_orders.php?updated=1");
        exit();
    }
}

// Handle order deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM order_items WHERE order_id = $id");
    mysqli_query($conn, "DELETE FROM orders WHERE id = $id");
    
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_query($conn, "INSERT INTO activity_log (admin_id, action, details, ip_address) 
                         VALUES ('$admin_id', 'order_deleted', 'Order #$id deleted', '$ip')");
    
    header("Location: admin_orders.php?deleted=1");
    exit();
}

// Get all orders
$orders_query = "
    SELECT o.*, u.fullname as user_name, u.email as user_email,
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
";
$orders_result = mysqli_query($conn, $orders_query);

// Get order stats
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status='processing' THEN 1 ELSE 0 END) as processing,
        SUM(CASE WHEN status='shipped' THEN 1 ELSE 0 END) as shipped,
        SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END) as delivered,
        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM orders
";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Management - BlackMarket Admin</title>
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
        .stat-card { background: #1a1a1a; padding: 1rem; border-radius: 8px; border: 1px solid #2a2a2a; text-align: center; }
        .stat-card .number { font-size: 1.5rem; font-weight: 700; color: #fff; }
        .stat-card .label { color: #888; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .btn-red { background: #c0392b; color: #fff; border: none; }
        .btn-red:hover { background: #96281b; color: #fff; }
        .btn-outline-light { border-color: #555; color: #aaa; }
        .btn-outline-light:hover { background: #333; color: #fff; }
        .status-badge { padding: 0.2rem 0.75rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #f39c12; color: #fff; }
        .status-processing { background: #3498db; color: #fff; }
        .status-shipped { background: #9b59b6; color: #fff; }
        .status-delivered { background: #27ae60; color: #fff; }
        .status-cancelled { background: #e74c3c; color: #fff; }
        .table-dark { background: #111; }
        .table-dark th { border-bottom: 2px solid #c0392b; color: #aaa; }
        .table-dark td { border-color: #222; vertical-align: middle; }
        .order-details { display: none; background: #0a0a0a; }
        .order-details.show { display: table-row; }
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
                    <a href="admin_orders.php" class="nav-link active"><i class="bi bi-box-seam"></i>Orders</a>
                    <a href="admin_inventory.php" class="nav-link"><i class="bi bi-clipboard-data"></i>Inventory</a>
                    <a href="admin_suppliers.php" class="nav-link"><i class="bi bi-truck"></i>Suppliers</a>
                    <a href="admin_users.php" class="nav-link"><i class="bi bi-people"></i>Users</a>
                    <a href="admin_logs.php" class="nav-link"><i class="bi bi-clock-history"></i>Logs</a>
                    <hr class="border-secondary">
                    <a href="admin_logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i>Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="text-white">📦 Order Management</h4>
                    <a href="admin_dashboard.php" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left"></i> Back</a>
                </div>

                <?php if (isset($_GET['updated'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">✅ Order status updated!</div>
                <?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">🗑️ Order deleted!</div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="row g-2 mb-4">
                    <div class="col-2"><div class="stat-card"><div class="number"><?php echo $stats['total']; ?></div><div class="label">Total</div></div></div>
                    <div class="col-2"><div class="stat-card"><div class="number text-warning"><?php echo $stats['pending']; ?></div><div class="label">Pending</div></div></div>
                    <div class="col-2"><div class="stat-card"><div class="number text-primary"><?php echo $stats['processing']; ?></div><div class="label">Processing</div></div></div>
                    <div class="col-2"><div class="stat-card"><div class="number text-success"><?php echo $stats['delivered']; ?></div><div class="label">Delivered</div></div></div>
                    <div class="col-2"><div class="stat-card"><div class="number text-danger"><?php echo $stats['cancelled']; ?></div><div class="label">Cancelled</div></div></div>
                    <div class="col-2"><div class="stat-card"><div class="number text-info"><?php echo $stats['shipped']; ?></div><div class="label">Shipped</div></div></div>
                </div>

                <!-- Orders Table -->
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                                <tr>
                                    <td><strong><?php echo $order['order_number']; ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($order['phone']); ?></small>
                                        <?php if ($order['user_id']): ?>
                                            <br><small class="text-success">User ID: <?php echo $order['user_id']; ?></small>
                                        <?php else: ?>
                                            <br><small class="text-warning">Guest Order</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $order['item_count']; ?> items</span>
                                        <button class="btn btn-sm btn-outline-secondary mt-1" onclick="toggleDetails(<?php echo $order['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                    <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    <td><?php echo ucfirst($order['payment_method']); ?></td>
                                    <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                    <td><small><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small></td>
                                    <td>
                                        <form method="POST" class="d-flex gap-1 flex-wrap" onsubmit="return confirm('Update order status?')">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="form-select form-select-sm bg-dark text-white border-secondary" style="width:100px;">
                                                <option value="pending" <?php echo $order['status']=='pending'?'selected':''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status']=='processing'?'selected':''; ?>>Processing</option>
                                                <option value="shipped" <?php echo $order['status']=='shipped'?'selected':''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo $order['status']=='delivered'?'selected':''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo $order['status']=='cancelled'?'selected':''; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-sm btn-red">Update</button>
                                        </form>
                                        <a href="?delete=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-danger mt-1" onclick="return confirm('Delete this order?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr id="details-<?php echo $order['id']; ?>" class="order-details">
                                    <td colspan="8">
                                        <div class="p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong class="text-white-50">Delivery Address:</strong>
                                                    <p class="text-white small"><?php echo htmlspecialchars($order['street']); ?><br><?php echo htmlspecialchars($order['city'] . ', ' . $order['province']); ?><br>ZIP: <?php echo htmlspecialchars($order['zip_code']); ?></p>
                                                    <?php if ($order['delivery_notes']): ?>
                                                        <p class="text-white-50 small"><em>Notes: <?php echo htmlspecialchars($order['delivery_notes']); ?></em></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong class="text-white-50">Items:</strong>
                                                    <?php
                                                    $items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = " . $order['id']);
                                                    while ($item = mysqli_fetch_assoc($items)):
                                                    ?>
                                                        <div class="d-flex justify-content-between text-white small">
                                                            <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                            <span>$<?php echo number_format($item['product_price'], 2); ?> x <?php echo $item['quantity']; ?></span>
                                                        </div>
                                                    <?php endwhile; ?>
                                                    <div class="d-flex justify-content-between text-white fw-bold mt-2 pt-2 border-top border-secondary">
                                                        <span>Total</span>
                                                        <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <span class="text-white-50 small">Last Updated: </span>
                                                <span class="text-muted small"><?php echo date('M d, Y h:i A', strtotime($order['updated_at'] ?? $order['created_at'])); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($orders_result) == 0): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">No orders found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleDetails(orderId) {
            document.getElementById('details-' + orderId).classList.toggle('show');
        }
    </script>
</body>
</html>