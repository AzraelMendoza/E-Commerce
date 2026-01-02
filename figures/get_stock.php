<?php
include '../db.php'; 

// Ensure headers tell the browser to expect JSON
header('Content-Type: application/json');

if (isset($_GET['variant_id'])) {
    $variant_id = intval($_GET['variant_id']);
    
    // Using mysqli syntax
    $stmt = $conn->prepare("SELECT stock_quantity FROM product_variants WHERE variant_id = ?");
    $stmt->bind_param("i", $variant_id); // "i" means integer
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        echo json_encode(['success' => true, 'stock' => (int)$row['stock_quantity']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Variant not found']);
    }
    $stmt->close();
}
?>