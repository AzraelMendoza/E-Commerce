<?php
// BACKEND REMAINS UNTOUCHED
require 'db.php';
session_start();

$message = "";
$alert_class = "alert-danger";

if (isset($_GET['msg']) && $_GET['msg'] === 'login_required') {
    $message = "Please log in first to view products or access your cart.";
    $alert_class = "alert-warning";
}

$remembered_email = isset($_COOKIE['user_login']) ? $_COOKIE['user_login'] : "";
$remembered_password = isset($_COOKIE['user_password']) ? $_COOKIE['user_password'] : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    $stmt = $conn->prepare("SELECT user_id, password_hash, is_email_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (!password_verify($password, $user['password_hash'])) {
            $message = "Incorrect password.";
            $alert_class = "alert-danger";
        } elseif ($user['is_email_verified'] == 0) {
            $message = "Please verify your email before logging in.";
            $alert_class = "alert-danger";
        } else {
            if ($remember) {
                setcookie("user_login", $email, time() + (86400 * 30), "/");
                setcookie("user_password", $password, time() + (86400 * 30), "/");
            } else {
                setcookie("user_login", "", time() - 3600, "/");
                setcookie("user_password", "", time() - 3600, "/");
            }
            $_SESSION['user_id'] = $user['user_id'];
            header("Location: welcome.php");
            exit;
        }
    } else {
        $message = "Email not found.";
        $alert_class = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CubeClass</title>
    <link rel="icon" type="image/png" href="img/media/logo2.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* CSS FIXES */
        body {
            background-color: #f4f4f4;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .login-card h3 {
            color: #1a1a1a;
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
        }

        .form-control {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #8b0000;
            box-shadow: 0 0 0 0.2rem rgba(139, 0, 0, 0.1);
        }

        /* INPUT WRAPPER FOR EYE ICON */
        .password-field-group {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #8b0000;
            font-size: 1.2rem;
            z-index: 10;
            display: flex; /* Ensures icon alignment */
        }

        /* BUTTON DESIGN */
        .btn-login {
            background-color: #8b0000;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #1a1a1a; /* Turns black on hover */
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .text-danger-link {
            color: #8b0000;
            text-decoration: none;
            font-weight: 600;
        }

        .text-danger-link:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h3>Login to CubeClass</h3>

    <?php if(!empty($message)): ?>
        <div class="alert <?= $alert_class ?> text-center"><?= $message ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required value="<?= htmlspecialchars($remembered_email) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="password-field-group">
                <input type="password" name="password" id="password" class="form-control pe-5" placeholder="Enter your password" required value="<?= htmlspecialchars($remembered_password) ?>">
                <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" <?= $remembered_email ? 'checked' : '' ?>>
                <label class="form-check-label small" for="remember">Remember me</label>
            </div>
            <a href="forgot_password.php" class="text-danger-link small">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn-login">Login</button>

        <div class="text-center mt-4">
            <span class="small text-muted">Don't have an account?</span>
            <a href="register.php" class="text-danger-link small ms-1">Register</a>
        </div>
    </form>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>

</body>
</html>