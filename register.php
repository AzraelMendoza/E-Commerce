<?php
require 'db.php';
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first          = trim($_POST['first_name']);
    $last           = trim($_POST['last_name']);
    $username       = trim($_POST['user_name']);
    $email          = trim($_POST['email']);
    $phone          = trim($_POST['phone_num']);
    $rawPass        = $_POST['password'];
    $confirmPass    = $_POST['confirm_password'];

    /* =======================
        VALIDATIONS (Non-DB checks first)
    ======================= */

    // First & Last name (letters only, min 2)
    if (!preg_match("/^[a-zA-Z]{2,}$/", $first)) {
        $errorMessage = "First name must contain letters only (min 2 characters).";
    }
    elseif (!preg_match("/^[a-zA-Z]{2,}$/", $last)) {
        $errorMessage = "Last name must contain letters only (min 2 characters).";
    }

    // Username format
    elseif (!preg_match("/^[a-zA-Z0-9_]{4,20}$/", $username)) {
        $errorMessage = "Username must be 4–20 characters and contain only letters, numbers, or underscore.";
    }

    // Email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email address format.";
    }

    // Phone number (09xxxxxxxxx)
    elseif (!preg_match("/^09\d{9}$/", $phone)) {
        $errorMessage = "Phone number must start with 09 and be exactly 11 digits.";
    }

    // Passwords must match
    elseif ($rawPass !== $confirmPass) {
        $errorMessage = "Passwords do not match.";
    }

    // Password strength
    elseif (
        strlen($rawPass) < 8 ||
        !preg_match("/[A-Z]/", $rawPass) ||
        !preg_match("/[a-z]/", $rawPass) ||
        !preg_match("/[0-9]/", $rawPass) ||
        !preg_match("/[\W]/", $rawPass)
    ) {
        $errorMessage = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    }

    /* =======================
        DUPLICATE CHECK (Database checks)
    ======================= */
    else {
        // Check for duplicate email
        $checkEmailStmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $emailResult = $checkEmailStmt->get_result();
        
        if ($emailResult->num_rows > 0) {
            $errorMessage = "Email address is already registered.";
        }
        $checkEmailStmt->close();
        
        // Check for duplicate username
        if (empty($errorMessage)) { // Only check username if email is unique
            $checkUsernameStmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
            $checkUsernameStmt->bind_param("s", $username);
            $checkUsernameStmt->execute();
            $usernameResult = $checkUsernameStmt->get_result();

            if ($usernameResult->num_rows > 0) {
                $errorMessage = "Username is already taken.";
            }
            $checkUsernameStmt->close();
        }
    }


    /* =======================
        IF ALL VALID → INSERT INTO DB
    ======================= */
    if (empty($errorMessage)) {

        // Hash password and generate token
        $password = password_hash($rawPass, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        $stmt = $conn->prepare("
            INSERT INTO users (
                first_name, last_name, username, email, phone_num,
                password_hash, is_email_verified, verification_token
            ) VALUES (?, ?, ?, ?, ?, ?, 0, ?)
        ");

        $stmt->bind_param(
            "sssssss",
            $first,
            $last,
            $username,
            $email,
            $phone,
            $password,
            $token
        );

        // Execute the insert
        if ($stmt->execute()) {

            $mail = new PHPMailer(true);
            try {
                // Email configuration (using environment variables)
                $mail->isSMTP();
                $mail->Host       = $_ENV['EMAIL_HOST'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['EMAIL_USER'];
                $mail->Password   = $_ENV['EMAIL_PASS'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $_ENV['EMAIL_PORT'];

                $mail->setFrom($_ENV['EMAIL_USER'], $_ENV['EMAIL_FROM_NAME']);
                $mail->addAddress($email, "$first $last");

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email Address';
                $mail->Body = "
                    Hi $first,<br><br>
                    Please verify your email by clicking the link below:<br>
                    <a href='http://localhost/E-commerce/verify.php?token=$token'>Verify Email</a>
                    <br><br>Thank you!
                ";

                $mail->send();
                $successMessage = "Registration successful! Please check your email inbox to verify your account.";

            } catch (Exception $e) {
                $errorMessage = "Email could not be sent.";
            }

        } else {
            // Fallback error, though the specific checks above should catch most issues.
            $errorMessage = "An error occurred during registration. Please try again.";
        }
    }
}
?>

<?php
    // These lines must remain outside the IF(POST) block to correctly populate fields after a failed submission
    $oldFirst     = $_POST['first_name'] ?? '';
    $oldLast      = $_POST['last_name'] ?? '';
    $oldUsername  = $_POST['user_name'] ?? '';
    $oldEmail     = $_POST['email'] ?? '';
    $oldPhone     = $_POST['phone_num'] ?? '';

    $oldPass   = $_POST['password'] ?? '';
    $oldConPass = $_POST['confirm_password'] ?? '';
?>

 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>

    <link rel="icon" type="image/png" href="img/media/logo2.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="css/reg.css">
</head>
<body>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card auth-card shadow-lg p-4" style="max-width: 480px; width: 100%;">
        <div class="text-center mb-4">
            <h3 class="auth-header">Create Account</h3>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success text-center"><?= $successMessage ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger text-center"><?= $errorMessage ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="registrationForm" novalidate>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-semibold mb-1" for="first_name_input">First Name</label>
                    <input type="text" class="form-control" name="first_name" id="first_name_input"
                            value="<?= htmlspecialchars($oldFirst ?? '') ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="fw-semibold mb-1" for="last_name_input">Last Name</label>
                    <input type="text" class="form-control" name="last_name" id="last_name_input"
                            value="<?= htmlspecialchars($oldLast ?? '') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-semibold mb-1" for="username">Username</label>
                <input type="text" class="form-control" name="user_name" id="username"
                        value="<?= htmlspecialchars($oldUsername ?? '') ?>" required>
                <small id="usernameError" class="text-danger d-none">
                    Username must be 4–20 characters and contain only letters, numbers, or underscore.
                </small>
            </div>

            <div class="mb-3">
                <label class="fw-semibold mb-1" for="email_input">Email Address</label>
                <input type="email" class="form-control" name="email" id="email_input"
                        value="<?= htmlspecialchars($oldEmail ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="fw-semibold mb-1" for="phone_num">Phone Number</label>
                <input type="tel" class="form-control" name="phone_num" id="phone_num"
                        value="<?= htmlspecialchars($oldPhone ?? '') ?>" placeholder="09XXXXXXXXX" required>
                <small id="phoneError" class="text-danger d-none">
                    Phone number must start with 09 and be exactly 11 digits.
                </small>
            </div>

            
            <div class="mb-3">
                <label class="fw-semibold mb-1" for="password">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" name="password"  id="password"  value="<?= htmlspecialchars($oldPass ?? '') ?>" required>
                    <span class="input-group-text eye-toggle" data-target="password" style="cursor: pointer;">
                        <i class="bi bi-eye-slash"></i>
                    </span>
                </div>
                <small id="passwordComplexityError" class="text-danger d-none mt-1"></small>
            </div>

            <div class="mb-4">
                <label class="fw-semibold mb-1" for="confirm_password">Confirm Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" value="<?= htmlspecialchars($oldConPass ?? '') ?>" required>
                    <span class="input-group-text eye-toggle" data-target="confirm_password" style="cursor: pointer;">
                        <i class="bi bi-eye-slash"></i>
                    </span>
                </div>
                <small id="passwordMatchError" class="text-danger d-none mt-1">
                    Passwords do not match.
                </small>
            </div>

            <button type="submit" class="btn btn-red w-100">Create Account</button>

            <p class="text-center mt-3 mb-0">
                Already have an account?
                <a href="login.php" class="text-danger fw-semibold">Login</a>
            </p>

        </form>
    </div>
</div>

<script src="js/reg.js"></script>
</body>
 
</html>