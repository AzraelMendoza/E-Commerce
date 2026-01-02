<?php
session_start();
require_once 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ensure the category_id matches your RC Toys category in the database
$query = "SELECT p.*, 
          COUNT(DISTINCT r.review_id) as total_reviews, 
          AVG(r.rating) as avg_rating,
          SUM(oi.quantity) as total_sold
          FROM products p
          LEFT JOIN reviews r ON p.product_id = r.product_id
          LEFT JOIN order_items oi ON p.product_id = oi.product_id
          WHERE p.category_id = 803 
          GROUP BY p.product_id 
          ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RC TOYS | Collection</title>
    <link rel="icon" type="image/png" href="img/media/logo2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> 
    <link rel="stylesheet" href="css/header.css">
    <style>
        :root {
            --accent-color: #8B0000;
            --text-dark: #000000;
            --border-light: #e0e0e0;
            --star-yellow: #ffc107;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            perspective: 1500px;
        }

        .product-card {
            border-radius: 20px !important; 
            border: 1px solid var(--border-light) !important;
            overflow: hidden;
            background: #fff;
            position: relative;
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s ease;
            transform-style: preserve-3d;
        }

        .product-card:hover {
            transform: translateY(-8px); 
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
            z-index: 10;
        }

        .img-container {
            background: #fff;
            padding: 30px 20px 10px 20px;
            overflow: hidden;
            position: relative;
        }

        .product-img {
            transition: transform 0.5s ease;
            width: 100%;
            display: block;
            transform: translateZ(0);
        }

        .product-card:hover .product-img {
            transform: scale(1.1); 
        }

        .sold-out-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 10;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 700;
        }

        .card-body {
            padding: 1.5rem !important;
            background: #fff;
            z-index: 5;
        }

        .price-text {
            color: var(--text-dark);
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .star-rating {
            font-size: 0.9rem;
            color: var(--star-yellow);
        }

        .brand-text {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 4px;
            display: block;
        }

        .main-content-area {
            margin-top: 60px; 
            margin-bottom: 100px;
        }
        
        .view-details-text {
            font-size: 0.95rem;
            color: #333;
            font-weight: 500;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .product-card:hover .view-details-text {
            color: var(--accent-color); 
            transform: translateX(5px);
        }
    </style>
</head>
<body>
    <?php include 'header.php' ?>

    <div class="container my-5">
        <h3 class="text-center fw-bold mb-4">Unleash the Power of Control</h3>
        <div class="row text-center">
            <div class="col-md-4">
                <div class="p-3">
                    <i class="bi bi-speedometer2 fs-1 text-primary"></i>
                    <h5 class="mt-3 fw-bold">High-Speed Racing</h5>
                    <p class="text-muted">Drift cars and off-road buggies built for speed.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <i class="bi bi-truck fs-1 text-warning"></i>
                    <h5 class="mt-3 fw-bold">Construction Fleet</h5>
                    <p class="text-muted">Heavy-duty RC bulldozers, excavators, and loaders.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <i class="bi bi-shield-shaded fs-1 text-dark"></i>
                    <h5 class="mt-3 fw-bold">Combat Tanks</h5>
                    <p class="text-muted">Realistic scale military tanks with full turret control.</p>
                </div>
            </div>
        </div>
    </div> 

    <div class="container main-content-area" id="product-grid">
        <div class="row g-4">
            <?php 
            if (mysqli_num_rows($result) > 0): 
                while ($row = mysqli_fetch_assoc($result)): 
                    $rating = round($row['avg_rating']);
            ?>
            
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                <a href="<?php echo htmlspecialchars($row['page_link']); ?>" class="text-decoration-none text-dark">
                    <div class="card h-100 product-card shadow-sm">
                        <div class="img-container text-center">
                            <?php if ($row['stock_quantity'] <= 0): ?>
                                <span class="badge bg-dark sold-out-badge">Out of Stock</span>
                            <?php endif; ?>
                            
                            <img src="/E-Commerce/figures/<?php echo $row['image_url']; ?>" 
                                class="img-fluid product-img" 
                                alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                style="height: 220px; object-fit: contain;"
                                onerror="this.src='https://via.placeholder.com/220x220?text=RC+Toy+Missing';">
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h4 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($row['name']); ?></h4>
                            <span class="brand-text"><?php echo htmlspecialchars($row['brand']); ?></span>
                            
                            <div class="d-flex align-items-center mt-2 mb-3">
                                <div class="star-rating me-2">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= $rating) ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                                <span class="text-muted small">(<?php echo $row['total_reviews']; ?> reviews)</span>
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
                    <i class="bi bi-search display-1 text-muted"></i>
                    <p class="mt-3 fs-4 text-muted">No RC toys found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php';?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>