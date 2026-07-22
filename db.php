<?php
// db.php - Database connection with all tables
$host = "localhost";
$user = "root";
$password = "";
$database = "blackmarket_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8");

// Create users table
$createTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $createTable);

// Create suppliers table
$createTable = "CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $createTable);

// Create supplier_contracts table
$createTable = "CREATE TABLE IF NOT EXISTS supplier_contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    contract_number VARCHAR(100) UNIQUE NOT NULL,
    contract_date DATE NOT NULL,
    expiry_date DATE,
    contract_value DECIMAL(15,2) DEFAULT 0.00,
    payment_terms VARCHAR(255),
    delivery_terms VARCHAR(255),
    status ENUM('active', 'expired', 'pending', 'terminated') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
)";
mysqli_query($conn, $createTable);

// Create supplier_items table
$createTable = "CREATE TABLE IF NOT EXISTS supplier_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_category VARCHAR(100),
    unit_price DECIMAL(10,2) DEFAULT 0.00,
    lead_time_days INT DEFAULT 7,
    minimum_order INT DEFAULT 1,
    is_active TINYINT DEFAULT 1,
    notes TEXT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
)";
mysqli_query($conn, $createTable);

// Create inventory table
$createTable = "CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    quantity INT DEFAULT 0,
    reorder_level INT DEFAULT 5,
    unit_price DECIMAL(10,2),
    supplier_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
)";
mysqli_query($conn, $createTable);

// Create orders table with updated_at for tracking
$createTable = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NULL,
    street VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20) NULL,
    delivery_notes TEXT NULL,
    payment_method VARCHAR(50) NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";
mysqli_query($conn, $createTable);

// Create order_items table
$createTable = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";
mysqli_query($conn, $createTable);

// Create activity_log table
$createTable = "CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(255),
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
)";
mysqli_query($conn, $createTable);

// Check and add supplier_id to inventory if not exists
$checkColumn = "SHOW COLUMNS FROM inventory LIKE 'supplier_id'";
$result = mysqli_query($conn, $checkColumn);
if (mysqli_num_rows($result) == 0) {
    $alterTable = "ALTER TABLE inventory ADD COLUMN supplier_id INT, ADD FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL";
    mysqli_query($conn, $alterTable);
}

// Check and add updated_at to orders if not exists
$checkColumn = "SHOW COLUMNS FROM orders LIKE 'updated_at'";
$result = mysqli_query($conn, $checkColumn);
if (mysqli_num_rows($result) == 0) {
    $alterTable = "ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    mysqli_query($conn, $alterTable);
}

// Check and add zip_code to orders if not exists
$checkColumn = "SHOW COLUMNS FROM orders LIKE 'zip_code'";
$result = mysqli_query($conn, $checkColumn);
if (mysqli_num_rows($result) == 0) {
    $alterTable = "ALTER TABLE orders ADD COLUMN zip_code VARCHAR(20) NULL";
    mysqli_query($conn, $alterTable);
}

// Check and add delivery_notes to orders if not exists
$checkColumn = "SHOW COLUMNS FROM orders LIKE 'delivery_notes'";
$result = mysqli_query($conn, $checkColumn);
if (mysqli_num_rows($result) == 0) {
    $alterTable = "ALTER TABLE orders ADD COLUMN delivery_notes TEXT NULL";
    mysqli_query($conn, $alterTable);
}
?>