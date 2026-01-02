<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: order_success.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$order_id = (int)$_GET['order_id'];

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reviews'])) {
    foreach ($_POST['reviews'] as $unique_key => $data) {
        $parts = explode('_', $unique_key);
        $product_id = (int)$parts[0];
        $variant_id = (int)$parts[1];

        // Skip if review already exists
        $checkReview = $conn->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND product_id = ? AND order_id = ? AND variant_id = ?");
        $checkReview->bind_param("iiii", $user_id, $product_id, $order_id, $variant_id);
        $checkReview->execute();
        if ($checkReview->get_result()->num_rows > 0) continue;

        $rating = isset($data['rating']) ? (int)$data['rating'] : 0;
        $comment = isset($data['comment']) ? $conn->real_escape_string($data['comment']) : '';
        $image_path = null;

        if (isset($_FILES['reviews']['name'][$unique_key]['image']) && $_FILES['reviews']['error'][$unique_key]['image'] === 0) {
            $target_dir = "img/reviews/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $ext = pathinfo($_FILES['reviews']['name'][$unique_key]['image'], PATHINFO_EXTENSION);
            $filename = "rev_" . time() . "_" . $user_id . "_" . $product_id . "_" . $variant_id . "." . $ext;
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($_FILES['reviews']['tmp_name'][$unique_key]['image'], $target_file)) {
                $image_path = $target_file;
            }
        }

        $stmt = $conn->prepare("INSERT INTO reviews (product_id, variant_id, user_id, order_id, rating, comment, review_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiisss", $product_id, $variant_id, $user_id, $order_id, $rating, $comment, $image_path);
        $stmt->execute();
    }
    header("Location: order_success.php?review_submitted=1");
    exit;
}

