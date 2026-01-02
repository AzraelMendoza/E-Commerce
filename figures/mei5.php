<?php
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: ../login.php");
            exit;
        }

        include '../db.php';

        $productId = 435; // 🔁 Change this per product page

        /* ===============================
        FETCH PRODUCT DETAILS
        ================================ */
        $productQuery = "
            SELECT 
                name,
                description,
                brand,
                price,
                stock_quantity,
                image_url,
                weight,
                material,
                page_link,
                created_at
            FROM products
            WHERE product_id = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($productQuery);
        $stmt->bind_param("i", $productId);
        $stmt->execute();

        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            die("Product not found.");
        }


        /* ===============================
        FETCH PRODUCT VARIANTS
        ================================ */
        $query = "
            SELECT variant_id, variant_value, stock_quantity, price_adjustment
            FROM product_variants
            WHERE product_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        $variants = [];
        while ($row = $result->fetch_assoc()) {
            $variants[$row['variant_id']] = $row;
        }

        $initialStock = $variants[78]['stock_quantity'] ?? 70;

        /* ===============================
        FETCH REVIEWS (VERIFIED ONLY)
        ================================ */
        $reviewQuery = "
            SELECT 
                r.rating,
                r.comment,
                r.review_image,
                r.created_at,
                v.variant_value,
                u.username
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            LEFT JOIN product_variants v ON r.variant_id = v.variant_id
            WHERE r.product_id = ?
            AND r.order_id IS NOT NULL
            ORDER BY r.created_at DESC
        ";
        $stmt = $conn->prepare($reviewQuery);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $reviewsResult = $stmt->get_result();

        /* ===============================
        RATING SUMMARY
        ================================ */
        $summaryQuery = "
            SELECT 
                COUNT(*) AS total_reviews,
                AVG(rating) AS avg_rating
            FROM reviews
            WHERE product_id = ?
            AND order_id IS NOT NULL
        ";
        $stmt = $conn->prepare($summaryQuery);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $summary = $stmt->get_result()->fetch_assoc();

        $totalReviews = (int) $summary['total_reviews'];
        $avgRating = round($summary['avg_rating'], 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meilong 5x5 M</title>
    <link rel="icon" type="image/png" href="img/media/logo2.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/category.css">
    <link rel="stylesheet" href="css/banner.css"> 
    <link rel="stylesheet" href="css/welcome.css">
    <link rel="stylesheet" href="css/product.css">
    <link rel="stylesheet" href="css/add.css">
    <link rel="stylesheet" href="css/prodpic.css">
</head>
<body>
        <?php include 'header.php'; ?>

        <div class="container py-5">
                <div class="row g-5">
                    <!-- Product Images -->
                    <div class="col-lg-5">
                        <div class="sticky-top" style="top: 100px;">
                            <img id="mainImage" src="img/products/mei5.PNG" class="product-img shadow-sm mb-3 w-100 rounded" alt="Product Image">

                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <label>
                                    <input type="radio" name="imgSelect" value="img/products/mei5.PNG" checked hidden>
                                    <img src="img/products/mei5.PNG" class="thumb" onclick="document.getElementById('mainImage').src=this.src">
                                </label>
                                <label>
                                    <input type="radio" name="imgSelect" value="img/products/mei52.jpg" hidden>
                                    <img src="img/products/mei52.jpg" class="thumb" onclick="document.getElementById('mainImage').src=this.src">
                                </label>
                                <label>
                                    <input type="radio" name="imgSelect" value="img/products/mei53.jpg" hidden>
                                    <img src="img/products/mei53.jpg" class="thumb" onclick="document.getElementById('mainImage').src=this.src">
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="col-lg-7 ps-lg-5">
                            <h1 class="fw-bold"><?php echo htmlspecialchars($product['name']); ?></h1>
                            <p class="text-muted mt-1">⭐⭐⭐⭐⭐ <?php echo $avgRating; ?> (<?php echo $totalReviews; ?> reviews)</p>
                            <div class="price" id="price">₱<?php echo htmlspecialchars($product['price']); ?></div>

                            <div class="mt-3">
                                <p class="text-secondary" style="font-size: 0.95rem; line-height: 1.6;">
                                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                                </p>
                            </div>

                        <!-- Product Variants -->
                            <div class="mt-4">
                                <label class="fw-bold mb-2">Options</label>
                                <div class="d-flex gap-2">
                                    <?php 
                                        // 1. Fetch stock for variant 78
                                        $stock78 = $variants[78]['stock_quantity'] ?? 0;
                                        $is78Disabled = ($stock78 <= 0);

                                        // 2. Set initial values for display
                                        $initialStock = $stock78;
                                        $allOutOfStock = $is78Disabled;
                                    ?>

                                    <button type="button" 
                                            class="btn option-btn <?php echo !$is78Disabled ? 'active' : 'disabled-variant'; ?>" 
                                            data-price="499" 
                                            data-variant-id="78" 
                                            data-stock="<?php echo $stock78; ?>"
                                            <?php echo $is78Disabled ? 'disabled' : ''; ?>>
                                        Standard <?php echo $is78Disabled ? '(Sold Out)' : ''; ?>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="fw-bold mb-2">Quantity</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="custom-qty-wrapper">
                                        <button id="minus" type="button">-</button>
                                        <input type="number" id="qty" 
                                            value="<?php echo $allOutOfStock ? '0' : '1'; ?>" 
                                            min="<?php echo $allOutOfStock ? '0' : '1'; ?>"
                                            <?php echo $allOutOfStock ? 'readonly' : ''; ?>> 
                                        <button id="plus" type="button">+</button>
                                    </div>
                                    <span class="text-muted">Stock: <strong id="stockCount"><?php echo $initialStock; ?></strong></span>
                                </div>
                            </div>
                             
                            <div class="action-buttons mt-4">
                                <button type="button" id="addToCartBtn" class="btn-action btn-add"
                                        <?php echo $allOutOfStock ? 'disabled style="background: gray; cursor: not-allowed;"' : ''; ?>>
                                    Add to Cart
                                </button>
                                <button type="button" id="buyNowBtn" class="btn-action btn-buy" onclick="handleAction('buy')"
                                        <?php echo $allOutOfStock ? 'disabled style="background: #ccc; cursor: not-allowed;"' : ''; ?>>
                                    Buy Now
                                </button>
                            </div>

                            <form id="cartForm" action="buynow.php" method="POST" style="display:none;">
                                <input type="hidden" id="product_id" name="product_id" value="<?php echo $productId; ?>">
                                <input type="hidden" id="variant_id" name="variant_id" value="78">
                                <input type="hidden" id="formQty" name="quantity" value="<?php echo $allOutOfStock ? '0' : '1'; ?>">
                            </form>
                    </div>
                </div>
                <!--PRODUCT INFO -->
                <div class="info-box p-4 mt-5 border rounded bg-light">
                            <h4 class="fw-bold mb-3">Product Information</h4>

                            <div class="row mb-4">
                                <div class="col-md-6 border-end">
                                    <p class="mb-1">
                                        <strong>Brand:</strong>
                                        <?php echo htmlspecialchars($product['brand']); ?>
                                    </p>

                                    <p class="mb-1">
                                        <strong>Weight:</strong>
                                        <?php echo htmlspecialchars($product['weight']); ?>
                                    </p>

                                    <p class="mb-1">
                                        <strong>Returns:</strong>
                                        Free Returns
                                    </p>
                                </div>

                                <div class="col-md-6 ps-md-4">
                                    <p class="mb-1">
                                        <strong>Material:</strong>
                                        <?php echo htmlspecialchars($product['material']); ?>
                                    </p>

                                    <p class="mb-1">
                                        <strong>Stock:</strong>
                                        <?php echo (int)$product['stock_quantity']; ?>
                                    </p>

                                    <p class="mb-1">
                                        <strong>Ships From:</strong>
                                        San Fernando, Pampanga
                                    </p>
                                </div>
                            </div>

                            <hr>

                            <h4 class="fw-bold mt-4">Product Description</h4>

                            <p class="mt-2 text-secondary">
                                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                            </p>
                </div>
        </div>

        <!--CUSTOMER REVIEWS -->
        <div class="container mb-5">
            <div class="p-4 border rounded bg-white shadow-sm">
                <h4 class="fw-bold mb-4">Customer Reviews</h4>

                <?php if ($reviewsResult->num_rows > 0): ?>
                    <div class="review-list">
                        <?php while ($review = $reviewsResult->fetch_assoc()): ?>
                            <div class="py-3 border-bottom">
                                
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark">
                                        <?php
                                            $name = $review['username'] ?? 'User';
                                            echo substr($name, 0, 1) . '***' . substr($name, -1);
                                        ?>
                                    </span>
                                    <small class="text-muted"><?php echo date('Y-m-d', strtotime($review['created_at'])); ?></small>
                                </div>

                                <div class="text-warning mb-2" style="font-size: 0.85rem;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                                    <?php endfor; ?>
                                </div>

                                <p class="text-secondary mb-2" style="font-size: 0.95rem;">
                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                </p>

                                <?php 
                                    // 1. Get the path directly from the DB column
                                    // Your DB currently stores: "img/reviews/rev_1766576191_23_400_1.jpg"
                                    $dbPath = trim($review['review_image'] ?? ''); 
                                    
                                    if (!empty($dbPath)): 
                                        // 2. Clean slashes for the server check (handles Windows vs Linux)
                                        $cleanPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dbPath);
                                        $fullServerPath = __DIR__ . DIRECTORY_SEPARATOR . $cleanPath;

                                        if (file_exists($fullServerPath)): 
                                ?>
                                            <div class="mb-3 mt-2">
                                                <a href="<?php echo htmlspecialchars($dbPath); ?>" target="_blank">
                                                    <img src="<?php echo htmlspecialchars($dbPath); ?>"
                                                        class="img-fluid rounded shadow-sm border"
                                                        style="max-width:150px; cursor: pointer;"
                                                        alt="Review Image">
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-danger small mb-2">
                                                <i class="bi bi-exclamation-triangle"></i> 
                                                File not found at: <?php echo htmlspecialchars($fullServerPath); ?>
                                            </div>
                                        <?php endif; ?>
                                <?php endif; ?>

                                <?php if (!empty($review['variant_value'])): ?>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-tag small"></i> Variation: <?php echo htmlspecialchars($review['variant_value']); ?>
                                    </small>
                                <?php endif; ?>

                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-chat-left-dots text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No reviews yet. Be the first to review this product!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cart Popup -->
        <div class="cart-popup" id="cartPopup">
            <div class="cart-popup-icon">✓</div>
            <span>Added to cart!</span>
            <a href="../figures/cart.php" id="cartB">View Cart</a>
        </div>

        <?php include 'footer.php'; ?>

        <script>
            window.PRODUCT_ID = <?php echo $productId; ?>;
        </script>
        <script src="js/prod.js?v=<?php echo time(); ?>"></script>
        <script src="js/buy.js?v=<?php echo time(); ?>"></script>

</body>
</html>
