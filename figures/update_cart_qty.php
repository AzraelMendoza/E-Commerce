<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$cart_id = (int)($_POST['cart_id'] ?? 0);
$action  = $_POST['action'] ?? '';

if (!$cart_id || !in_array($action, ['plus', 'minus'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

if ($action === 'plus') {
    $sql = "UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ?";
} else {
    $sql = "UPDATE cart SET quantity = GREATEST(quantity - 1, 1) WHERE cart_id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cart_id);
$stmt->execute();

echo json_encode(['success' => true]);