// FETCH ITEMS + VARIANTS + EXISTING REVIEWS
$itemStmt = $conn->prepare("
    SELECT oi.product_id, oi.variant_id, oi.quantity, p.name, p.image_url, 
           v.variant_value,
           r.rating AS existing_rating, r.comment AS existing_comment, r.review_image AS existing_image
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.product_id 
    LEFT JOIN product_variants v ON oi.variant_id = v.variant_id
    LEFT JOIN reviews r ON (r.product_id = oi.product_id AND r.order_id = oi.order_id AND r.user_id = ? AND r.variant_id = oi.variant_id)
    WHERE oi.order_id = ?
");
$itemStmt->bind_param("ii", $user_id, $order_id);
$itemStmt->execute();
$items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$all_reviewed = true;
foreach($items as $item) {
    if (is_null($item['existing_rating'])) {
        $all_reviewed = false;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Review Your Purchase</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* ===========================
   Variables
=========================== */
:root {
    --color-primary: #1a1a1a;
    --color-accent: #dc3545;
    --color-bg: #f8f8f8;
    --color-card-bg: #ffffff;
    --color-muted: #6c757d;
    --color-light-gray: #e0e0e0;
    --border-radius: 0.75rem;
}

/* ===========================
   Body & Typography
=========================== */
body {
    font-family: 'Inter', sans-serif;
    background-color: var(--color-bg);
    color: var(--color-primary);
    margin: 0;
    padding: 0;
}

a {
    color: var(--color-accent);
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}

/* ===========================
   Page Header
=========================== */
.page-header {
    margin-bottom: 2rem;
}
.page-header h1 {
    font-weight: 700;
    font-size: 1.8rem;
    margin-top: 0.5rem;
}

/* ===========================
   Review Card
=========================== */
.review-card {
    background-color: var(--color-card-bg);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    position: relative;
    transition: transform 0.2s;
}
.review-card:hover {
    transform: translateY(-3px);
}

/* ===========================
   Reviewed Badge
=========================== */
.reviewed-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background-color: var(--color-accent);
    color: #fff;
    font-size: 0.75rem;
    padding: 0.35rem 0.7rem;
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* ===========================
   Product Info
=========================== */
.product-img-wrapper img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    border-radius: 0.25rem;
    border: 1px solid var(--color-light-gray);
    padding: 4px;
}
.qty-badge {
    background-color: #f0f0f0;
    color: var(--color-primary);
    padding: 0.25rem 0.6rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}

/* ===========================
   Rating Stars
=========================== */
.rating-group {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 0.3rem;
}
.rating-group input {
    display: none;
}
.rating-group label {
    cursor: pointer;
    color: #ccc;
    font-size: 1.3rem;
    transition: color 0.2s;
}
.rating-group input:checked ~ label,
.rating-group label:hover,
.rating-group label:hover ~ label {
    color: var(--color-accent);
}
.rating-group.readonly label {
    cursor: default;
}

/* ===========================
   Form Elements
=========================== */
.form-label-custom {
    font-weight: 600;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}
textarea.form-control {
    resize: none;
    box-shadow: none;
}

/* ===========================
   Review Image
=========================== */
.review-img-preview {
    width: 100%;
    max-width: 120px;
    margin-top: 0.5rem;
    border-radius: 0.5rem;
    border: 1px solid var(--color-light-gray);
}

/* ===========================
   Buttons
=========================== */
.btn-submit {
    background-color: var(--color-primary);
    color: #fff;
    border-radius: var(--border-radius);
    padding: 0.55rem 1rem;
    width: 100%;
    font-weight: 600;
    transition: background-color 0.2s;
}
.btn-submit:hover {
    background-color: var(--color-accent);
    color: #fff;
}

/* ===========================
   Responsive
=========================== */
@media (max-width: 576px) {
    .review-card {
        padding: 1rem;
    }
    .product-img-wrapper img {
        width: 60px;
        height: 60px;
    }
}
</style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="page-header">
                <a href="order_success.php" class="fw-bold"><i class="bi bi-arrow-left"></i> Back</a>
                <h1 class="mt-2 mb-0"><?= $all_reviewed ? 'Your Reviews' : 'Rate Your Items' ?></h1>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <?php foreach ($items as $item): 
                    $unique_key = $item['product_id'] . '_' . ($item['variant_id'] ?? 0);
                    $is_reviewed = !is_null($item['existing_rating']);
                    $variant_display = $item['variant_value'] ?? '';
                ?>
                <div class="review-card">
                    <?php if($is_reviewed): ?>
                        <span class="reviewed-badge"><i class="bi bi-check-circle-fill"></i> Reviewed</span>
                    <?php endif; ?>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="product-img-wrapper"><img src="<?= htmlspecialchars($item['image_url']) ?>" alt="Product"></div>
                        <div class="flex-grow-1">
                            <h6 class="fw-semibold mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                            <?php if($variant_display): ?>
                                <small class="text-muted text-uppercase" style="font-size: 0.75rem;">Variation: <?= htmlspecialchars($variant_display) ?></small>
                            <?php endif; ?>
                            <div class="mt-1"><span class="qty-badge">Qty: <?= $item['quantity'] ?></span></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Rating</label>
                        <div class="rating-group <?= $is_reviewed ? 'readonly' : '' ?>">
                            <?php for($i=5; $i>=1; $i--): 
                                $checked = ($is_reviewed && (int)$item['existing_rating'] === $i) ? 'checked' : '';
                                $disabled = $is_reviewed ? 'disabled' : '';
                            ?>
                            <input type="radio" id="star_<?= $unique_key ?>_<?= $i ?>" 
                                   name="reviews[<?= $unique_key ?>][rating]" 
                                   value="<?= $i ?>" <?= $checked ?> <?= $disabled ?> required>
                            <label for="star_<?= $unique_key ?>_<?= $i ?>"><i class="bi bi-star-fill"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Comment</label>
                        <?php if($is_reviewed): ?>
                            <p class="mb-0 text-muted small"><?= nl2br(htmlspecialchars($item['existing_comment'])) ?></p>
                        <?php else: ?>
                            <textarea name="reviews[<?= $unique_key ?>][comment]" class="form-control shadow-none" rows="3" placeholder="Share your thoughts..."></textarea>
                        <?php endif; ?>
                    </div>

                    <?php if($is_reviewed && $item['existing_image']): ?>
                        <div>
                            <label class="form-label-custom d-block">Your Photo</label>
                            <img src="<?= htmlspecialchars($item['existing_image']) ?>" class="review-img-preview" alt="Review Image">
                        </div>
                    <?php elseif(!$is_reviewed): ?>
                        <div>
                            <label class="form-label-custom">Add Photo</label>
                            <input type="file" name="reviews[<?= $unique_key ?>][image]" class="form-control form-control-sm shadow-none" accept="image/*">
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <?php if(!$all_reviewed): ?>
                    <button type="submit" name="submit_reviews" class="btn-submit mt-3">Complete Submission</button>
                <?php else: ?>
                    <a href="order_success.php" class="btn-submit text-decoration-none d-block mt-3">Back to My Orders</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

</body>
</html>
