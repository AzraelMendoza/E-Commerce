<?php
require 'db.php';
require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

date_default_timezone_set('Asia/Manila'); // Set your timezone

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $errorMessage = "Email not found.";
    } else {
        // Generate token and expiry (3 minutes)
        $token = bin2hex(random_bytes(32));
        $expire = date("Y-m-d H:i:s", strtotime("+3 minutes"));

        // Update token in DB
        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expire = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expire, $email);
        $update->execute();

        // Send email
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $_ENV['EMAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['EMAIL_USER'];
            $mail->Password = $_ENV['EMAIL_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['EMAIL_PORT'];

            $mail->setFrom($_ENV['EMAIL_USER'], $_ENV['EMAIL_FROM_NAME']);
            $mail->addAddress($email);

            $mail->Subject = "Reset Password";
            $mail->isHTML(true);
            $mail->Body = "
                Hi,<br><br>
                Click the link below to reset your password (valid for 3 minutes):<br>
                <a href='http://localhost/E-Commerce/reset.php?token=$token'>Reset Password</a><br><br>
                If you didn't request this, ignore this email.
            ";

            $mail->send();
            $successMessage = "✅ Reset link sent to your email.";
        } catch (Exception $e) {
            $errorMessage = "Failed to send email. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
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
    <h3 class="text-center mb-4">Forgot Password</h3>

    <?php if(!empty($successMessage)): ?>
        <div class="alert alert-success text-center"><?php echo $successMessage; ?></div>
        <p class="text-center mt-3"><a href="login.php" class="text-danger">Go to Login</a></p>
    <?php else: ?>
        <?php if(!empty($errorMessage)): ?>
            <div class="alert alert-danger text-center"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" id="email" required placeholder="Enter your email">
            </div>
            <button type="submit" class="btn btn-red w-100">Send Reset Link</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
