<?php
session_start();
require_once 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$searchTerm = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$query = "SELECT p.*, 
          COUNT(DISTINCT r.review_id) as total_reviews, 
          AVG(r.rating) as avg_rating,
          SUM(oi.quantity) as total_sold
          FROM products p
          LEFT JOIN reviews r ON p.product_id = r.product_id
          LEFT JOIN order_items oi ON p.product_id = oi.product_id
          WHERE p.name LIKE '%$searchTerm%' 
          OR p.brand LIKE '%$searchTerm%'
          OR p.description LIKE '%$searchTerm%'
          GROUP BY p.product_id 
          ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | CubeClass</title>
    <link rel="icon" type="image/png" href="img/media/logo2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> 
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/welcome.css">
    <link rel="stylesheet" href="css/search.css">
</head>
<body id="welcome-page">
    <?php include 'header.php' ?>

    <div class="search-header bg-light py-5 border-bottom">
        <div class="container text-center">
            <p class="text-muted mb-1 text-uppercase fw-bold" style="letter-spacing: 2px;">Search Discovery</p>
            <h2 class="fw-bold display-6">Results for: "<span class="text-danger"><?= htmlspecialchars($searchTerm) ?></span>"</h2>
            <div class="mx-auto mt-3" style="width: 60px; height: 3px; background: #8B0000;"></div>
        </div>
    </div>

    <div class="container mb-5 mt-5" id="product-grid">
        <div class="row g-4">
            <?php 
            if (mysqli_num_rows($result) > 0): 
                while ($row = mysqli_fetch_assoc($result)): 
                    $rating = round($row['avg_rating'] ?? 0);
            ?>
            
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <a href="<?php echo htmlspecialchars($row['page_link']); ?>" class="text-decoration-none text-dark">
                    <div class="card product-card shadow-sm">
                        <div class="img-container">
                            <?php if ($row['stock_quantity'] <= 0): ?>
                                <span class="badge bg-dark sold-out-badge">Out of Stock</span>
                            <?php endif; ?>
                            
                            <img src="/E-Commerce/figures/<?php echo $row['image_url']; ?>" 
                                 class="product-img" 
                                 alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                 onerror="this.src='https://via.placeholder.com/220x220?text=Product+Missing';">
                        </div>

                        <div class="card-body">
                            <h4 class="product-title"><?php echo htmlspecialchars($row['name']); ?></h4>
                            <span class="brand-text"><?php echo htmlspecialchars($row['brand']); ?></span>
                            
                            <div class="d-flex align-items-center mt-2 mb-3">
                                <div class="star-rating me-2">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= $rating) ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                                <span class="text-muted small">(<?php echo $row['total_reviews']; ?>)</span>
                            </div>

                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="price-text">₱<?php echo number_format($row['price'], 0); ?></span>
                                <span class="view-details-text">View details →</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <?php 
                endwhile; 
            else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search display-1 text-secondary opacity-25"></i>
                    <p class="mt-3 fs-4 text-muted">We couldn't find any matches for "<?= htmlspecialchars($searchTerm) ?>"</p>
                    <a href="welcome.php" class="btn btn-dark rounded-pill px-4 mt-2">Back to Shop</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php';?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>