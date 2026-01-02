<?php
session_start();
require_once 'db.php';

$count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    // Use COUNT(*) for unique items or SUM(quantity) for total pieces
    $stmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0];
}

echo json_encode(['count' => $count]);