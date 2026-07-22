<?php
session_start();
include "db.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

// Handle add inventory
if (isset($_POST['add_inventory'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $reorder_level = (int)$_POST['reorder_level'];
    $unit_price = (float)$_POST['unit_price'];
    $supplier_id = $_POST['supplier_id'] ? (int)$_POST['supplier_id'] : 'NULL';
    
    $sql = "INSERT INTO inventory (product_name, category, quantity, reorder_level, unit_price, supplier_id) 
            VALUES ('$product_name', '$category', $quantity, $reorder_level, $unit_price, $supplier_id)";
    
    if (mysqli_query($conn, $sql)) {
        $admin_id = $_SESSION['admin_id'];
        $ip = $_SERVER['REMOTE_ADDR'];
        mysqli_query($conn, "INSERT INTO activity_log (admin_id, action, details, ip_address) 
                             VALUES ('$admin_id', 'inventory_added', 'Added inventory item: $product_name', '$ip')");
        header("Location: admin_inventory.php?added=1");
        exit();
    }
}

// Handle inventory update
if (isset($_POST['update_inventory']) && isset($_POST['inventory_id'])) {
    $id = (int)$_POST['inventory_id'];
    $quantity = (int)$_POST['quantity'];
    $reorder_level = (int)$_POST['reorder_level'];
    $unit_price = (float)$_POST['unit_price'];
    $supplier_id = $_POST['supplier_id'] ? (int)$_POST['supplier_id'] : 'NULL';
    
    mysqli_query($conn, "UPDATE inventory SET 
                        quantity = $quantity, 
                        reorder_level = $reorder_level, 
                        unit_price = $unit_price,
                        supplier_id = $supplier_id
                        WHERE id = $id");
    
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_query($conn, "INSERT INTO activity_log (admin_id, action, details, ip_address) 
                         VALUES ('$admin_id', 'inventory_updated', 'Inventory #$id updated', '$ip')");
    
    header("Location: admin_inventory.php?updated=1");
    exit();
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT product_name FROM inventory WHERE id = $id"));
    mysqli_query($conn, "DELETE FROM inventory WHERE id = $id");
    header("Location: admin_inventory.php?deleted=1");
    exit();
}

// Get inventory with supplier info
$inventory = mysqli_query($conn, "SELECT i.*, s.supplier_name 
                                  FROM inventory i 
                                  LEFT JOIN suppliers s ON i.supplier_id = s.id 
                                  ORDER BY i.category, i.product_name");

// Get suppliers for dropdown
$suppliers = mysqli_query($conn, "SELECT id, supplier_name FROM suppliers ORDER BY supplier_name");

// Get low stock items
$low_stock = mysqli_query($conn, "SELECT i.*, s.supplier_name 
                                  FROM inventory i 
                                  LEFT JOIN suppliers s ON i.supplier_id = s.id 
                                  WHERE i.quantity <= i.reorder_level 
                                  ORDER BY i.quantity ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory - BlackMarket Admin</title>
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
        .btn-red { background: #c0392b; color: #fff; border: none; }
        .btn-red:hover { background: #96281b; color: #fff; }
        .btn-outline-red { border: 1px solid #c0392b; color: #c0392b; background: transparent; }
        .btn-outline-red:hover { background: #c0392b; color: #fff; }
        .table-dark { background: #111; }
        .table-dark th { border-bottom: 2px solid #c0392b; color: #aaa; }
        .table-dark td { border-color: #222; vertical-align: middle; }
        .form-dark { background: #0a0a0a; border: 1px solid #333; color: #fff; }
        .form-dark:focus { border-color: #c0392b; box-shadow: 0 0 0 0.2rem rgba(192,57,43,0.25); }
        .form-dark option { background: #1a1a1a; }
        .low-stock { background: rgba(231,76,60,0.1); }
        .low-stock td { color: #e74c3c; }
        .in-stock td { color: #2ecc71; }
        .status-badge { padding: 0.2rem 0.75rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .status-low { background: #e74c3c; color: #fff; }
        .status-in { background: #27ae60; color: #fff; }
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
                    <a href="admin_inventory.php" class="nav-link active"><i class="bi bi-clipboard-data"></i>Inventory</a>
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
                    <h4 class="text-white">📊 Inventory Management</h4>
                    <button class="btn btn-red" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
                        <i class="bi bi-plus-circle"></i> Add Item
                    </button>
                </div>

                <?php if (isset($_GET['added'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">✅ Inventory item added!</div>
                <?php endif; ?>
                <?php if (isset($_GET['updated'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">✅ Inventory updated!</div>
                <?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">🗑️ Item removed!</div>
                <?php endif; ?>

                <!-- Low Stock Alert -->
                <?php if (mysqli_num_rows($low_stock) > 0): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Low Stock Alert!</strong> The following items need reordering:
                        <?php 
                        $low_list = [];
                        while ($item = mysqli_fetch_assoc($low_stock)) {
                            $low_list[] = htmlspecialchars($item['product_name']) . ' (Stock: ' . $item['quantity'] . ')';
                        }
                        echo implode(', ', $low_list);
                        ?>
                    </div>
                    <?php mysqli_data_seek($low_stock, 0); ?>
                <?php endif; ?>

                <!-- Inventory Table -->
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Value</th>
                                <th>Supplier</th>
                                <th>Reorder Level</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = mysqli_fetch_assoc($inventory)): 
                                $is_low = $item['quantity'] <= $item['reorder_level'];
                                $total_value = $item['quantity'] * $item['unit_price'];
                            ?>
                                <tr class="<?php echo $is_low ? 'low-stock' : ''; ?>">
                                    <td><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($item['category']); ?></span></td>
                                    <td>
                                        <span class="<?php echo $is_low ? 'text-danger' : 'text-success'; ?> fw-bold">
                                            <?php echo $item['quantity']; ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td>$<?php echo number_format($total_value, 2); ?></td>
                                    <td><?php echo htmlspecialchars($item['supplier_name'] ?? 'No supplier'); ?></td>
                                    <td><?php echo $item['reorder_level']; ?></td>
                                    <td>
                                        <?php if ($is_low): ?>
                                            <span class="status-badge status-low">Low Stock</span>
                                        <?php else: ?>
                                            <span class="status-badge status-in">In Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $item['id']; ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this item?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?php echo $item['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content bg-dark text-white border-secondary">
                                            <div class="modal-header border-secondary">
                                                <h5 class="modal-title">Edit Inventory - <?php echo htmlspecialchars($item['product_name']); ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="inventory_id" value="<?php echo $item['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label text-white-50 small">Quantity</label>
                                                        <input type="number" name="quantity" class="form-control form-dark" value="<?php echo $item['quantity']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-white-50 small">Reorder Level</label>
                                                        <input type="number" name="reorder_level" class="form-control form-dark" value="<?php echo $item['reorder_level']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-white-50 small">Unit Price</label>
                                                        <input type="number" step="0.01" name="unit_price" class="form-control form-dark" value="<?php echo $item['unit_price']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-white-50 small">Supplier</label>
                                                        <select name="supplier_id" class="form-select form-dark">
                                                            <option value="">No supplier</option>
                                                            <?php 
                                                            $suppliers_dropdown = mysqli_query($conn, "SELECT id, supplier_name FROM suppliers ORDER BY supplier_name");
                                                            while ($sup = mysqli_fetch_assoc($suppliers_dropdown)): 
                                                            ?>
                                                                <option value="<?php echo $sup['id']; ?>" <?php echo $item['supplier_id'] == $sup['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($sup['supplier_name']); ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-secondary">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="update_inventory" class="btn btn-red">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($inventory) == 0): ?>
                                <tr><td colspan="9" class="text-center text-muted py-4">No inventory items</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Inventory Modal -->
    <div class="modal fade" id="addInventoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add Inventory Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Product Name *</label>
                            <input type="text" name="product_name" class="form-control form-dark" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Category *</label>
                            <select name="category" class="form-select form-dark" required>
                                <option value="pistol">Pistol</option>
                                <option value="rifle">Rifle</option>
                                <option value="sniper">Sniper Rifle</option>
                                <option value="accessory">Accessory</option>
                                <option value="ammunition">Ammunition</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Quantity *</label>
                            <input type="number" name="quantity" class="form-control form-dark" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Reorder Level *</label>
                            <input type="number" name="reorder_level" class="form-control form-dark" min="0" value="5" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Unit Price *</label>
                            <input type="number" step="0.01" name="unit_price" class="form-control form-dark" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Supplier</label>
                            <select name="supplier_id" class="form-select form-dark">
                                <option value="">No supplier</option>
                                <?php 
                                $sup_query = mysqli_query($conn, "SELECT id, supplier_name FROM suppliers ORDER BY supplier_name");
                                while ($sup = mysqli_fetch_assoc($sup_query)): 
                                ?>
                                    <option value="<?php echo $sup['id']; ?>"><?php echo htmlspecialchars($sup['supplier_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_inventory" class="btn btn-red">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>