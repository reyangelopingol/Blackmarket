<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's orders
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);

// Get status counts
$status_counts = [];
$status_query = "SELECT status, COUNT(*) as count FROM orders WHERE user_id = $user_id GROUP BY status";
$status_result = mysqli_query($conn, $status_query);
while ($row = mysqli_fetch_assoc($status_result)) {
    $status_counts[$row['status']] = $row['count'];
}

// Get total orders
$total_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = $user_id";
$total_result = mysqli_query($conn, $total_query);
$total_orders = mysqli_fetch_assoc($total_result)['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Orders - BlackMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #0a0a0a; font-family: 'Inter', sans-serif; }
        .navbar-custom { background: #111; border-bottom: 1px solid #222; padding: 0.8rem 0; }
        .navbar-brand-custom { font-family: 'Oswald', sans-serif; font-weight: 700; font-size: 1.6rem; color: #fff; text-decoration: none; }
        .navbar-brand-custom span { color: #c0392b; }
        .btn-red { background: #c0392b; color: #fff; border: none; }
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
        .order-card { background: #141414; border: 1px solid #2a2a2a; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; transition: all 0.3s; }
        .order-card:hover { border-color: rgba(192,57,43,0.3); }
        .order-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid #2a2a2a; margin-bottom: 0.75rem; }
        .order-number { color: #fff; font-weight: 600; font-size: 1.1rem; }
        .order-date { color: rgba(255,255,255,0.3); font-size: 0.8rem; }
        .order-total { color: #c0392b; font-weight: 700; font-size: 1.2rem; }
        .order-items { color: rgba(255,255,255,0.5); font-size: 0.85rem; }
        .stat-card { background: #141414; padding: 1rem; border-radius: 10px; border: 1px solid #2a2a2a; text-align: center; }
        .stat-card .number { font-size: 1.5rem; font-weight: 700; color: #fff; }
        .stat-card .label { color: rgba(255,255,255,0.3); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .order-details-toggle { cursor: pointer; }
        .order-details-toggle:hover { color: #c0392b; }
    </style>
</head>
<body>
    <nav class="navbar-custom">
        <div class="container">
            <a class="navbar-brand-custom" href="index.php">Black<span>Market</span></a>
            <div class="d-flex gap-2">
                <a href="user.php" class="btn btn-sm btn-outline-light"><i class="bi bi-person"></i> My Account</a>
                <a href="index.php" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left"></i> Back to Store</a>
                <a href="logout.php" class="btn btn-sm btn-outline-red">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-white mb-0">📦 My Orders</h4>
            <span class="text-white-50 small">Total: <?php echo $total_orders; ?> orders</span>
        </div>

        <!-- Status Stats -->
        <div class="row g-2 mb-4">
            <div class="col-4 col-md-2">
                <div class="stat-card">
                    <div class="number text-warning"><?php echo $status_counts['pending'] ?? 0; ?></div>
                    <div class="label">Pending</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="stat-card">
                    <div class="number text-primary"><?php echo $status_counts['processing'] ?? 0; ?></div>
                    <div class="label">Processing</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="stat-card">
                    <div class="number text-info"><?php echo $status_counts['shipped'] ?? 0; ?></div>
                    <div class="label">Shipped</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="stat-card">
                    <div class="number text-success"><?php echo $status_counts['delivered'] ?? 0; ?></div>
                    <div class="label">Delivered</div>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="stat-card">
                    <div class="number text-danger"><?php echo $status_counts['cancelled'] ?? 0; ?></div>
                    <div class="label">Cancelled</div>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <?php if (mysqli_num_rows($orders_result) > 0): ?>
            <?php while ($order = mysqli_fetch_assoc($orders_result)): 
                $items_query = "SELECT * FROM order_items WHERE order_id = {$order['id']}";
                $items_result = mysqli_query($conn, $items_query);
                $item_count = mysqli_num_rows($items_result);
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                            <div class="order-date"><?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div class="text-end">
                            <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                            <div class="order-total mt-1">$<?php echo number_format($order['total_amount'], 2); ?></div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="order-items">📋 <?php echo $item_count; ?> item(s) • <?php echo ucfirst($order['payment_method']); ?></div>
                            <div class="order-items small mt-1">
                                <?php 
                                mysqli_data_seek($items_result, 0);
                                $item_names = [];
                                while ($item = mysqli_fetch_assoc($items_result)) {
                                    $item_names[] = htmlspecialchars($item['product_name']) . ' x' . $item['quantity'];
                                }
                                echo implode(', ', $item_names);
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-2 mt-md-0">
                            <div class="order-items small">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($order['city'] . ', ' . $order['province']); ?>
                            </div>
                            <button class="btn btn-sm btn-outline-light mt-1 order-details-toggle" onclick="toggleOrderDetails(<?php echo $order['id']; ?>)">
                                <i class="bi bi-chevron-down"></i> View Details
                            </button>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div id="order-details-<?php echo $order['id']; ?>" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #2a2a2a;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="small text-white-50 mb-2">📬 Delivery Details</div>
                                <div class="text-white-50 small">
                                    <strong class="text-white"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong><br>
                                    <?php echo htmlspecialchars($order['phone']); ?><br>
                                    <?php echo htmlspecialchars($order['street']); ?><br>
                                    <?php echo htmlspecialchars($order['city'] . ', ' . $order['province']); ?><br>
                                    <?php if ($order['zip_code']): ?>ZIP: <?php echo htmlspecialchars($order['zip_code']); ?><br><?php endif; ?>
                                    <?php if ($order['delivery_notes']): ?>📝 <?php echo htmlspecialchars($order['delivery_notes']); ?><?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-white-50 mb-2">📋 Order Items</div>
                                <?php 
                                mysqli_data_seek($items_result, 0);
                                while ($item = mysqli_fetch_assoc($items_result)): 
                                ?>
                                    <div class="d-flex justify-content-between text-white-50 small py-1">
                                        <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                        <span>$<?php echo number_format($item['product_price'], 2); ?> x <?php echo $item['quantity']; ?></span>
                                    </div>
                                <?php endwhile; ?>
                                <div class="d-flex justify-content-between text-white fw-bold mt-2 pt-2 border-top border-secondary">
                                    <span>Total</span>
                                    <span class="order-total">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                <div class="mt-2 pt-2 border-top border-secondary">
                                    <span class="text-white-50 small">Last Updated: </span>
                                    <span class="text-white small"><?php echo date('M d, Y h:i A', strtotime($order['updated_at'] ?? $order['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                <h5 class="text-white-50">You haven't placed any orders yet</h5>
                <p class="text-muted">Start shopping for premium firearms today!</p>
                <a href="index.php#products" class="btn btn-red mt-2">🛒 Browse Products</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleOrderDetails(orderId) {
            const details = document.getElementById('order-details-' + orderId);
            if (details.style.display === 'none') {
                details.style.display = 'block';
            } else {
                details.style.display = 'none';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>