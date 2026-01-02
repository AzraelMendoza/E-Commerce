<?php
session_start();
// Removed the mandatory redirect to allow guests to see the landing page
$is_logged_in = isset($_SESSION['user_id']); 

include 'db.php'; 

// FETCH NEW ARRIVALS
$query = "
    (SELECT p.*, 
        (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.product_id) as total_reviews,
        (SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.product_id) as avg_rating 
     FROM products p WHERE category_id = 801 ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT p.*, 
        (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.product_id) as total_reviews,
        (SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.product_id) as avg_rating 
     FROM products p WHERE category_id = 802 ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT p.*, 
        (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.product_id) as total_reviews,
        (SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.product_id) as avg_rating 
     FROM products p WHERE category_id = 803 ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT p.*, 
        (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.product_id) as total_reviews,
        (SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.product_id) as avg_rating 
     FROM products p WHERE category_id = 804 ORDER BY created_at DESC LIMIT 3)
    ORDER BY created_at DESC LIMIT 12";

$result = mysqli_query($conn, $query);

// FETCH REVIEWS
$rev_query = "
    SELECT r.comment, r.rating, p.name as p_name, u.username 
    FROM reviews r 
    JOIN products p ON r.product_id = p.product_id 
    JOIN users u ON r.user_id = u.user_id
    WHERE r.rating >= 4 
    ORDER BY r.created_at DESC 
    LIMIT 9";

$rev_result = mysqli_query($conn, $rev_query);
$reviews = [];
while ($row = mysqli_fetch_assoc($rev_result)) {
    $reviews[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to CubeClass</title>

    <link rel="icon" type="image/png" href="img/media/logo2.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/category.css">
    <link rel="stylesheet" href="css/banner.css">
    <link rel="stylesheet" href="figures/css/land.css">
</head>
<body>

<?php include 'header.php'; ?>

<div id="cubeSlider" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php for ($i = 1; $i <= 6; $i++): ?>
            <div class="carousel-item <?= ($i === 1) ? 'active' : '' ?>">
                <img src="img/slideshow/slide<?= $i ?>.jpg" alt="Slide <?= $i ?>">
                <div class="carousel-caption">
                    <h1>Welcome to CubeClass</h1>
                    <p>Board Games • Puzzles • RC Toys • Figures</p>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>

<div class="container my-5 text-center">
    <div class="row">
        <div class="col-md-4">
            <i class="bi bi-truck fs-1 text-danger"></i>
            <h5 class="fw-bold mt-2">Fast Shipping</h5>
            <p>Nationwide delivery in 2–5 days</p>
        </div>
        <div class="col-md-4">
            <i class="bi bi-shop fs-1 text-danger"></i>
            <h5 class="fw-bold mt-2">Visit Our Store</h5>
            <p>See and try our items in person</p>
        </div>
        <div class="col-md-4">
            <i class="bi bi-arrow-repeat fs-1 text-danger"></i>
            <h5 class="fw-bold mt-2">Easy Returns</h5>
            <p>Hassle-free 7-day return policy</p>
        </div>
    </div>
</div>

<div class="container my-5" id="newAr">
    <h2 class="mb-5 text-center fw-bold" style="font-size:45px;">NEW ARRIVALS</h2>
    <div class="row g-4">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $rating = round($row['avg_rating'] ?? 0);
                
                // TASK LOGIC: Determine link based on login status
                if ($is_logged_in) {
                    $target_url = "figures/" . htmlspecialchars($row['page_link']);
                } else {
                    $target_url = "login.php?msg=login_required";
                }
            ?>
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <a href="<?= $target_url ?>" class="text-decoration-none text-dark">
                    <div class="card product-card shadow-sm">
                        <div class="img-container">
                            <?php if ($row['stock_quantity'] <= 0): ?>
                                <span class="badge bg-dark sold-out-badge">Out of Stock</span>
                            <?php endif; ?>
                            <img src="/E-Commerce/figures/<?= $row['image_url'] ?>"
                                 class="img-fluid product-img"
                                 alt="<?= htmlspecialchars($row['name']) ?>"
                                 onerror="this.src='https://via.placeholder.com/150x150?text=No+Image';">
                        </div>
                        <div class="card-body">
                            <h4 class="product-title"><?= htmlspecialchars($row['name']) ?></h4>
                            <span class="brand-text"><?= htmlspecialchars($row['brand']) ?></span>
                            <div class="d-flex align-items-center mt-1 mb-2">
                                <div class="star-rating me-2">
                                    <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $rating) ? '★' : '☆'; ?>
                                </div>
                                <span class="text-muted small">(<?= $row['total_reviews'] ?>)</span>
                            </div>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="price-text">₱<?= number_format($row['price'], 0) ?></span>
                                <span class="view-details-text">View details →</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<section id="customer-reviews" class="py-5 border-top">
    <div class="container">
        <h2 class="text-center fw-bold mb-5">WHAT OUR CUSTOMERS SAY</h2>

        <div class="review-slider position-relative">
            <div class="review-track">
                <?php foreach ($reviews as $rev):
                    $masked = substr($rev['username'],0,1).'***'.substr($rev['username'],-1);
                ?>
                <div class="card custom-review-card" style="width:300px;">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="user-avatar-small me-3"><?= strtoupper($rev['username'][0]) ?></div>
                            <div>
                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($masked) ?></h6>
                                <div class="text-warning small">
                                    <?php for ($i=1;$i<=5;$i++) echo ($i <= $rev['rating']) ? '★' : '☆'; ?>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mb-4 flex-grow-1">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                        <div class="mt-auto pt-3 border-top">
                            <small class="text-danger fw-bold d-block"><?= htmlspecialchars($rev['p_name']) ?></small>
                            <small class="text-muted" style="font-size:10px;">Verified Buyer</small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button id="prevReview" class="review-control-btn position-absolute top-50 start-0 translate-middle-y">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button id="nextReview" class="review-control-btn position-absolute top-50 end-0 translate-middle-y">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/reviews.js"></script>
</body>
</html>