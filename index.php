<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlackMarket | Premium Firearms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bm-red: #c0392b;
            --bm-red-dark: #96281b;
            --bm-red-glow: rgba(192,57,43,0.15);
            --bm-dark: #0a0a0a;
            --bm-card: #141414;
            --bm-border: rgba(255,255,255,0.06);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: var(--bm-dark); 
            font-family: 'Inter', sans-serif; 
            color: #fff;
            overflow-x: hidden;
        }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bm-dark); }
        ::-webkit-scrollbar-thumb { background: var(--bm-red); border-radius: 10px; }
        
        .navbar-custom {
            background: rgba(10, 10, 10, 0.92) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--bm-border);
            padding: 0.8rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand-custom {
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 1.6rem;
            letter-spacing: 0.06em;
            color: #fff !important;
            text-decoration: none;
        }
        
        .navbar-brand-custom span { color: var(--bm-red); }
        
        .search-wrapper {
            position: relative;
            width: 100%;
            max-width: 400px;
        }
        
        .search-wrapper .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.2);
            font-size: 0.9rem;
            pointer-events: none;
        }
        
        .search-wrapper input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.8rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--bm-border);
            border-radius: 12px;
            color: #fff;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }
        
        .search-wrapper input::placeholder {
            color: rgba(255,255,255,0.2);
        }
        
        .search-wrapper input:focus {
            outline: none;
            border-color: var(--bm-red);
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 4px rgba(192,57,43,0.08);
        }
        
        .nav-btn {
            background: transparent;
            border: 1px solid var(--bm-border);
            color: rgba(255,255,255,0.6);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            cursor: pointer;
        }
        
        .nav-btn:hover {
            border-color: var(--bm-red);
            color: #fff;
            background: rgba(192,57,43,0.08);
        }
        
        .nav-btn .cart-badge {
            background: var(--bm-red);
            color: #fff;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0.1rem 0.5rem;
            border-radius: 50px;
            margin-left: 0.25rem;
        }
        
        .user-btn {
            background: rgba(192,57,43,0.15);
            border: 1px solid rgba(192,57,43,0.2);
            color: #fff;
            padding: 0.5rem 1.2rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-btn:hover {
            background: rgba(192,57,43,0.25);
            border-color: var(--bm-red);
            color: #fff;
        }
        
        .logout-btn {
            background: transparent;
            border: 1px solid rgba(231,76,60,0.2);
            color: rgba(231,76,60,0.6);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: rgba(231,76,60,0.1);
            border-color: #e74c3c;
            color: #e74c3c;
        }
        
        .hero-section {
            padding: 4rem 0 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -20%;
            width: 60%;
            height: 80%;
            background: radial-gradient(ellipse, rgba(192,57,43,0.06) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .hero-title {
            font-family: 'Oswald', sans-serif;
            font-size: 3.2rem;
            font-weight: 700;
            line-height: 1.1;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        
        .hero-title span { color: var(--bm-red); }
        
        .hero-subtitle {
            color: rgba(255,255,255,0.4);
            font-size: 1.05rem;
            max-width: 500px;
            margin-bottom: 1.5rem;
        }
        
        .hero-stats {
            display: flex;
            gap: 2.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--bm-border);
        }
        
        .hero-stats .stat-item .stat-number {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            line-height: 1;
        }
        
        .hero-stats .stat-item .stat-label {
            color: rgba(255,255,255,0.3);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        
        .hero-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }
        
        .hero-feature-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--bm-border);
            border-radius: 14px;
            padding: 1.2rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .hero-feature-card:hover {
            border-color: rgba(192,57,43,0.2);
            transform: translateY(-2px);
        }
        
        .hero-feature-card .feature-icon { font-size: 1.8rem; margin-bottom: 0.3rem; }
        .hero-feature-card .feature-name { 
            font-weight: 600; 
            font-size: 0.85rem;
            color: #fff;
        }
        .hero-feature-card .feature-desc {
            color: rgba(255,255,255,0.3);
            font-size: 0.7rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 2rem;
        }
        
        .section-header .section-label {
            color: var(--bm-red);
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }
        
        .section-header .section-title {
            font-family: 'Oswald', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0;
        }
        
        .section-header .section-link {
            color: rgba(255,255,255,0.3);
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .section-header .section-link:hover {
            color: var(--bm-red);
        }
        
        .category-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--bm-border);
            border-radius: 16px;
            padding: 1.8rem 1.5rem;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(192,57,43,0.05), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .category-card:hover {
            border-color: rgba(192,57,43,0.3);
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
        }
        
        .category-card:hover::before { opacity: 1; }
        
        .category-card .category-icon {
            width: 52px;
            height: 52px;
            background: rgba(192,57,43,0.1);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 0.8rem;
            position: relative;
            z-index: 1;
        }
        
        .category-card .category-name {
            font-family: 'Oswald', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
            position: relative;
            z-index: 1;
        }
        
        .category-card .category-desc {
            color: rgba(255,255,255,0.35);
            font-size: 0.85rem;
            position: relative;
            z-index: 1;
        }
        
        .category-card .category-count {
            display: inline-block;
            background: rgba(192,57,43,0.1);
            color: var(--bm-red);
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.15rem 0.8rem;
            border-radius: 50px;
            margin-top: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .filter-btn {
            padding: 0.4rem 1.2rem;
            border-radius: 50px;
            border: 1px solid var(--bm-border);
            background: transparent;
            color: rgba(255,255,255,0.4);
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .filter-btn:hover {
            border-color: rgba(192,57,43,0.3);
            color: #fff;
        }
        
        .filter-btn.active {
            background: var(--bm-red);
            border-color: var(--bm-red);
            color: #fff;
        }
        
        .product-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--bm-border);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s ease;
            height: 100%;
        }
        
        .product-card:hover {
            border-color: rgba(192,57,43,0.2);
            transform: translateY(-6px);
            box-shadow: 0 16px 50px rgba(0,0,0,0.5);
        }
        
        .product-card .product-image {
            height: 210px;
            background: rgba(255,255,255,0.02);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
        }
        
        .product-card .product-image img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            transition: transform 0.4s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .product-card .product-badge {
            position: absolute;
            top: 0.8rem;
            left: 0.8rem;
            padding: 0.15rem 0.8rem;
            border-radius: 50px;
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .product-badge.best-seller {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: #fff;
        }
        
        .product-badge.classic {
            background: rgba(52,152,219,0.2);
            color: #3498db;
        }
        
        .product-badge.iconic {
            background: rgba(155,89,182,0.2);
            color: #9b59b6;
        }
        
        .product-badge.elite {
            background: linear-gradient(135deg, #c0392b, #e74c3c);
            color: #fff;
        }
        
        .product-badge.precision {
            background: rgba(46,204,113,0.2);
            color: #2ecc71;
        }
        
        .product-badge.popular {
            background: rgba(243,156,18,0.2);
            color: #f39c12;
        }
        
        .product-card .product-body {
            padding: 1.2rem 1.5rem 1.5rem;
        }
        
        .product-card .product-category {
            color: rgba(255,255,255,0.25);
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 500;
        }
        
        .product-card .product-name {
            font-family: 'Oswald', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.2rem;
        }
        
        .product-card .product-desc {
            color: rgba(255,255,255,0.35);
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
        
        .product-card .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-card .product-price {
            font-family: 'Oswald', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--bm-red);
        }
        
        .product-card .btn-add-cart {
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--bm-border);
            color: rgba(255,255,255,0.6);
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .product-card .btn-add-cart:hover {
            background: var(--bm-red);
            border-color: var(--bm-red);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(192,57,43,0.3);
        }
        
        .features-bar {
            background: rgba(192,57,43,0.06);
            border-top: 1px solid var(--bm-border);
            border-bottom: 1px solid var(--bm-border);
            padding: 1rem 0;
            margin: 2rem 0;
        }
        
        .features-bar .feature-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: rgba(255,255,255,0.5);
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .features-bar .feature-item i {
            color: var(--bm-red);
            font-size: 1.1rem;
        }
        
        .footer {
            border-top: 1px solid var(--bm-border);
            padding: 3rem 0 2rem;
            margin-top: 3rem;
        }
        
        .footer-brand {
            font-family: 'Oswald', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
        }
        
        .footer-brand span { color: var(--bm-red); }
        
        .footer p { color: rgba(255,255,255,0.25); font-size: 0.85rem; }
        
        .footer-links h6 {
            color: rgba(255,255,255,0.4);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 600;
        }
        
        .footer-links a {
            color: rgba(255,255,255,0.25);
            text-decoration: none;
            font-size: 0.85rem;
            display: block;
            margin-bottom: 0.3rem;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover { color: var(--bm-red); }
        
        .footer-bottom {
            border-top: 1px solid var(--bm-border);
            padding-top: 1.5rem;
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .footer-bottom small {
            color: rgba(255,255,255,0.15);
            font-size: 0.75rem;
        }
        
        @media (max-width: 992px) {
            .hero-title { font-size: 2.5rem; }
            .hero-features { grid-template-columns: repeat(3, 1fr); }
        }
        
        @media (max-width: 768px) {
            .navbar-custom .container { flex-wrap: wrap; gap: 0.5rem; }
            .search-wrapper { max-width: 100%; order: 3; flex: 0 0 100%; }
            .hero-title { font-size: 2rem; }
            .hero-features { grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
            .hero-feature-card { padding: 0.8rem; }
            .hero-feature-card .feature-icon { font-size: 1.2rem; }
            .hero-stats { gap: 1.5rem; }
            .section-header .section-title { font-size: 1.5rem; }
        }
        
        @media (max-width: 480px) {
            .hero-features { grid-template-columns: repeat(3, 1fr); }
            .hero-feature-card .feature-desc { display: none; }
            .hero-stats .stat-item .stat-number { font-size: 1.4rem; }
            .product-card .product-image { height: 160px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-custom">
    <div class="container">
        <a class="navbar-brand-custom" href="index.php">Black<span>Market</span></a>
        
        <div class="search-wrapper d-none d-md-block">
            <span class="search-icon"><i class="bi bi-search"></i></span>
            <input id="searchInput" type="search" placeholder="Search firearms...">
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <button id="cartBtn" class="nav-btn">
                <i class="bi bi-cart3"></i> Cart
                <span class="cart-badge" id="cartCount">0</span>
            </button>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="user.php" class="user-btn">
                    <i class="bi bi-person-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="nav-btn">Login</a>
                <a href="signup.php" class="user-btn">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="hero-title">
                    Precision.<br><span>Power.</span> Purpose.
                </div>
                <p class="hero-subtitle">
                    Handguns, rifles, and sniper systems from the world's most trusted manufacturers.
                </p>
                <div class="d-flex gap-3 mb-4">
                    <a href="#products" class="btn btn-red" style="background:var(--bm-red);color:#fff;padding:0.6rem 2rem;border-radius:10px;font-weight:600;text-decoration:none;transition:all 0.3s;display:inline-flex;align-items:center;gap:0.5rem;">
                        <i class="bi bi-arrow-right"></i> Shop Now
                    </a>
                    <a href="#categories" class="btn" style="border:1px solid var(--bm-border);color:rgba(255,255,255,0.6);padding:0.6rem 1.5rem;border-radius:10px;font-weight:500;text-decoration:none;transition:all 0.3s;">
                        Browse Categories
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">6+</div>
                        <div class="stat-label">Firearms</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Categories</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24h</div>
                        <div class="stat-label">Shipping</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="hero-features">
                    <div class="hero-feature-card">
                        <div class="feature-icon">🔫</div>
                        <div class="feature-name">Pistols</div>
                        <div class="feature-desc">Compact carry arms</div>
                    </div>
                    <div class="hero-feature-card">
                        <div class="feature-icon">🪖</div>
                        <div class="feature-name">Rifles</div>
                        <div class="feature-desc">Versatile long guns</div>
                    </div>
                    <div class="hero-feature-card">
                        <div class="feature-icon">🎯</div>
                        <div class="feature-name">Sniper</div>
                        <div class="feature-desc">Precision systems</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES BAR -->
<div class="features-bar">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3 feature-item">
                <i class="bi bi-box-seam"></i> Discrete Packaging
            </div>
            <div class="col-6 col-md-3 feature-item">
                <i class="bi bi-shield-lock"></i> Secure Checkout
            </div>
            <div class="col-6 col-md-3 feature-item">
                <i class="bi bi-award"></i> Trusted Brands
            </div>
            <div class="col-6 col-md-3 feature-item">
                <i class="bi bi-truck"></i> Fast Delivery
            </div>
        </div>
    </div>
</div>

<!-- CATEGORIES -->
<section id="categories" style="padding: 2rem 0;">
    <div class="container">
        <div class="section-header">
            <div>
                <div class="section-label">Browse by type</div>
                <h2 class="section-title">Product Categories</h2>
            </div>
            <a href="#products" class="section-link">See all <i class="bi bi-chevron-right"></i></a>
        </div>
        
        <div class="row g-3">
            <div class="col-md-4">
                <a href="#products" class="category-card" data-category-filter="pistol">
                    <div class="category-icon">🔫</div>
                    <div class="category-name">Pistols</div>
                    <div class="category-desc">Compact sidearms for close-range engagements and concealed carry.</div>
                    <span class="category-count">2 items</span>
                </a>
            </div>
            <div class="col-md-4">
                <a href="#products" class="category-card" data-category-filter="rifle">
                    <div class="category-icon">🪖</div>
                    <div class="category-name">Rifles</div>
                    <div class="category-desc">Versatile rifles for medium to long-range tactical use.</div>
                    <span class="category-count">2 items</span>
                </a>
            </div>
            <div class="col-md-4">
                <a href="#products" class="category-card" data-category-filter="sniper">
                    <div class="category-icon">🎯</div>
                    <div class="category-name">Sniper Rifles</div>
                    <div class="category-desc">High-caliber, bolt-action and semi-auto precision systems.</div>
                    <span class="category-count">2 items</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCTS -->
<section id="products" style="padding: 2rem 0 3rem;">
    <div class="container">
        <div class="section-header">
            <div>
                <div class="section-label">All Firearms</div>
                <h2 class="section-title">Product Listing</h2>
            </div>
        </div>
        
        <div class="filter-group">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="pistol">Pistols</button>
            <button class="filter-btn" data-filter="rifle">Rifles</button>
            <button class="filter-btn" data-filter="sniper">Snipers</button>
        </div>
        
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4" id="productGrid">
            
            <!-- Product 1: Glock 19 -->
            <div class="col product-col" data-category="pistol" data-name="glock 19">
                <div class="product-card">
                    <div class="product-image">
                        <span class="product-badge best-seller">Best Seller</span>
                        <img src="https://guntech.ph/wp-content/uploads/2020/07/Glock-19-Gen5-FS-728px.png" alt="Glock 19" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231a1a1a%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23333%22 font-family=%22Inter%22 font-size=%2216%22%3EGlock 19%3C/text%3E%3C/svg%3E'">
                    </div>
                    <div class="product-body">
                        <div class="product-category">Pistol · 9mm</div>
                        <div class="product-name">Glock 19</div>
                        <div class="product-desc">Reliable 9mm compact pistol — the gold standard for tactical carry.</div>
                        <div class="product-footer">
                            <span class="product-price">$599</span>
                            <button class="btn-add-cart add-to-cart" data-name="Glock 19" data-price="599">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product 2: Colt 1911 -->
            <div class="col product-col" data-category="pistol" data-name="colt 1911">
                <div class="product-card">
                    <div class="product-image">
                        <span class="product-badge classic">Classic</span>
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSJQtDG5tGB8pMoNd6AKUNJ8LvhoOtVlGvsgg&s" alt="Colt 1911" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231a1a1a%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23333%22 font-family=%22Inter%22 font-size=%2216%22%3EColt 1911%3C/text%3E%3C/svg%3E'">
                    </div>
                    <div class="product-body">
                        <div class="product-category">Pistol · .45 ACP</div>
                        <div class="product-name">Colt 1911</div>
                        <div class="product-desc">Legendary .45 ACP with over a century of proven stopping power.</div>
                        <div class="product-footer">
                            <span class="product-price">$749</span>
                            <button class="btn-add-cart add-to-cart" data-name="Colt 1911" data-price="749">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product 3: AK-47 -->
            <div class="col product-col" data-category="rifle" data-name="ak-47">
                <div class="product-card">
                    <div class="product-image">
                        <span class="product-badge iconic">Iconic</span>
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQza2n-jrKXWyVhsHDF5J8d5xvfUlLZcOs04A&s" alt="AK-47" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231a1a1a%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23333%22 font-family=%22Inter%22 font-size=%2216%22%3EAK-47%3C/text%3E%3C/svg%3E'">
                    </div>
                    <div class="product-body">
                        <div class="product-category">Rifle · 7.62×39mm</div>
                        <div class="product-name">AK-47</div>
                        <div class="product-desc">Durable gas-operated rifle built for reliability in any condition.</div>
                        <div class="product-footer">
                            <span class="product-price">$1,199</span>
                            <button class="btn-add-cart add-to-cart" data-name="AK-47" data-price="1199">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product 4: AR-15 -->
            <div class="col product-col" data-category="rifle" data-name="ar-15">
                <div class="product-card">
                    <div class="product-image">
                        <span class="product-badge popular">Popular</span>
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRXkIuZByRs4iF8XjdJEkE2lebVUfMxuXP-pQ&s" alt="AR-15" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231a1a1a%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23333%22 font-family=%22Inter%22 font-size=%2216%22%3EAR-15%3C/text%3E%3C/svg%3E'">
                    </div>
                    <div class="product-body">
                        <div class="product-category">Rifle · 5.56mm NATO</div>
                        <div class="product-name">AR-15</div>
                        <div class="product-desc">America's rifle — modular, accurate, and infinitely customizable.</div>
                        <div class="product-footer">
                            <span class="product-price">$1,299</span>
                            <button class="btn-add-cart add-to-cart" data-name="AR-15" data-price="1299">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product 5: Barrett M82 -->
            <div class="col product-col" data-category="sniper" data-name="barrett m82">
                <div class="product-card">
                    <div class="product-image">
                        <span class="product-badge elite">Elite</span>
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSXw_bthfkSIcTCnlysPkUyMmejYS4UBbWc8Q&s" alt="Barrett M82" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231a1a1a%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23333%22 font-family=%22Inter%22 font-size=%2216%22%3EBarrett M82%3C/text%3E%3C/svg%3E'">
                    </div>
                    <div class="product-body">
                        <div class="product-category">Sniper Rifle · .50 BMG</div>
                        <div class="product-name">Barrett M82</div>
                        <div class="product-desc">Anti-materiel semi-auto sniper rifle with devastating long-range power.</div>
                        <div class="product-footer">
                            <span class="product-price">$5,499</span>
                            <button class="btn-add-cart add-to-cart" data-name="Barrett M82" data-price="5499">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product 6: Remington 700 -->
            <div class="col product-col" data-category="sniper" data-name="remington 700">
                <div class="product-card">
                    <div class="product-image">
                        <span class="product-badge precision">Precision</span>
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT63MDQQczEsFO1Tw_J5ATKHgxIXZU6TcVcpQ&s" alt="Remington 700" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231a1a1a%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23333%22 font-family=%22Inter%22 font-size=%2216%22%3ERemington 700%3C/text%3E%3C/svg%3E'">
                    </div>
                    <div class="product-body">
                        <div class="product-category">Sniper Rifle · .308 Win</div>
                        <div class="product-name">Remington 700</div>
                        <div class="product-desc">Bolt-action precision platform trusted by hunters and military alike.</div>
                        <div class="product-footer">
                            <span class="product-price">$2,299</span>
                            <button class="btn-add-cart add-to-cart" data-name="Remington 700" data-price="2299">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="footer-brand">Black<span>Market</span></div>
                <p>Premium firearms marketplace with discrete shipping and secure checkout.</p>
            </div>
            <div class="col-6 col-md-2 footer-links">
                <h6>Quick Links</h6>
                <a href="index.php">Home</a>
                <a href="#categories">Categories</a>
                <a href="#products">Products</a>
            </div>
            <div class="col-6 col-md-2 footer-links">
                <h6>Account</h6>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="user.php">My Profile</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="signup.php">Register</a>
                <?php endif; ?>
            </div>
            <div class="col-md-4 footer-links">
                <h6>Contact</h6>
                <a href="mailto:support@blackmarket.store">support@blackmarket.store</a>
                <a href="tel:+15550102020">+1 (555) 010-2020</a>
            </div>
        </div>
        <div class="footer-bottom">
            <small>© 2026 BlackMarket. All rights reserved.</small>
            <small>Firearms sold legally — compliance required by buyer.</small>
        </div>
    </div>
</footer>

<!-- ===== CART MODAL ===== -->
<div class="modal fade" id="cartModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="background:#141414;border:1px solid var(--bm-border);color:#fff;border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid var(--bm-border);">
                <h5 class="modal-title"><i class="bi bi-cart3 me-2"></i>Shopping Cart</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="cartEmpty" class="text-center py-5" style="color:rgba(255,255,255,0.3);">
                    <div style="font-size:3rem;margin-bottom:0.5rem;">🛒</div>
                    Your cart is empty. Add some firearms first!
                </div>
                <div id="cartItemsList"></div>
                <div id="cartTotalRow" class="d-none text-end mt-3 pt-2" style="border-top:1px solid var(--bm-border);">
                    <span style="color:rgba(255,255,255,0.4);margin-right:0.5rem;">Total:</span>
                    <strong style="color:var(--bm-red);font-size:1.25rem;">$<span id="cartTotal">0.00</span></strong>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--bm-border);">
                <button class="btn" style="background:transparent;border:1px solid var(--bm-border);color:rgba(255,255,255,0.5);border-radius:10px;padding:0.5rem 1.5rem;" data-bs-dismiss="modal">Continue Shopping</button>
                <button class="btn" id="proceedCheckoutBtn" style="background:var(--bm-red);color:#fff;border-radius:10px;padding:0.5rem 1.5rem;border:none;font-weight:600;">Proceed to Checkout →</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== CHECKOUT MODAL ===== -->
<div class="modal fade" id="checkoutModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="background:#141414;border:1px solid var(--bm-border);color:#fff;border-radius:16px;">
            <div class="modal-header" style="border-bottom:1px solid var(--bm-border);">
                <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Checkout</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="checkoutCloseBtn"></button>
            </div>
            <div class="modal-body">
                <div class="step-indicator" style="display:flex;gap:0;margin-bottom:2rem;">
                    <div class="step-dot active" id="dot1" style="flex:1;text-align:center;position:relative;">
                        <div style="width:32px;height:32px;border-radius:50%;border:2px solid var(--bm-red);background:var(--bm-red);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;position:relative;z-index:1;">1</div>
                        <div style="font-size:0.65rem;color:rgba(255,255,255,0.3);margin-top:4px;">Delivery</div>
                    </div>
                    <div class="step-dot" id="dot2" style="flex:1;text-align:center;position:relative;">
                        <div style="width:32px;height:32px;border-radius:50%;border:2px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.3);display:inline-flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;position:relative;z-index:1;">2</div>
                        <div style="font-size:0.65rem;color:rgba(255,255,255,0.3);margin-top:4px;">Payment</div>
                    </div>
                    <div class="step-dot" id="dot3" style="flex:1;text-align:center;position:relative;">
                        <div style="width:32px;height:32px;border-radius:50%;border:2px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.3);display:inline-flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;position:relative;z-index:1;">3</div>
                        <div style="font-size:0.65rem;color:rgba(255,255,255,0.3);margin-top:4px;">Confirm</div>
                    </div>
                </div>

                <!-- STEP 1 -->
                <div class="checkout-step active" id="step1">
                    <h6 style="color:rgba(255,255,255,0.3);text-transform:uppercase;font-size:0.7rem;letter-spacing:0.1em;margin-bottom:1.5rem;">Delivery Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label style="color:rgba(255,255,255,0.4);font-size:0.8rem;">First Name <span style="color:var(--bm-red);">*</span></label>
                            <input type="text" id="fname" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;" placeholder="Juan" required>
                        </div>
                        <div class="col-md-6">
                            <label style="color:rgba(255,255,255,0.4);font-size:0.8rem;">Last Name <span style="color:var(--bm-red);">*</span></label>
                            <input type="text" id="lname" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;" placeholder="dela Cruz" required>
                        </div>
                        <div class="col-md-6">
                            <label style="color:rgba(255,255,255,0.4);font-size:0.8rem;">Phone Number <span style="color:var(--bm-red);">*</span></label>
                            <input type="tel" id="phone" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;" placeholder="09XX XXX XXXX" required>
                        </div>
                        <div class="col-md-6">
                            <label style="color:rgba(255,255,255,0.4);font-size:0.8rem;">Email Address</label>
                            <input type="email" id="email" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;" placeholder="juan@email.com">
                        </div>
                        <div class="col-12">
                            <label style="color:rgba(255,255,255,0.4);font-size:0.8rem;">Street Address <span style="color:var(--bm-red);">*</span></label>
                            <input type="text" id="street" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;" placeholder="House No., Street, Subdivision / Barangay" required>
                        </div>
                        <div class="col-md-4">
                            <label style="color:rgba(255,255,255,0.4);font-size:0.8rem;">City <span style="color:var(--bm-red);">*</span></label>
                            <input type="text" id="city" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;" placeholder="e.g. Indang" required>
                        </div>
                        <div class="col-md-4">
                            <label style="color:rgba(255,255,255,0.4);font-size:0.8rem;">Province <span style="color:var(--bm-red);">*</span></label>
                            <input type="text" id="province" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;" placeholder="e.g. Cavite" required>
                        </div>
                        <div class="col-md-4">
                            <label style="color:rgba(255,255,255,0.4);font-size:0.8rem;">ZIP Code</label>
                            <input type="text" id="zip" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;" placeholder="4122">
                        </div>
                        <div class="col-12">
                            <label style="color:rgba(255,255,255,0.4);font-size:0.8rem;">Delivery Notes</label>
                            <textarea id="notes" class="form-control" rows="2" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;" placeholder="Landmark, special instructions, etc."></textarea>
                        </div>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div class="checkout-step" id="step2">
                    <h6 style="color:rgba(255,255,255,0.3);text-transform:uppercase;font-size:0.7rem;letter-spacing:0.1em;margin-bottom:1.5rem;">Select Payment Method</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4">
                            <div class="pay-card" data-pay="gcash" onclick="selectPayment(this)" style="border:2px solid var(--bm-border);border-radius:12px;padding:1rem;cursor:pointer;text-align:center;transition:all 0.3s;background:rgba(255,255,255,0.02);">
                                <div style="font-size:1.8rem;">💙</div>
                                <div style="font-weight:600;color:#fff;font-size:0.85rem;">GCash</div>
                                <div style="color:rgba(255,255,255,0.3);font-size:0.65rem;">E-wallet transfer</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="pay-card" data-pay="paymaya" onclick="selectPayment(this)" style="border:2px solid var(--bm-border);border-radius:12px;padding:1rem;cursor:pointer;text-align:center;transition:all 0.3s;background:rgba(255,255,255,0.02);">
                                <div style="font-size:1.8rem;">💚</div>
                                <div style="font-weight:600;color:#fff;font-size:0.85rem;">PayMaya</div>
                                <div style="color:rgba(255,255,255,0.3);font-size:0.65rem;">E-wallet transfer</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="pay-card" data-pay="maribank" onclick="selectPayment(this)" style="border:2px solid var(--bm-border);border-radius:12px;padding:1rem;cursor:pointer;text-align:center;transition:all 0.3s;background:rgba(255,255,255,0.02);">
                                <div style="font-size:1.8rem;">🟣</div>
                                <div style="font-weight:600;color:#fff;font-size:0.85rem;">Maribank</div>
                                <div style="color:rgba(255,255,255,0.3);font-size:0.65rem;">Savings transfer</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="pay-card" data-pay="metrobank" onclick="selectPayment(this)" style="border:2px solid var(--bm-border);border-radius:12px;padding:1rem;cursor:pointer;text-align:center;transition:all 0.3s;background:rgba(255,255,255,0.02);">
                                <div style="font-size:1.8rem;">🏦</div>
                                <div style="font-weight:600;color:#fff;font-size:0.85rem;">Metrobank</div>
                                <div style="color:rgba(255,255,255,0.3);font-size:0.65rem;">Bank deposit</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="pay-card" data-pay="cod" onclick="selectPayment(this)" style="border:2px solid var(--bm-border);border-radius:12px;padding:1rem;cursor:pointer;text-align:center;transition:all 0.3s;background:rgba(255,255,255,0.02);">
                                <div style="font-size:1.8rem;">💵</div>
                                <div style="font-weight:600;color:#fff;font-size:0.85rem;">Cash on Delivery</div>
                                <div style="color:rgba(255,255,255,0.3);font-size:0.65rem;">Pay on arrival</div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-option" id="pay-gcash" style="display:none;">
                        <div style="background:rgba(255,255,255,0.03);border:1px solid var(--bm-border);border-radius:12px;padding:1rem;color:rgba(255,255,255,0.7);font-size:0.9rem;">
                            <strong style="color:#fff;">💙 GCash</strong><br>
                            Send payment to: <strong style="color:var(--bm-red);">0917-XXX-XXXX</strong> (BlackMarket PH)<br>
                            <label style="display:block;margin-top:0.5rem;color:rgba(255,255,255,0.4);font-size:0.8rem;">GCash Reference Number</label>
                            <input type="text" id="gcash-ref" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;width:100%;" placeholder="Enter GCash reference #">
                        </div>
                    </div>

                    <div class="payment-option" id="pay-paymaya" style="display:none;">
                        <div style="background:rgba(255,255,255,0.03);border:1px solid var(--bm-border);border-radius:12px;padding:1rem;color:rgba(255,255,255,0.7);font-size:0.9rem;">
                            <strong style="color:#fff;">💚 PayMaya</strong><br>
                            Send payment to: <strong style="color:var(--bm-red);">0915-XXX-XXXX</strong> (BlackMarket PH)<br>
                            <label style="display:block;margin-top:0.5rem;color:rgba(255,255,255,0.4);font-size:0.8rem;">PayMaya Reference Number</label>
                            <input type="text" id="maya-ref" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;width:100%;" placeholder="Enter PayMaya reference #">
                        </div>
                    </div>

                    <div class="payment-option" id="pay-maribank" style="display:none;">
                        <div style="background:rgba(255,255,255,0.03);border:1px solid var(--bm-border);border-radius:12px;padding:1rem;color:rgba(255,255,255,0.7);font-size:0.9rem;">
                            <strong style="color:#fff;">🟣 Maribank</strong><br>
                            Account Name: <strong style="color:#fff;">BlackMarket Store</strong><br>
                            Account Number: <strong style="color:var(--bm-red);">1234-5678-9012</strong><br>
                            <label style="display:block;margin-top:0.5rem;color:rgba(255,255,255,0.4);font-size:0.8rem;">Transfer Reference Number</label>
                            <input type="text" id="mari-ref" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;width:100%;" placeholder="Enter reference #">
                        </div>
                    </div>

                    <div class="payment-option" id="pay-metrobank" style="display:none;">
                        <div style="background:rgba(255,255,255,0.03);border:1px solid var(--bm-border);border-radius:12px;padding:1rem;color:rgba(255,255,255,0.7);font-size:0.9rem;">
                            <strong style="color:#fff;">🏦 Metrobank</strong><br>
                            Account Name: <strong style="color:#fff;">BlackMarket Inc.</strong><br>
                            Account Number: <strong style="color:var(--bm-red);">XXX-X-XXXXXXX-X</strong><br>
                            Branch: <strong style="color:#fff;">Cavite Branch</strong><br>
                            <label style="display:block;margin-top:0.5rem;color:rgba(255,255,255,0.4);font-size:0.8rem;">Deposit Reference</label>
                            <input type="text" id="metro-ref" class="form-control" style="background:rgba(255,255,255,0.05);border:1px solid var(--bm-border);color:#fff;border-radius:10px;padding:0.6rem 1rem;width:100%;" placeholder="Enter deposit slip or reference #">
                        </div>
                    </div>

                    <div class="payment-option" id="pay-cod" style="display:none;">
                        <div style="background:rgba(255,255,255,0.03);border:1px solid var(--bm-border);border-radius:12px;padding:1rem;color:rgba(255,255,255,0.7);font-size:0.9rem;">
                            <strong style="color:#fff;">💵 Cash on Delivery</strong><br>
                            Please prepare the exact amount upon delivery. Our rider will collect payment at your doorstep.
                            <br><span style="color:#f39c12;">Note: COD orders are subject to verification call before dispatch.</span>
                        </div>
                    </div>

                    <div id="pay-error" class="text-danger small mt-2 d-none">⚠️ Please select a payment method.</div>
                </div>

                <!-- STEP 3 -->
                <div class="checkout-step" id="step3">
                    <h6 style="color:rgba(255,255,255,0.3);text-transform:uppercase;font-size:0.7rem;letter-spacing:0.1em;margin-bottom:1.5rem;">Order Summary</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--bm-border);border-radius:12px;padding:1rem;">
                                <p style="color:rgba(255,255,255,0.3);font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">Deliver to</p>
                                <div id="confirm-address" style="color:rgba(255,255,255,0.7);font-size:0.9rem;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--bm-border);border-radius:12px;padding:1rem;">
                                <p style="color:rgba(255,255,255,0.3);font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">Payment Method</p>
                                <div id="confirm-payment" style="color:#fff;font-weight:600;font-size:0.9rem;"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--bm-border);border-radius:12px;padding:1rem;">
                                <p style="color:rgba(255,255,255,0.3);font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">Items Ordered</p>
                                <div id="confirm-items"></div>
                                <div style="display:flex;justify-content:space-between;align-items:center;padding-top:0.5rem;margin-top:0.5rem;border-top:1px solid var(--bm-border);">
                                    <span style="color:rgba(255,255,255,0.4);font-size:0.85rem;">Order Total</span>
                                    <strong style="color:var(--bm-red);font-size:1.2rem;">$<span id="confirm-total"></span></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--bm-border);display:flex;justify-content:space-between;">
                <button class="btn" id="backBtn" style="display:none!important;background:transparent;border:1px solid var(--bm-border);color:rgba(255,255,255,0.5);border-radius:10px;padding:0.5rem 1.5rem;">← Back</button>
                <div style="margin-left:auto;display:flex;gap:0.5rem;">
                    <button class="btn" id="cancelCheckoutBtn" style="background:transparent;border:1px solid var(--bm-border);color:rgba(255,255,255,0.5);border-radius:10px;padding:0.5rem 1.5rem;" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn" id="nextBtn" style="background:var(--bm-red);color:#fff;border-radius:10px;padding:0.5rem 1.5rem;border:none;font-weight:600;">Next →</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== SUCCESS MODAL ===== -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#141414;border:1px solid var(--bm-border);color:#fff;border-radius:16px;text-align:center;padding:2rem;">
            <div style="font-size:3rem;">✅</div>
            <h4 style="font-family:'Oswald',sans-serif;margin-top:1rem;">Order Placed!</h4>
            <p style="color:rgba(255,255,255,0.5);font-size:0.9rem;">Thank you for your order. Our team will contact you shortly for verification.</p>
            <p style="color:rgba(255,255,255,0.3);font-size:0.85rem;">Expected delivery: <strong style="color:#fff;">3–5 business days</strong></p>
            <button class="btn" style="background:var(--bm-red);color:#fff;border-radius:10px;padding:0.6rem 2rem;border:none;font-weight:600;margin-top:0.5rem;" data-bs-dismiss="modal">Continue Shopping</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let cart = [];
    let currentStep = 1;
    let selectedPayment = '';

    const cartModalEl   = new bootstrap.Modal(document.getElementById('cartModal'));
    const checkoutModalEl = new bootstrap.Modal(document.getElementById('checkoutModal'));
    const successModalEl  = new bootstrap.Modal(document.getElementById('successModal'));

    const payLabels = {
        gcash: '💙 GCash',
        paymaya: '💚 PayMaya',
        maribank: '🟣 Maribank',
        metrobank: '🏦 Metrobank',
        cod: '💵 Cash on Delivery'
    };

    // Add to cart
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', () => addToCart(btn.dataset.name, Number(btn.dataset.price)));
    });

    document.getElementById('cartBtn').addEventListener('click', () => cartModalEl.show());

    document.getElementById('proceedCheckoutBtn').addEventListener('click', () => {
        if (!cart.length) { alert('⚠️ Your cart is empty!'); return; }
        cartModalEl.hide();
        setTimeout(() => { resetCheckout(); checkoutModalEl.show(); }, 350);
    });

    document.getElementById('searchInput').addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('.product-col').forEach(col => {
            col.style.display = col.dataset.name.includes(q) ? '' : 'none';
        });
    });

    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const f = btn.dataset.filter;
            document.querySelectorAll('.product-col').forEach(col => {
                col.style.display = (f === 'all' || col.dataset.category === f) ? '' : 'none';
            });
        });
    });

    // Category card filter click
    document.querySelectorAll('.category-card[data-category-filter]').forEach(card => {
        card.addEventListener('click', (e) => {
            e.preventDefault();
            const filter = card.dataset.categoryFilter;
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('active');
                if (b.dataset.filter === filter) b.classList.add('active');
            });
            document.querySelectorAll('.product-col').forEach(col => {
                col.style.display = col.dataset.category === filter ? '' : 'none';
            });
            document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
        });
    });

    document.getElementById('nextBtn').addEventListener('click', () => {
        if (currentStep === 1) {
            const required = ['fname','lname','phone','street','city','province'];
            let valid = true;
            required.forEach(id => {
                const el = document.getElementById(id);
                if (!el.value.trim()) { el.style.borderColor = '#e74c3c'; valid = false; }
                else el.style.borderColor = 'var(--bm-border)';
            });
            if (!valid) { alert('⚠️ Please fill in all required fields.'); return; }
            goToStep(2);
        } else if (currentStep === 2) {
            if (!selectedPayment) { document.getElementById('pay-error').classList.remove('d-none'); return; }
            document.getElementById('pay-error').classList.add('d-none');
            buildConfirmation();
            goToStep(3);
        } else if (currentStep === 3) {
            placeOrder();
        }
    });

    document.getElementById('backBtn').addEventListener('click', () => {
        if (currentStep > 1) goToStep(currentStep - 1);
    });

    function goToStep(step) {
        document.querySelectorAll('.checkout-step').forEach(s => s.classList.remove('active'));
        document.getElementById('step' + step).classList.add('active');
        [1,2,3].forEach(n => {
            const dot = document.getElementById('dot' + n);
            const div = dot.querySelector('div:first-child');
            dot.classList.remove('active');
            if (n < step) {
                div.style.background = 'var(--bm-red)';
                div.style.borderColor = 'var(--bm-red)';
                div.style.color = '#fff';
            } else if (n === step) {
                div.style.background = 'transparent';
                div.style.borderColor = 'var(--bm-red)';
                div.style.color = '#fff';
            } else {
                div.style.background = 'transparent';
                div.style.borderColor = 'rgba(255,255,255,0.1)';
                div.style.color = 'rgba(255,255,255,0.3)';
            }
        });
        currentStep = step;
        const backBtn = document.getElementById('backBtn');
        const nextBtn = document.getElementById('nextBtn');
        backBtn.style.setProperty('display', step > 1 ? 'inline-block' : 'none', 'important');
        nextBtn.textContent = step === 3 ? '✅ Place Order' : 'Next →';
    }

    function resetCheckout() {
        selectedPayment = '';
        document.querySelectorAll('.pay-card').forEach(c => {
            c.style.borderColor = 'var(--bm-border)';
            c.style.background = 'rgba(255,255,255,0.02)';
        });
        document.querySelectorAll('.payment-option').forEach(o => o.style.display = 'none');
        document.getElementById('pay-error').classList.add('d-none');
        goToStep(1);
    }

    function selectPayment(card) {
        document.querySelectorAll('.pay-card').forEach(c => {
            c.style.borderColor = 'var(--bm-border)';
            c.style.background = 'rgba(255,255,255,0.02)';
        });
        card.style.borderColor = 'var(--bm-red)';
        card.style.background = 'rgba(192,57,43,0.1)';
        selectedPayment = card.dataset.pay;
        document.querySelectorAll('.payment-option').forEach(o => o.style.display = 'none');
        document.getElementById('pay-' + selectedPayment).style.display = 'block';
        document.getElementById('pay-error').classList.add('d-none');
    }

    function buildConfirmation() {
        const fname = document.getElementById('fname').value;
        const lname = document.getElementById('lname').value;
        const phone = document.getElementById('phone').value;
        const email = document.getElementById('email').value;
        const street = document.getElementById('street').value;
        const city = document.getElementById('city').value;
        const province = document.getElementById('province').value;
        const zip = document.getElementById('zip').value;
        const notes = document.getElementById('notes').value;

        document.getElementById('confirm-address').innerHTML = `
            <strong>${fname} ${lname}</strong><br>
            ${phone}${email ? ' · ' + email : ''}<br>
            ${street}<br>
            ${city}, ${province}${zip ? ' ' + zip : ''}<br>
            ${notes ? '<span style="color:rgba(255,255,255,0.3);">Note: ' + notes + '</span>' : ''}
        `;
        document.getElementById('confirm-payment').textContent = payLabels[selectedPayment] || selectedPayment;

        let itemsHTML = '';
        let total = 0;
        cart.forEach(item => {
            total += item.price;
            itemsHTML += `<div style="display:flex;justify-content:space-between;padding:0.3rem 0;border-bottom:1px solid rgba(255,255,255,0.03);">
                <span style="color:rgba(255,255,255,0.5);font-size:0.85rem;">${item.name}</span>
                <span style="color:#fff;font-size:0.85rem;">$${item.price.toLocaleString()}</span>
            </div>`;
        });
        document.getElementById('confirm-items').innerHTML = itemsHTML;
        document.getElementById('confirm-total').textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2 });
    }

    function placeOrder() {
        // Validate all required fields
        const required = ['fname','lname','phone','street','city','province'];
        let valid = true;
        required.forEach(id => {
            const el = document.getElementById(id);
            if (!el.value.trim()) { 
                el.style.borderColor = '#e74c3c'; 
                valid = false; 
            } else {
                el.style.borderColor = 'var(--bm-border)';
            }
        });
        if (!valid) { 
            alert('⚠️ Please fill in all required fields.'); 
            return; 
        }
        if (!selectedPayment) { 
            alert('⚠️ Please select a payment method.'); 
            return; 
        }
        if (!cart.length) { 
            alert('⚠️ Your cart is empty!'); 
            return; 
        }

        const orderData = {
            first_name: document.getElementById('fname').value.trim(),
            last_name: document.getElementById('lname').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            email: document.getElementById('email').value.trim(),
            street: document.getElementById('street').value.trim(),
            city: document.getElementById('city').value.trim(),
            province: document.getElementById('province').value.trim(),
            zip: document.getElementById('zip').value.trim(),
            notes: document.getElementById('notes').value.trim(),
            payment_method: selectedPayment,
            items: cart,
            total: cart.reduce((sum, item) => sum + item.price, 0)
        };

        const nextBtn = document.getElementById('nextBtn');
        nextBtn.textContent = '⏳ Processing...';
        nextBtn.disabled = true;

        fetch('save_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                checkoutModalEl.hide();
                cart = [];
                updateCartUI();
                selectedPayment = '';
                setTimeout(() => successModalEl.show(), 400);
                alert('✅ Order #' + data.order_number + ' placed successfully!');
            } else {
                alert('Error: ' + data.message);
                nextBtn.textContent = '✅ Place Order';
                nextBtn.disabled = false;
            }
        })
        .catch(error => {
            alert('An error occurred: ' + error.message);
            nextBtn.textContent = '✅ Place Order';
            nextBtn.disabled = false;
        });
    }

    function addToCart(name, price) {
        cart.push({ name, price });
        updateCartUI();
        alert(`🛒 ${name} added to cart!`);
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        updateCartUI();
        alert('🗑️ Item removed from cart.');
    }

    function updateCartUI() {
        document.getElementById('cartCount').textContent = cart.length;
        const listEl = document.getElementById('cartItemsList');
        const emptyEl = document.getElementById('cartEmpty');
        const totalRow = document.getElementById('cartTotalRow');
        listEl.innerHTML = '';
        if (!cart.length) {
            emptyEl.style.display = 'block';
            totalRow.classList.add('d-none');
            return;
        }
        emptyEl.style.display = 'none';
        totalRow.classList.remove('d-none');
        let total = 0;
        cart.forEach((item, i) => {
            total += item.price;
            const row = document.createElement('div');
            row.style.cssText = 'display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);';
            row.innerHTML = `
                <span style="color:#fff;flex:1;">${item.name}</span>
                <span style="color:var(--bm-red);font-weight:600;min-width:70px;text-align:right;">$${item.price.toLocaleString()}</span>
                <button onclick="removeFromCart(${i})" style="background:transparent;border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.3);border-radius:6px;padding:0.1rem 0.5rem;font-size:0.7rem;cursor:pointer;transition:all 0.3s;">✕</button>
            `;
            listEl.appendChild(row);
        });
        document.getElementById('cartTotal').textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2 });
    }

    // Checkout close handler
    document.getElementById('checkoutCloseBtn').addEventListener('click', () => {
        resetCheckout();
    });

    document.getElementById('cancelCheckoutBtn').addEventListener('click', () => {
        resetCheckout();
    });

    updateCartUI();
</script>
</body>
</html>