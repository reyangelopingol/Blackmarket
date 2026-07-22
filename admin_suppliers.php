<?php
session_start();
include "db.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

// First, insert sample contracts for each supplier if they don't exist
// This runs once when the page loads

// Get all suppliers
$all_suppliers = mysqli_query($conn, "SELECT id, supplier_name FROM suppliers");

while ($supplier = mysqli_fetch_assoc($all_suppliers)) {
    // Check if this supplier already has contracts
    $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM supplier_contracts WHERE supplier_id = {$supplier['id']}");
    $count = mysqli_fetch_assoc($check);
    
    // If no contracts, insert sample contracts
    if ($count['count'] == 0) {
        // Sample contracts based on supplier
        $sample_contracts = [
            [
                'contract_number' => 'CTR-' . date('Y') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'contract_date' => date('Y-m-d', strtotime('-6 months')),
                'expiry_date' => date('Y-m-d', strtotime('+6 months')),
                'contract_value' => rand(50000, 500000),
                'payment_terms' => 'Net 30 days',
                'delivery_terms' => 'FOB Shipping Point',
                'status' => 'active',
                'notes' => 'Standard supply agreement for ' . $supplier['supplier_name']
            ],
            [
                'contract_number' => 'CTR-' . date('Y') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'contract_date' => date('Y-m-d', strtotime('-3 months')),
                'expiry_date' => date('Y-m-d', strtotime('+9 months')),
                'contract_value' => rand(25000, 300000),
                'payment_terms' => '50% upfront, 50% on delivery',
                'delivery_terms' => 'EXW',
                'status' => 'active',
                'notes' => 'Special bulk order agreement'
            ]
        ];
        
        // Insert 2 contracts per supplier
        foreach ($sample_contracts as $contract) {
            $sql = "INSERT INTO supplier_contracts (
                supplier_id, contract_number, contract_date, expiry_date, 
                contract_value, payment_terms, delivery_terms, status, notes
            ) VALUES (
                {$supplier['id']}, 
                '{$contract['contract_number']}', 
                '{$contract['contract_date']}', 
                '{$contract['expiry_date']}',
                {$contract['contract_value']}, 
                '{$contract['payment_terms']}', 
                '{$contract['delivery_terms']}', 
                '{$contract['status']}', 
                '{$contract['notes']}'
            )";
            mysqli_query($conn, $sql);
        }
    }
}

