<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/* ==========================
   HANDLE STATUS UPDATES
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    $action = $_POST['action'];

    if ($action === 'order_received') {
        // We update the status AND the updated_at timestamp so it jumps to the top
        $stmt = $conn->prepare("UPDATE orders SET status = 'completed', updated_at = NOW() WHERE order_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
    } 
    elseif ($action === 'cancel_order') {
        // Restore Stock logic
        $itemsQuery = $conn->prepare("SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?");
        $itemsQuery->bind_param("i", $order_id);
        $itemsQuery->execute();
        $itemsToRestore = $itemsQuery->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($itemsToRestore as $item) {
            $qty = $item['quantity'];
            if (!empty($item['variant_id'])) {
                $updateStock = $conn->prepare("UPDATE product_variants SET stock_quantity = stock_quantity + ? WHERE variant_id = ?");
                $updateStock->bind_param("ii", $qty, $item['variant_id']);
            } else {
                $updateStock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
                $updateStock->bind_param("ii", $qty, $item['product_id']);
            }
            $updateStock->execute();
        }

        $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE order_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
    }
    header("Location: order_success.php");
    exit;
}

/* ==========================
   FETCH AND CATEGORIZE ORDERS
   Sorted by updated_at so the most recent action is at the top
========================== */
$orderStmt = $conn->prepare("SELECT order_id, total_amount, status, created_at, updated_at FROM orders WHERE user_id = ? ORDER BY updated_at DESC");
$orderStmt->bind_param("i", $user_id);
$orderStmt->execute();
$all_orders = $orderStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pending = [];
$completed = [];
$cancelled = [];

foreach ($all_orders as $o) {
    if ($o['status'] === 'completed') $completed[] = $o;
    elseif ($o['status'] === 'cancelled') $cancelled[] = $o;
    else $pending[] = $o; 
}

$itemStmt = $conn->prepare("
    SELECT oi.quantity, oi.price, p.name AS product_name, 
    COALESCE(v.img_url, p.image_url) AS display_image, v.variant_value
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    LEFT JOIN product_variants v ON oi.variant_id = v.variant_id
    WHERE oi.order_id = ?
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/header.css">
     <link rel="icon" type="image/png" href="img/media/logo2.png">
    <link rel="stylesheet" href="css/order.css">
</head>
<body>
    <div class="container py-5" style="max-width: 800px;">
        
        <a href="../welcome.php" class="btn-back-home">
            <i class="bi bi-arrow-left me-2"></i> Back to Home
        </a>

        <h2 class="fw-bold mb-4">My Purchases</h2>

        <ul class="nav nav-tabs mb-4" id="orderTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-pending">Active</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-completed">Completed</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-cancelled">History</button></li>
        </ul>

        <div class="tab-content">
            <?php 
            $sections = ['pending' => $pending, 'completed' => $completed, 'cancelled' => $cancelled];
            foreach ($sections as $id => $order_list): 
            ?>
                <div class="tab-pane fade <?= $id === 'pending' ? 'show active' : '' ?>" id="tab-<?= $id ?>">
                    <?php if (empty($order_list)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-bag text-light" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3">Nothing to show here yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($order_list as $order): ?>
                            <div class="card order-card shadow-sm">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <div class="text-muted small text-uppercase fw-bold" style="letter-spacing: 1px;">Ordered On</div>
                                            <div class="date-header"><?= date('F d, Y', strtotime($order['created_at'])) ?></div>
                                        </div>
                                        <span class="status-badge status-<?= $order['status'] ?>">
                                            <?= $order['status'] ?>
                                        </span>
                                    </div>

                                    <?php
                                    $itemStmt->bind_param("i", $order['order_id']);
                                    $itemStmt->execute();
                                    $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    foreach ($items as $item):
                                    ?>
                                        <div class="d-flex align-items-center py-3 border-top border-light">
                                            <img src="<?= htmlspecialchars($item['display_image']) ?>" class="product-img me-3">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold fs-6"><?= htmlspecialchars($item['product_name']) ?></div>
                                                <div class="text-muted small">
                                                    <?= htmlspecialchars($item['variant_value'] ?? 'Standard') ?> <span class="mx-1">•</span> Qty: <?= $item['quantity'] ?>
                                                </div>
                                            </div>
                                            <div class="fw-bold">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                        <span class="text-muted fw-bold">Grand Total</span>
                                        <span class="text-dark fw-bold fs-4">₱<?= number_format($order['total_amount'], 2) ?></span>
                                    </div>

                                    <div class="d-flex gap-2 mt-4">
                                        <?php if ($id === 'pending'): ?>
                                            <form method="POST" class="m-0 d-flex gap-2 w-100">
                                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                                <button type="submit" name="action" value="order_received" class="btn-black flex-grow-1">Confirm Received</button>
                                                <button type="submit" name="action" value="cancel_order" class="btn btn-light border px-4 fw-bold" onclick="return confirm('Cancel this order?')">Cancel</button>
                                            </form>
                                        <?php elseif ($id === 'completed'): ?>
                                            <a href="write_review.php?order_id=<?= $order['order_id'] ?>" class="btn-black flex-grow-1 text-center" style="text-decoration:none;">Write a Review</a>
                                            <a href="figures.php" class="btn-outline-red flex-grow-1">Shop More</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
