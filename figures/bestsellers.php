<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php'; 

// ==========================
// FETCH 15 BEST SELLERS (ONLY items with sales)
// ==========================
$query = "
    (SELECT p.*, SUM(oi.quantity) as total_sold,
        (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.product_id) as total_reviews,
        (SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.product_id) as avg_rating 
    FROM products p 
    INNER JOIN order_items oi ON p.product_id = oi.product_id 
    WHERE p.category_id = 801 
    GROUP BY p.product_id HAVING total_sold > 0 ORDER BY total_sold DESC LIMIT 4)
    UNION ALL
    (SELECT p.*, SUM(oi.quantity) as total_sold,
        (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.product_id) as total_reviews,
        (SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.product_id) as avg_rating 
    FROM products p 
    INNER JOIN order_items oi ON p.product_id = oi.product_id 
    WHERE p.category_id = 802 
    GROUP BY p.product_id HAVING total_sold > 0 ORDER BY total_sold DESC LIMIT 4)
    UNION ALL
    (SELECT p.*, SUM(oi.quantity) as total_sold,
        (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.product_id) as total_reviews,
        (SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.product_id) as avg_rating 
    FROM products p 
    INNER JOIN order_items oi ON p.product_id = oi.product_id 
    WHERE p.category_id = 803 
    GROUP BY p.product_id HAVING total_sold > 0 ORDER BY total_sold DESC LIMIT 4)
    UNION ALL
    (SELECT p.*, SUM(oi.quantity) as total_sold,
        (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.product_id) as total_reviews,
        (SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.product_id) as avg_rating 
    FROM products p 
    INNER JOIN order_items oi ON p.product_id = oi.product_id 
    WHERE p.category_id = 804 
    GROUP BY p.product_id HAVING total_sold > 0 ORDER BY total_sold DESC LIMIT 3)
    LIMIT 15";

$result = mysqli_query($conn, $query);
$rank = 1; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Best Sellers | CubeClass</title>
     <link rel="icon" type="image/png" href="img/media/logo2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/category.css">
    <link rel="stylesheet" href="css/best.css"> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php include 'header.php' ?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-5">BEST SELLERS</h2>
        <div class="mx-auto mb-3" style="width: 80px; height: 4px; background: #8B0000; border-radius: 2px;"></div>
        <p class="text-muted">Our most popular items with the highest sales volume.</p>
    </div>
    
    <div class="row g-5">
        <?php if(mysqli_num_rows($result) > 0): 
            while($row = mysqli_fetch_assoc($result)):
                $rating = round($row['avg_rating'] ?? 0);
                $sold = $row['total_sold'];
        ?>
        <div class="col-lg-4 col-md-6 col-12">
            <a href="figures/<?php echo htmlspecialchars($row['page_link']); ?>" class="text-decoration-none text-dark">
                <div class="card product-card shadow-sm">
                    
                    <?php if($rank <= 3): ?>
                        <div class="rank-badge rank-<?php echo $rank; ?>">
                            <?php if($rank == 1) echo '<i class="bi bi-award-fill"></i>'; else echo $rank; ?>
                        </div>
                    <?php endif; ?>

                    <div class="img-container">
                        <img src="/E-Commerce/figures/<?php echo $row['image_url']; ?>" class="img-fluid product-img" alt="Product">
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h4 class="product-title mt-2"><?php echo htmlspecialchars($row['name']); ?></h4>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="star-rating me-2">
                                <?php for($i=1; $i<=5; $i++) echo ($i <= $rating) ? '★' : '☆'; ?>
                            </div>
                            <span class="text-muted small">(<?php echo $row['total_reviews']; ?>)</span>
                        </div>
                        
                        <div class="mb-3">
                            <div class="progress" style="height: 6px; background-color: #f0f0f0; border-radius: 10px;">
                                <div class="progress-bar bg-danger" 
                                     role="progressbar" 
                                     style="width: <?php echo min(($sold / 25) * 100, 100); ?>%"></div>
                            </div>
                        </div>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div>
                                <span class="price-text">₱<?php echo number_format($row['price'], 0); ?></span>
                                <br><small class="text-danger fw-bold" style="font-size: 11px;">
                                    <i class="bi bi-cart-check"></i> <?php echo $sold; ?> units sold
                                </small>
                            </div>
                            <span class="view-details-text">Grab yours →</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php $rank++; endwhile; else: ?>
            <div class="col-12 text-center py-5">
                <h3 class="text-muted">Stay tuned for our upcoming best sellers!</h3>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php' ?>
</body>
</html>