// Get all suppliers with stats
$suppliers = mysqli_query($conn, "
    SELECT s.*,
    (SELECT COUNT(*) FROM supplier_contracts WHERE supplier_id = s.id AND status = 'active') as active_contracts,
    (SELECT COUNT(*) FROM supplier_contracts WHERE supplier_id = s.id) as total_contracts,
    (SELECT COUNT(*) FROM supplier_items WHERE supplier_id = s.id AND is_active = 1) as active_items
    FROM suppliers s
    ORDER BY s.supplier_name
");

// Get stats
$stats_query = "SELECT COUNT(*) as total FROM suppliers";
$stats_result = mysqli_query($conn, $stats_query);
$total_suppliers = mysqli_fetch_assoc($stats_result)['total'];

$contract_stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
    SUM(contract_value) as total_value
    FROM supplier_contracts";
$contract_stats_result = mysqli_query($conn, $contract_stats_query);
$contract_stats = mysqli_fetch_assoc($contract_stats_result);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Suppliers - BlackMarket Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bm-red: #c0392b; --bm-red-dark: #96281b; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; font-family: 'Inter', sans-serif; }
        
        .sidebar {
            background: #0d0d0d;
            min-height: 100vh;
            border-right: 1px solid rgba(255,255,255,0.04);
            padding: 1.5rem 1rem;
            position: sticky;
            top: 0;
        }
        
        .sidebar-brand {
            font-family: 'Oswald', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.06em;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 1.5rem;
        }
        .sidebar-brand span { color: var(--bm-red); }
        .sidebar-brand small { display: block; font-size: 0.5rem; color: rgba(255,255,255,0.25); font-family: 'Inter', sans-serif; letter-spacing: 0.15em; text-transform: uppercase; }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.4);
            padding: 0.7rem 1rem;
            border-radius: 10px;
            transition: all 0.25s ease;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }
        .sidebar .nav-link i { font-size: 1.1rem; width: 22px; text-align: center; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.05); color: #fff; transform: translateX(4px); }
        .sidebar .nav-link.active { background: linear-gradient(135deg, rgba(192,57,43,0.2), rgba(192,57,43,0.05)); color: #fff; border: 1px solid rgba(192,57,43,0.15); }
        .sidebar .nav-link.active i { color: var(--bm-red); }
        .sidebar .nav-divider { border-color: rgba(255,255,255,0.05); margin: 0.75rem 0; }
        .sidebar .nav-link.logout { color: rgba(231,76,60,0.5); }
        .sidebar .nav-link.logout:hover { color: #e74c3c; background: rgba(231,76,60,0.08); }
        
        .main-content { padding: 2rem 2.5rem; background: #0a0a0a; }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .page-header h2 { font-family: 'Oswald', sans-serif; color: #fff; font-size: 1.8rem; margin-bottom: 0; }
        .page-header .subtitle { color: rgba(255,255,255,0.35); font-size: 0.85rem; }
        
        .stat-card {
            background: rgba(26, 26, 26, 0.7);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.04);
            transition: all 0.3s ease;
            height: 100%;
        }
        .stat-card:hover { border-color: rgba(192,57,43,0.2); transform: translateY(-2px); }
        .stat-card .stat-number { font-size: 2rem; font-weight: 700; color: #fff; }
        .stat-card .stat-label { color: rgba(255,255,255,0.35); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; }
        .stat-card .stat-icon { font-size: 2rem; opacity: 0.1; }
        
        .status-badge {
            padding: 0.2rem 0.75rem;
            border-radius: 50px;
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-active { background: rgba(46,204,113,0.15); color: #2ecc71; }
        .status-expired { background: rgba(231,76,60,0.15); color: #e74c3c; }
        .status-pending { background: rgba(243,156,18,0.15); color: #f39c12; }
        .status-terminated { background: rgba(155,155,155,0.15); color: #999; }
        
        .section-card {
            background: rgba(26, 26, 26, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.04);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .section-card .section-title {
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .supplier-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 1.2rem;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        .supplier-card:hover { border-color: rgba(192,57,43,0.2); background: rgba(255,255,255,0.03); }
        .supplier-card .supplier-name { color: #fff; font-weight: 600; font-size: 1.1rem; }
        .supplier-card .supplier-detail { color: rgba(255,255,255,0.4); font-size: 0.85rem; }
        
        .contract-item {
            background: rgba(255,255,255,0.02);
            border-left: 3px solid var(--bm-red);
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 0 8px 8px 0;
            transition: all 0.2s ease;
        }
        .contract-item:hover { background: rgba(255,255,255,0.04); }
        .contract-item .contract-number { color: #fff; font-weight: 500; font-size: 0.9rem; }
        .contract-item .contract-detail { color: rgba(255,255,255,0.3); font-size: 0.75rem; }
        
        .item-tag {
            display: inline-block;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 6px;
            padding: 0.2rem 0.6rem;
            font-size: 0.7rem;
            color: rgba(255,255,255,0.5);
            margin: 0.2rem;
        }
        
        .badge-count {
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
        }
        
        @media (max-width: 768px) {
            .sidebar { min-height: auto; position: relative; }
            .main-content { padding: 1rem; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .supplier-card .row > div { margin-bottom: 1rem; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 col-lg-2 d-md-block sidebar">
                <div class="sidebar-brand">
                    Black<span>Market</span>
                    <small>Admin Panel</small>
                </div>
                <nav class="nav flex-column">
                    <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    <a href="admin_orders.php" class="nav-link"><i class="bi bi-box-seam"></i> Orders</a>
                    <a href="admin_inventory.php" class="nav-link"><i class="bi bi-clipboard-data"></i> Inventory</a>
                    <a href="admin_suppliers.php" class="nav-link active"><i class="bi bi-truck"></i> Suppliers</a>
                    <a href="admin_users.php" class="nav-link"><i class="bi bi-people"></i> Users</a>
                    <a href="admin_logs.php" class="nav-link"><i class="bi bi-clock-history"></i> Logs</a>
                    <hr class="nav-divider">
                    <a href="admin_logout.php" class="nav-link logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 col-lg-10 main-content">
                <div class="page-header">
                    <div>
                        <h2><i class="bi bi-truck me-2" style="color:var(--bm-red);"></i>Supplier Management</h2>
                        <span class="subtitle">View suppliers, contracts, and supplied items</span>
                    </div>
                </div>

                <!-- Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-number"><?php echo $total_suppliers; ?></div>
                                    <div class="stat-label">Total Suppliers</div>
                                </div>
                                <div class="stat-icon bi bi-truck"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-number" style="color:#2ecc71;"><?php echo $contract_stats['active'] ?? 0; ?></div>
                                    <div class="stat-label">Active Contracts</div>
                                </div>
                                <div class="stat-icon bi bi-file-earmark-text"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-number" style="color:#f39c12;"><?php echo $contract_stats['pending'] ?? 0; ?></div>
                                    <div class="stat-label">Pending Contracts</div>
                                </div>
                                <div class="stat-icon bi bi-clock"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-number" style="color:#f1c40f;">$<?php echo number_format($contract_stats['total_value'] ?? 0, 0); ?></div>
                                    <div class="stat-label">Total Contract Value</div>
                                </div>
                                <div class="stat-icon bi bi-currency-dollar"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Suppliers List -->
                <div class="section-card">
                    <div class="section-title"><i class="bi bi-list-ul me-2"></i>All Suppliers</div>
                    
                    <?php while ($supplier = mysqli_fetch_assoc($suppliers)): 
                        $supplier_contracts = mysqli_query($conn, "
                            SELECT * FROM supplier_contracts 
                            WHERE supplier_id = {$supplier['id']} 
                            ORDER BY contract_date DESC
                        ");
                        
                        $supplier_items_list = mysqli_query($conn, "
                            SELECT * FROM supplier_items 
                            WHERE supplier_id = {$supplier['id']} AND is_active = 1
                            ORDER BY item_name
                        ");
                    ?>
                        <div class="supplier-card">
                            <div class="row align-items-start">
                                <div class="col-md-3">
                                    <div class="supplier-name"><?php echo htmlspecialchars($supplier['supplier_name']); ?></div>
                                    <div class="supplier-detail">
                                        <?php if ($supplier['contact_person']): ?>
                                            <i class="bi bi-person"></i> <?php echo htmlspecialchars($supplier['contact_person']); ?><br>
                                        <?php endif; ?>
                                        <?php if ($supplier['email']): ?>
                                            <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($supplier['email']); ?><br>
                                        <?php endif; ?>
                                        <?php if ($supplier['phone']): ?>
                                            <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($supplier['phone']); ?><br>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-success badge-count me-1"><?php echo $supplier['active_contracts']; ?> Active Contracts</span>
                                        <span class="badge bg-info badge-count"><?php echo $supplier['active_items']; ?> Items</span>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="small text-white-50 mb-1">📄 Contracts</div>
                                    <?php if (mysqli_num_rows($supplier_contracts) > 0): ?>
                                        <?php while ($contract = mysqli_fetch_assoc($supplier_contracts)): ?>
                                            <div class="contract-item">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <div class="contract-number"><?php echo htmlspecialchars($contract['contract_number']); ?></div>
                                                        <div class="contract-detail">
                                                            <?php echo date('M d, Y', strtotime($contract['contract_date'])); ?>
                                                            <?php if ($contract['expiry_date']): ?>
                                                                → <?php echo date('M d, Y', strtotime($contract['expiry_date'])); ?>
                                                            <?php endif; ?>
                                                            <?php if ($contract['contract_value'] > 0): ?>
                                                                • $<?php echo number_format($contract['contract_value'], 2); ?>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($contract['payment_terms']): ?>
                                                            <div class="contract-detail">Payment: <?php echo htmlspecialchars($contract['payment_terms']); ?></div>
                                                        <?php endif; ?>
                                                        <?php if ($contract['delivery_terms']): ?>
                                                            <div class="contract-detail">Delivery: <?php echo htmlspecialchars($contract['delivery_terms']); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <span class="status-badge status-<?php echo $contract['status']; ?>"><?php echo ucfirst($contract['status']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="text-white-50 small" style="padding:0.5rem 0;">No contracts yet</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-5">
                                    <div class="small text-white-50 mb-1">📦 Supplied Items</div>
                                    <?php if (mysqli_num_rows($supplier_items_list) > 0): ?>
                                        <div class="d-flex flex-wrap">
                                            <?php while ($item = mysqli_fetch_assoc($supplier_items_list)): ?>
                                                <span class="item-tag">
                                                    <?php echo htmlspecialchars($item['item_name']); ?>
                                                    <?php if ($item['item_category']): ?>
                                                        <span class="badge bg-secondary" style="font-size:0.5rem;"><?php echo ucfirst($item['item_category']); ?></span>
                                                    <?php endif; ?>
                                                    ($<?php echo number_format($item['unit_price'], 2); ?>)
                                                </span>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-white-50 small" style="padding:0.5rem 0;">No items added</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if (mysqli_num_rows($suppliers) == 0): ?>
                        <div class="text-center text-muted py-4">
                            <div style="font-size:3rem;margin-bottom:0.5rem;">📭</div>
                            No suppliers found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>