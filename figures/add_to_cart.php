<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$required = ['product_id', 'variant_id', 'quantity'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
        exit;
    }
}

$user_id    = (int) $_SESSION['user_id'];
$product_id = (int) $_POST['product_id'];
$variant_id = (int) $_POST['variant_id'];
$quantity   = (int) $_POST['quantity'];

if ($quantity < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit;
}

require_once '../db.php';

/* ==========================
   CHECK IF ITEM EXISTS
========================== */
$check_sql = "
    SELECT cart_id, quantity 
    FROM cart 
    WHERE user_id = ? AND product_id = ? AND variant_id = ?
";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("iii", $user_id, $product_id, $variant_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($row = $result->fetch_assoc()) {

    /* ==========================
       UPDATE EXISTING ITEM
       (updated_at auto-refreshes)
    ========================== */
    $new_qty = $row['quantity'] + $quantity;
    $cart_id = $row['cart_id'];

    $update_stmt = $conn->prepare(
        "UPDATE cart SET quantity = ? WHERE cart_id = ?"
    );
    $update_stmt->bind_param("ii", $new_qty, $cart_id);

    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }

    $update_stmt->close();

} else {

    /* ==========================
       INSERT NEW ITEM
    ========================== */
    $insert_stmt = $conn->prepare(
        "INSERT INTO cart (user_id, product_id, variant_id, quantity)
         VALUES (?, ?, ?, ?)"
    );
    $insert_stmt->bind_param(
        "iiii",
        $user_id,
        $product_id,
        $variant_id,
        $quantity
    );

    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Insert failed']);
    }

    $insert_stmt->close();
}

$check_stmt->close();
$conn->close();
