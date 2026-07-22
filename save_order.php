<?php
session_start();
include "db.php";

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit();
}

// Get user_id if logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Generate order number
$order_number = 'ORD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Escape all data for safety
$first_name = mysqli_real_escape_string($conn, $data['first_name']);
$last_name = mysqli_real_escape_string($conn, $data['last_name']);
$phone = mysqli_real_escape_string($conn, $data['phone']);
$email = mysqli_real_escape_string($conn, $data['email']);
$street = mysqli_real_escape_string($conn, $data['street']);
$city = mysqli_real_escape_string($conn, $data['city']);
$province = mysqli_real_escape_string($conn, $data['province']);
$zip = mysqli_real_escape_string($conn, $data['zip']);
$notes = mysqli_real_escape_string($conn, $data['notes']);
$payment_method = mysqli_real_escape_string($conn, $data['payment_method']);
$total = (float)$data['total'];

// Insert order
$sql = "INSERT INTO orders (
    user_id, order_number, first_name, last_name, phone, email, 
    street, city, province, zip_code, delivery_notes, 
    payment_method, total_amount, status
) VALUES (
    " . ($user_id ? $user_id : 'NULL') . ", 
    '$order_number', 
    '$first_name', 
    '$last_name', 
    '$phone', 
    '$email', 
    '$street', 
    '$city', 
    '$province', 
    '$zip', 
    '$notes', 
    '$payment_method', 
    $total, 
    'pending'
)";

if (mysqli_query($conn, $sql)) {
    $order_id = mysqli_insert_id($conn);
    
    // Insert order items
    foreach ($data['items'] as $item) {
        $product_name = mysqli_real_escape_string($conn, $item['name']);
        $product_price = (float)$item['price'];
        
        $item_sql = "INSERT INTO order_items (order_id, product_name, product_price, quantity) 
                     VALUES ($order_id, '$product_name', $product_price, 1)";
        mysqli_query($conn, $item_sql);
    }
    
    echo json_encode(['success' => true, 'order_id' => $order_id, 'order_number' => $order_number]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}
?>