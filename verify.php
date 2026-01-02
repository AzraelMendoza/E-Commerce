<?php
require 'db.php';

$success = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("
        UPDATE users
        SET is_email_verified = 1, verification_token = NULL
        WHERE verification_token = ?
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="icon" type="image/png" href="img/media/logo2.png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f5f5;
        }
        .card {
            border-radius: 14px;
        }
        .btn-red {
            background: #dc3545;
            color: #fff;
        }
        .btn-red:hover {
            background: #b02a37;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4 text-center" style="max-width: 420px; width: 100%;">

        <?php if ($success): ?>
            <h4 class="text-success mb-3">🎉 Email Verified!</h4>
            <p class="mb-4">
                Your email address has been successfully verified.<br>
                You can now log in to your account.
            </p>
            <a href="login.php" class="btn btn-red w-100">
                Go to Login
            </a>
        <?php else: ?>
            <h4 class="text-danger mb-3">⚠ Verification Failed</h4>
            <p class="mb-4">
                This verification link is invalid or has already been used.
            </p>
            <a href="register.php" class="btn btn-secondary w-100">
                Create an Account
            </a>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
