<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

/* ==========================
FETCH CART ITEMS
========================== */
$sql = "
SELECT 
    c.cart_id,
    c.quantity,
    p.name AS product_name,
    p.price AS base_price,
    p.image_url AS product_image,
    v.variant_name,
    v.variant_value,
    v.price_adjustment,
    v.img_url AS variant_image
FROM cart c
JOIN products p ON c.product_id = p.product_id
JOIN product_variants v ON c.variant_id = v.variant_id
WHERE c.user_id = ?
ORDER BY c.updated_at DESC, c.cart_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $row['final_price'] = $row['base_price'] + $row['price_adjustment'];
    $row['subtotal']    = $row['final_price'] * $row['quantity'];
    $row['display_image'] = (!empty($row['variant_image'])) ? $row['variant_image'] : $row['product_image'];
    $cart_items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="img/media/logo2.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/header.css">
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
    /* Theme Variables */
    :root {
        --dark-red: #a30000;
        --dark-red-hover: #800000;
        --accent-black: #1a1a1a;
    }

    /* Black & White Checkbox Logic */
    .outline-checkbox {
        width: 18px; /* Made slightly smaller */
        height: 18px;
        cursor: pointer;
        border: 2px solid #3333332b;
        border-radius: 5px;
        appearance: none;
        background-color: #fff;
        position: relative;
        transition: all 0.2s ease;
    }

    .outline-checkbox:checked {
        background-color: var(--accent-black);
        border-color: var(--accent-black);
    }

    .outline-checkbox:checked::after {
        content: '\F26E';
        font-family: 'bootstrap-icons';
        position: absolute;
        color: white;
        font-size: 14px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Smaller Item Images */
    .cart-item-img {
        width: 65px; /* Reduced size */
        height: 65px;
        object-fit: cover;
        border-radius: 8px;
        margin-left: 10px; /* Moves image a bit to the right */
    }

    /* Quantity Selector */
    .qty-container {
        display: flex;
        align-items: center;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
        width: fit-content;
    }
    .qty-btn {
        background: #f8f9fa;
        border: none;
        padding: 6px 12px;
        font-size: 1.1rem;
        color: var(--accent-black);
        text-decoration: none !important;
        transition: background 0.2s;
    }
    .qty-btn:hover {
        background: #e9ecef;
    }
    .qty-input {
        width: 40px;
        text-align: center;
        border: none;
        font-weight: 700;
        background: transparent;
    }

    .btn-dark-red {
        background-color: var(--dark-red);
        border-color: var(--dark-red);
        color: white;
    }
    .btn-dark-red:hover {
        background-color: var(--dark-red-hover);
        border-color: var(--dark-red-hover);
        color: white;
    }

    .cart-item-row {
        border-radius: 12px;
        overflow: hidden;
    }
    
    @media (max-width: 576px) {
        .product-info-col { width: 100%; }
        .subtotal-display { font-size: 1.1rem !important; }
        .qty-container { transform: scale(0.9); transform-origin: left; }
        .cart-item-row .row { row-gap: 10px; }
        .cart-item-img { margin-left: 5px; } /* Less margin on mobile */
    }

    .sticky-bottom-card {
        position: sticky;
        bottom: 0;
        z-index: 1020;
        border-radius: 15px 15px 0 0;
    }

    .checkout-actions-wrapper {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
    }

    .btn-delete:hover {
        color: var(--dark-red);
        transform: scale(1.1);
    }
</style>
</head>
<body class="bg-light">

    <?php include 'header.php'; ?>

    <div class="container py-4">
        <h2 class="fw-bold mb-4">🛒 Your Cart</h2>

        <?php if (empty($cart_items)): ?>
            <div class="alert alert-light shadow-sm text-center py-5">
                <i class="bi bi-cart-x fs-1 text-muted"></i>
                <p class="mt-3 text-muted">Your cart is empty.</p>
                <a href="../welcome.php" class="btn btn-danger px-4 rounded-pill">Shop Now</a>
            </div>
        <?php else: ?>

        <form action="checkout.php" method="POST" id="cartForm">
            <div class="pb-2"> 
                <?php foreach ($cart_items as $item): ?>
                    <div class="card shadow-sm border-0 mb-3 cart-item-row" 
                         data-cart-id="<?= $item['cart_id'] ?>" 
                         data-price="<?= $item['final_price'] ?>">
                        <div class="card-body p-3">
                            <div class="row align-items-center g-2 g-md-3">
                                
                                <div class="col-12 col-md-5 d-flex align-items-center product-info-col">
                                    <input type="checkbox" class="form-check-input cart-checkbox outline-checkbox me-2 mt-0 flex-shrink-0" name="cart_id[]" value="<?= $item['cart_id'] ?>">
                                    
                                    <img src="<?= htmlspecialchars($item['display_image']) ?>" 
                                         class="cart-item-img shadow-sm flex-shrink-0" alt="Product">
                                    
                                    <div class="ms-3 min-width-0">
                                        <h6 class="fw-bold mb-0 text-truncate" style="font-size: 0.95rem;"><?= htmlspecialchars($item['product_name']) ?></h6>
                                        <small class="text-muted d-block text-truncate">
                                            <?= htmlspecialchars($item['variant_name'] ?? 'Variant') ?>: <?= htmlspecialchars($item['variant_value']) ?>
                                        </small>
                                        <div class="d-md-none fw-semibold text-muted small mt-1">
                                            ₱<?= number_format($item['final_price'], 2) ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2 d-none d-md-block text-center">
                                    <span class="fw-semibold">₱<?= number_format($item['final_price'], 2) ?></span>
                                </div>

                                <div class="col-6 col-md-2 d-flex justify-content-start justify-content-md-center">
                                    <div class="qty-container shadow-sm">
                                        <button class="qty-btn" type="button" data-action="minus"><i class="bi bi-dash"></i></button>
                                        <input type="text" class="qty-input" value="<?= $item['quantity'] ?>" readonly>
                                        <button class="qty-btn" type="button" data-action="plus"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>

                                <div class="col-6 col-md-3 d-flex align-items-center justify-content-end gap-3">
                                    <div class="text-end">
                                        <small class="text-muted d-block d-md-none">Subtotal</small>
                                        <span class="fw-bold text-danger fs-5 subtotal-display">
                                            ₱<?= number_format($item['subtotal'], 2) ?>
                                        </span>
                                    </div>
                                    <button type="button" class="btn btn-link btn-delete p-0 text-secondary delete-item">
                                        <i class="bi bi-trash3-fill fs-5"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card border-0 shadow-lg mt-4 sticky-bottom-card overflow-hidden">
                <div class="card-body p-4 bg-white">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h5 class="fw-bold mb-0">Total Estimated</h5>
                            <small class="text-muted">+ ₱100 Shipping Fee (applied when items selected)</small>
                        </div>
                        <div class="col-md-6 checkout-actions-wrapper">
                            <h3 class="fw-bold text-danger mb-0 me-md-4" id="estimatedTotal">₱0.00</h3>
                            <div class="d-flex gap-2 w-100 w-md-auto">
                                <a href="../welcome.php" class="btn btn-outline-dark px-4 flex-grow-1">Add More</a>
                                <button type="submit" class="btn btn-dark-red px-5 fw-bold shadow-sm flex-grow-1">
                                    Checkout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <script src="js/cartItem.js"></script>
</body>
</html>