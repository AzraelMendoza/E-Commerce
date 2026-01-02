<?php
session_start();
require_once 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$query = "SELECT p.*, 
          COUNT(DISTINCT r.review_id) as total_reviews, 
          AVG(r.rating) as avg_rating,
          SUM(oi.quantity) as total_sold
          FROM products p
          LEFT JOIN reviews r ON p.product_id = r.product_id
          LEFT JOIN order_items oi ON p.product_id = oi.product_id
          WHERE p.category_id = 804 
          GROUP BY p.product_id 
          ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOARD GAMES | Collection</title>
    <link rel="icon" type="image/png" href="img/media/logo2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> 
    <link rel="stylesheet" href="css/header.css">
    <style>
        
        .product-card {
            border-radius: 20px !important;
            border: 1px solid #e5e5e5 !important;
            background-color: #ffffff !important;
            overflow: hidden;
            height: 480px;
            position: relative;
            transition:
                transform 0.35s cubic-bezier(.34,1.56,.64,1),
                box-shadow 0.35s ease,
                border-color 0.35s ease;
        }

        /* ✅ SAME SHADOW FEEL AS figures.php */
        .product-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 26px 45px rgba(0,0,0,0.18) !important;
            border-color: #dcdcdc;
            z-index: 10;
        }


            /* =========================
            IMAGE CONTAINER
            ========================== */
            .img-container {
                height: 300px;
                background-color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 12px;
                overflow: hidden;
            }

            .product-img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
                display: block;
                transition: transform 0.45s cubic-bezier(.34,1.56,.64,1);
            }

            .product-card:hover .product-img {
                transform: scale(1.1);
            }

            /* =========================
            CARD BODY
            ========================== */
            .card-body {
                padding: 1rem !important;
                display: flex;
                flex-direction: column;
                background-color: #ffffff;
            }

            .product-title {
                font-size: 1.05rem;
                font-weight: 700;
                margin-bottom: 2px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                transition: transform 0.25s ease;
            }

            .product-card:hover .product-title {
                transform: translateY(-1px);
            }

            .brand-text {
                font-size: 0.85rem;
                color: #6c757d;
                margin-bottom: 4px;
            }

            .star-rating {
                font-size: 0.8rem;
                color: #ffc107;
                transition: transform 0.25s ease;
            }

            .product-card:hover .star-rating {
                transform: scale(1.08);
            }

            .price-text {
                font-size: 1.2rem;
                font-weight: 800;
                transition: transform 0.25s ease;
            }

            .product-card:hover .price-text {
                transform: translateY(-2px);
            }

            /* =========================
            VIEW DETAILS (DARK RED)
            ========================== */
            .view-details-text {
                font-size: 0.85rem;
                font-weight: 500;
                color: #333;
                transition:
                    transform 0.3s ease,
                    color 0.3s ease,
                    letter-spacing 0.3s ease;
            }

            .product-card:hover .view-details-text {
                transform: translateX(10px);
                letter-spacing: 0.4px;
                color: #8B0000; /* ✅ DARK RED */
            }
    </style>
</head>
<body>
    <?php include 'header.php' ?>

    <div class="container mt-5">
        <div class="row g-3 text-center">
            <div class="col-12 mb-2">
                <h2 class="fw-bold">Find Your Perfect Play</h2>
                <p class="text-muted">Explore our curated collection of tabletop favorites</p>
            </div>
            <div class="col-md-4">
                <div class="card p-3 game-guide-card shadow-sm h-100">
                    <h5 class="fw-bold" style="color: var(--accent-color);"><i class="bi bi-trophy me-2"></i>Strategy Classics</h5>
                    <p class="small text-muted mb-0">Master the board with <strong>Chess</strong>, <strong>Rummikub</strong>, and <strong>Scrabble</strong>.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 game-guide-card shadow-sm h-100">
                    <h5 class="fw-bold" style="color: var(--accent-color);"><i class="bi bi-people-fill me-2"></i>Family Favorites</h5>
                    <p class="small text-muted mb-0">Build fortunes and memories with the iconic <strong>Monopoly</strong> editions.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 game-guide-card shadow-sm h-100">
                    <h5 class="fw-bold" style="color: var(--accent-color);"><i class="bi bi-incognito me-2"></i>Kids & Co-op</h5>
                    <p class="small text-muted mb-0">Fun for all ages with <strong>Animal Upon Animal</strong> and <strong>Outfoxed</strong>.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container main-content-area mt-5" id="product-grid">
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
                                onerror="this.src='https://via.placeholder.com/220x220?text=Figure+Missing';">
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
                    <p class="mt-3 fs-4 text-muted">No products found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php';?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>