<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$message = "";

/* ==========================
   HANDLE PROFILE UPDATE
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $update_success = true;

    // 1. Check for Duplicate Username or Email (Excluding current user)
    $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
    $checkStmt->bind_param("ssi", $new_username, $new_email, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $message = "<div class='alert alert-danger small p-2'>Username or Email is already taken by another user.</div>";
        $update_success = false;
    } else {
        // 2. Handle Avatar Upload
        if (!empty($_FILES['avatar']['name'])) {
            $target_dir = "uploads/avatars/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $file_extension = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
            $new_filename = "avatar_" . $user_id . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            $check = getimagesize($_FILES["avatar"]["tmp_name"]);
            if($check !== false) {
                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                    $updateAvatar = $conn->prepare("UPDATE users SET avatar_url = ? WHERE user_id = ?");
                    $updateAvatar->bind_param("si", $target_file, $user_id);
                    $updateAvatar->execute();
                } else {
                    $update_success = false;
                    $message .= "<div class='alert alert-danger small p-2'>Failed to upload image.</div>";
                }
            } else {
                $update_success = false;
                $message .= "<div class='alert alert-danger small p-2'>File is not a valid image.</div>";
            }
        }

        // 3. Handle Text Data
        if ($update_success && !empty($new_username) && filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $updateStmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
            $updateStmt->bind_param("ssi", $new_username, $new_email, $user_id);
            
            if ($updateStmt->execute()) {
                $message = "<div class='alert alert-success small p-2'>Profile updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger small p-2'>Error updating database. Please try again.</div>";
            }
        } elseif ($update_success) {
            $message = "<div class='alert alert-warning small p-2'>Please provide a valid username and email.</div>";
        }
    }
}

/* ==========================
   FETCH CURRENT DATA
========================== */
$userStmt = $conn->prepare("SELECT email, username, avatar_url FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$avatar = !empty($user['avatar_url']) ? $user['avatar_url'] : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | <?= htmlspecialchars($user['username']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="img/media/logo2.png">
    <link rel="stylesheet" href="css/header.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .edit-card { background: white; border-radius: 15px; border: none; max-width: 500px; margin: 50px auto; }
        .profile-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; margin-bottom: 20px; border: 3px solid #f4f7f6; }
        .form-control:focus { border-color: #1a1a1a; box-shadow: none; }
        .btn-black { background: #1a1a1a; color: white; border: none; padding: 10px 25px; border-radius: 8px; font-weight: 600; }
        .btn-black:hover { background: #333; color: white; }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <div class="card edit-card shadow-sm p-4 p-md-5">
        <div class="text-center">
            <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="profile-preview shadow-sm">
            <h4 class="fw-bold mb-1">Edit Settings</h4>
            <p class="text-muted small mb-4">Update your account information</p>
        </div>

        <?= $message ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Profile Photo</label>
                <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="username" class="form-control bg-light border-start-0" 
                           value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control bg-light border-start-0" 
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="update_profile" class="btn btn-black shadow-sm">
                    Save Changes
                </button>
                <a href="dashboard.php" class="btn btn-link text-muted text-decoration-none small">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>