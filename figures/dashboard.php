<?php
session_start();
require_once 'db.php';

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
        $stmt = $conn->prepare("UPDATE orders SET status = 'completed', updated_at = NOW() WHERE order_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
    } 
    elseif ($action === 'cancel_order') {
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
    header("Location: dashboard.php?update=success");
    exit;
}

/* ==========================
    FETCH USER DETAILS
========================== */
$userStmt = $conn->prepare("SELECT email, username, avatar_url FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userName = $user['username'] ?? "User";
$avatar = !empty($user['avatar_url']) ? $user['avatar_url'] : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';

/* ==========================
    FETCH AND CATEGORIZE ORDERS
========================== */
$orderStmt = $conn->prepare("SELECT order_id, total_amount, status, created_at, updated_at FROM orders WHERE user_id = ? ORDER BY updated_at DESC");
$orderStmt->bind_param("i", $user_id);
$orderStmt->execute();
$all_orders = $orderStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pending = []; $completed = []; $cancelled = [];
foreach ($all_orders as $o) {
    if ($o['status'] === 'completed') $completed[] = $o;
    elseif ($o['status'] === 'cancelled') $cancelled[] = $o;
    else $pending[] = $o; 
}

$itemStmt = $conn->prepare("
    SELECT oi.quantity, oi.price, p.name AS product_name, p.image_url AS main_img, v.variant_value, v.img_url AS variant_img
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
    <title>Dashboard | <?= htmlspecialchars($userName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/header.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Inter', sans-serif; }
        .hero-section { background: #fff; padding: 40px 0; border-bottom: 1px solid #e9ecef; }
        .profile-avatar { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .nav-tabs .nav-link { color: #6c757d; border: none; font-weight: 600; padding: 12px 20px; }
        .nav-tabs .nav-link.active { color: #dc3545; border-bottom: 3px solid #dc3545; background: none; }
        .status-badge { border-radius: 20px; padding: 5px 15px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; border: 1px solid; }
        .status-completed { background-color: #ebfbee; color: #2b8a3e; border-color: #b2f2bb; }
        .status-pending { background-color: #fff9db; color: #f08c00; border-color: #ffe066; }
        .status-cancelled { background-color: #fff5f5; color: #c92a2a; border-color: #ffc9c9; }
        .order-card { background: #fff; border-radius: 12px; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.05); }
        .product-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .btn-black { background: #1a1a1a; color: #fff; border-radius: 6px; padding: 8px 18px; font-weight: 600; border: none; text-decoration: none; display: inline-block; }
        .btn-back-home { text-decoration: none; color: #444; font-size: 0.85rem; font-weight: 600; padding: 8px 18px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 50px; transition: all 0.3s ease; display: inline-flex; align-items: center; }
        .btn-back-home i { transition: transform 0.3s ease; }
        .btn-back-home:hover { color: #000; background: #fff; border-color: #ced4da; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .btn-back-home:hover i { transform: translateX(-4px); }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="hero-section text-center position-relative">
    <div class="container">
        <div class="position-absolute top-0 start-0 p-4">
            <a href="../welcome.php" class="btn-back-home">
                <i class="bi bi-arrow-left me-2"></i> Back to Home
            </a>
        </div>

        <div class="position-absolute top-0 end-0 p-4 d-flex gap-2">
            <a href="edit_profile.php" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold">
                <i class="bi bi-gear-fill me-1"></i> Settings
            </a>
            <a href="../logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">
                Logout
            </a>
        </div>

        <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="profile-avatar mb-3">
        <h2 class="fw-bold mb-1">Howdy, <?= htmlspecialchars($userName) ?>!</h2>
        <p class="text-muted small mb-0"><?= htmlspecialchars($user['email']) ?></p>
    </div>
</div>

<div class="container py-5" style="max-width: 900px;">
    
    <ul class="nav nav-tabs mb-4 justify-content-center" id="orderTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-pending">Active</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-completed">Completed</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-cancelled">History</button>
        </li>
    </ul>

    <div class="tab-content">
        <?php 
        $tabs = ['pending' => $pending, 'completed' => $completed, 'cancelled' => $cancelled];
        foreach ($tabs as $id => $list): 
        ?>
            <div class="tab-pane fade <?= $id === 'pending' ? 'show active' : '' ?>" id="tab-<?= $id ?>">
                <?php if (empty($list)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-box2 text-light display-1"></i>
                        <p class="text-muted mt-3">Nothing to show here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($list as $order): 
                        $statusClass = "status-" . ($order['status'] === 'completed' ? 'completed' : ($order['status'] === 'cancelled' ? 'cancelled' : 'pending'));
                    ?>
                        <div class="card order-card shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Order Date</small>
                                        <span class="fw-bold"><?= date('F d, Y', strtotime($order['created_at'])) ?></span>
                                    </div>
                                    <span class="status-badge <?= $statusClass ?>"><?= $order['status'] ?></span>
                                </div>

                                <?php
                                $itemStmt->bind_param("i", $order['order_id']);
                                $itemStmt->execute();
                                $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                foreach ($items as $item):
                                    $displayImg = (!empty($item['variant_img'])) ? $item['variant_img'] : $item['main_img'];
                                ?>
                                    <div class="d-flex align-items-center py-3 border-top">
                                        <img src="<?= htmlspecialchars($displayImg) ?>" class="product-img me-3 border">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($item['product_name']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($item['variant_value'] ?? 'Standard') ?> <span class="mx-1">•</span> Qty: <?= $item['quantity'] ?></small>
                                        </div>
                                        <div class="fw-bold">₱<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <div class="d-flex gap-2">
                                        <?php if ($id === 'pending'): ?>
                                            <form method="POST" class="m-0 d-flex gap-2">
                                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                                <button type="submit" name="action" value="order_received" class="btn-black btn-sm">Confirm Received</button>
                                                <button type="submit" name="action" value="cancel_order" class="btn btn-sm btn-light border" onclick="return confirm('Cancel this order?')">Cancel</button>
                                            </form>
                                        <?php elseif ($id === 'completed'): ?>
                                            <a href="write_review.php?order_id=<?= $order['order_id'] ?>" class="btn-black btn-sm">Rate & Review</a>
                                            <a href="figures.php" class="btn btn-sm btn-outline-danger px-3">Shop More</a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Total Amount</small>
                                        <span class="text-dark fw-bold fs-5">₱<?= number_format($order['total_amount'], 2) ?></span>
                                    </div>
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