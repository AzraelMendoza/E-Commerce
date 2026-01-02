<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$user_id = (int)$_SESSION['user_id'];
$shipping_fee = 100;
$errors = [];
$cart_items = [];
$total = 0;

/* ===============================
    BUY NOW OR CART CHECKOUT
================================ */
$buy_now = isset($_GET['buy_now'], $_GET['product_id'], $_GET['quantity']);

if ($buy_now) {
    $product_id = (int)$_GET['product_id'];
    $variant_id = isset($_GET['variant_id']) ? (int)$_GET['variant_id'] : null;
    $quantity   = max(1, (int)$_GET['quantity']);

    $stmt = $conn->prepare("
        SELECT 
            p.product_id, p.name AS product_name, p.price AS base_price,
            p.image_url AS product_image, p.stock_quantity AS product_stock,
            v.variant_id, v.variant_value, v.price_adjustment,
            v.stock_quantity AS variant_stock, v.img_url AS variant_image
        FROM products p
        LEFT JOIN product_variants v ON v.variant_id = ? AND v.product_id = p.product_id
        WHERE p.product_id = ? LIMIT 1
    ");
    $stmt->bind_param("ii", $variant_id, $product_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item) {
        $errors[] = "Product not found.";
    } elseif ($variant_id && $item['variant_stock'] < $quantity) {
        $errors[] = "Not enough variant stock.";
    } elseif ($item['product_stock'] < $quantity) {
        $errors[] = "Not enough product stock.";
    } else {
        $price_adj = $item['price_adjustment'] ?? 0;
        $item['final_price'] = $item['base_price'] + $price_adj;
        $item['quantity'] = $quantity;
        $item['subtotal'] = $item['final_price'] * $quantity;
        $item['image_url'] = $item['variant_image'] ?? $item['product_image'];
        $total = $item['subtotal'];
        $cart_items[] = $item;
    }
} else {
    $cart_ids = $_POST['cart_id'] ?? [];
    if ($cart_ids) {
        $placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
        $stmt = $conn->prepare("
            SELECT c.cart_id, c.product_id, c.variant_id, c.quantity,
                   p.name AS product_name, p.price AS base_price, p.image_url AS product_image, p.stock_quantity AS product_stock,
                   v.variant_value, v.price_adjustment, v.stock_quantity AS variant_stock, v.img_url AS variant_image
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            LEFT JOIN product_variants v ON c.variant_id = v.variant_id
            WHERE c.cart_id IN ($placeholders) AND c.user_id = ?
        ");
        $types = str_repeat('i', count($cart_ids)) . 'i';
        $params = array_merge($cart_ids, [$user_id]);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $price_adj = $row['price_adjustment'] ?? 0;
            $row['final_price'] = $row['base_price'] + $price_adj;
            $row['subtotal'] = $row['final_price'] * $row['quantity'];
            $row['image_url'] = $row['variant_image'] ?? $row['product_image'];
            $total += $row['subtotal'];
            $cart_items[] = $row;
        }
    }
}

/* ===============================
    LOAD DEFAULT ADDRESS
================================ */
$stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$default_address = $stmt->get_result()->fetch_assoc() ?? [];
/* ===============================
    PLACE ORDER + STOCK UPDATE
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order']) && !empty($cart_items)) {

    // BACKEND VALIDATION: Trim and Check
    $fname     = trim($_POST['first_name'] ?? '');
    $mname     = trim($_POST['middle_name'] ?? ''); // Now treated as required
    $lname     = trim($_POST['last_name'] ?? '');
    $contact   = trim($_POST['contact'] ?? '');
    $province  = trim($_POST['province'] ?? '');
    $city      = trim($_POST['city'] ?? '');
    $barangay  = trim($_POST['barangay'] ?? '');
    $street    = trim($_POST['street'] ?? '');
    $house_num = trim($_POST['house_num'] ?? '');
    $zip       = trim($_POST['zip'] ?? '');

    // Added $mname to the empty check
    if (empty($fname) || empty($mname) || empty($lname) || empty($contact) || empty($province) || empty($city) || empty($barangay) || empty($street) || empty($house_num) || empty($zip)) {
        $errors[] = "All name fields (including Middle Name) and shipping details are required.";
    }

    if (!preg_match('/^09\d{9}$/', $contact)) {
        $errors[] = "Invalid contact number format.";
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            if (isset($_POST['use_default'])) {
                $stmt = $conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();

                $stmt = $conn->prepare("
                    INSERT INTO user_addresses (user_id, receiver_firstname, receiver_middlename, receiver_lastname, contact_num, province, city, village, street, house_num, zip_code, is_default)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,1)
                ");
                $stmt->bind_param("issssssssss", $user_id, $fname, $mname, $lname, $contact, $province, $city, $barangay, $street, $house_num, $zip);
                $stmt->execute();
            }

            $total_amount = $total + $shipping_fee;
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, 'pending', NOW())");
            $stmt->bind_param("id", $user_id, $total_amount);
            $stmt->execute();
            $order_id = $conn->insert_id;

            $insertItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, variant_id, quantity, price) VALUES (?,?,?,?,?)");
            $updateProductStock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?");
            $updateVariantStock = $conn->prepare("UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE variant_id = ? AND stock_quantity >= ?");

            foreach ($cart_items as $item) {
                $v_id = $item['variant_id'] ?? null;
                $insertItem->bind_param("iiidi", $order_id, $item['product_id'], $v_id, $item['quantity'], $item['final_price']);
                $insertItem->execute();
                $updateProductStock->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
                $updateProductStock->execute();
                if ($v_id) {
                    $updateVariantStock->bind_param("iii", $item['quantity'], $v_id, $item['quantity']);
                    $updateVariantStock->execute();
                }
            }

            if (!$buy_now && !empty($_POST['cart_id'])) {
                $ph = implode(',', array_fill(0, count($_POST['cart_id']), '?'));
                $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id IN ($ph) AND user_id = ?");
                $types = str_repeat('i', count($_POST['cart_id'])) . 'i';
                $params = array_merge($_POST['cart_id'], [$user_id]);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
            }

            $conn->commit();
            header("Location: order_success.php?order_id=$order_id");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/header.css">
    <style>
        body { background-color: #f8f9fa; }
        .order-summary { background-color: #fff; border-radius: .5rem; padding: 1.5rem; }
        .order-item img { width: 70px; height: 70px; object-fit: cover; border-radius: .5rem; }
        .sticky-summary { position: sticky; top: 20px; }
        .is-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important; }
    </style>
</head>
<body data-province="<?= htmlspecialchars($default_address['province'] ?? '') ?>" data-city="<?= htmlspecialchars($default_address['city'] ?? '') ?>" data-barangay="<?= htmlspecialchars($default_address['village'] ?? '') ?>">

<?php include 'header.php'; ?>

<div class="container py-5">
    <h2 class="fw-bold mb-4">Checkout</h2>
    <div class="row">
        <div class="col-md-7 mb-4">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card p-4 mb-4">
                <h5 class="mb-3">Shipping Information</h5>
                <form method="POST" id="checkoutForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($_POST['first_name'] ?? $default_address['receiver_firstname'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($_POST['middle_name'] ?? $default_address['receiver_middlename'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($_POST['last_name'] ?? $default_address['receiver_lastname'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label>Contact Number</label>
                        <input type="text" name="contact" id="contact" class="form-control" value="<?= htmlspecialchars($_POST['contact'] ?? $default_address['contact_num'] ?? '') ?>" required placeholder="09xxxxxxxxx">
                        <div id="contactError" class="text-danger mt-1" style="display:none;">Invalid format (09XXXXXXXXX)</div>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <label>Province</label>
                            <select name="province" id="province" class="form-select" required></select>
                        </div>
                        <div class="col-md-4">
                            <label>City / Municipality</label>
                            <select name="city" id="city" class="form-select" required></select>
                        </div>
                        <div class="col-md-4">
                            <label>Barangay</label>
                            <select name="barangay" id="barangay" class="form-select" required></select>
                        </div>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col-md-8">
                            <label>Street</label>
                            <input type="text" name="street" class="form-control" value="<?= htmlspecialchars($_POST['street'] ?? $default_address['street'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label>House Number</label>
                            <input type="text" name="house_num" class="form-control" value="<?= htmlspecialchars($_POST['house_num'] ?? $default_address['house_num'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label>Zip Code</label>
                        <input type="text" name="zip" class="form-control" value="<?= htmlspecialchars($_POST['zip'] ?? $default_address['zip_code'] ?? '') ?>" required>
                    </div>
                    <div class="form-check mt-3 mb-3">
                        <input class="form-check-input" type="checkbox" name="use_default" id="use_default">
                        <label class="form-check-label" for="use_default">Set as default address</label>
                    </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="order-summary sticky-summary p-3 shadow-sm">
                <h5 class="mb-3">Order Summary</h5>
                <?php foreach ($cart_items as $item): ?>
                    <div class="d-flex align-items-center mb-3 order-item">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="Product">
                        <div class="ms-3 flex-grow-1">
                            <div><?= htmlspecialchars($item['product_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($item['variant_value'] ?? '') ?></small>
                            <div>Qty: <?= (int)$item['quantity'] ?></div>
                        </div>
                        <div class="fw-bold">₱<?= number_format($item['subtotal'], 2) ?></div>
                        <?php if (isset($item['cart_id'])): ?>
                            <input type="hidden" name="cart_id[]" value="<?= (int)$item['cart_id'] ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span>₱<?= number_format($total + $shipping_fee, 2) ?></span></div>
                <button type="submit" name="place_order" value="1" class="btn btn-danger w-100 mt-3">Place Order</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="js/checkout.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>