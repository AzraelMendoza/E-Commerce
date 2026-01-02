<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login first']);
        exit;
    }

    $user_id    = (int)$_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    $variant_id = (int)$_POST['variant_id'];
    $quantity   = (int)$_POST['quantity'];

    // Update or Insert into Cart
    $stmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? AND product_id = ? AND variant_id = ?");
    $stmt->bind_param("iii", $user_id, $product_id, $variant_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->bind_param("ii", $quantity, $row['cart_id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, variant_id, quantity) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $user_id, $product_id, $variant_id, $quantity);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}