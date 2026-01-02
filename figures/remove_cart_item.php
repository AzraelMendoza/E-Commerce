<?php
        session_start();
        require_once '../db.php';

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $user_id = (int) $_SESSION['user_id'];
        $cart_id = (int) ($_POST['cart_id'] ?? 0);

        if ($cart_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid item']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
?>
