<?php
require 'db.php';
require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

date_default_timezone_set('Asia/Manila');

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['token'], $_POST['password'])) {
        die("Invalid request.");
    }

    $token = $_POST['token'];
    $newPassword = $_POST['password'];

    // Server-side password validation
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/', $newPassword)) {
        $errorMessage = "Password must have at least 8 characters, including uppercase, lowercase, number, and special character.";
    } else {
        // Fetch token and expiry from DB
        $stmt = $conn->prepare("SELECT user_id, reset_token_expire FROM users WHERE reset_token = ? LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $errorMessage = "Invalid or expired reset token.";
        } else {
            $row = $result->fetch_assoc();
            $user_id = $row['user_id'];
            $expire = $row['reset_token_expire'];

            if (strtotime($expire) < time()) {
                $errorMessage = "Reset token has expired.";
            } else {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expire = NULL WHERE user_id = ?");
                $update->bind_param("si", $passwordHash, $user_id);

                if ($update->execute()) {
                    $successMessage = "✅ Password successfully reset. You may now log in.";
                } else {
                    $errorMessage = "Failed to reset password. Please try again.";
                }
            }
        }
    }
}

$token = $_GET['token'] ?? '';
if (!isset($_GET['token']) && empty($successMessage)) {
    $errorMessage = "Invalid reset link.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: 'Segoe UI', sans-serif; }
.card { padding: 2rem; border-radius: 15px; width: 100%; max-width: 450px; background: #fff; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
.btn-red { background: #dc3545; color: white; font-weight: 600; }
.btn-red:hover { background: #b02a37; }
.form-control:focus { border-color: #dc3545; box-shadow: 0 0 0 .2rem rgba(220,53,69,.25); }
.alert { margin-top: 10px; }
</style>
</head>
<body>

<div class="card">
    <h3 class="text-center mb-4">Reset Password</h3>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success text-center"><?php echo $successMessage; ?></div>
        <p class="text-center mt-3"><a href="login.php" class="text-danger">Go to Login</a></p>
    <?php else: ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger text-center" id="errorMessage"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <form method="POST" id="resetForm">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" name="password" id="password" required placeholder="Enter your new password">
            </div>

            <button type="submit" class="btn btn-red w-100">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

<script>
document.getElementById('resetForm').ad
