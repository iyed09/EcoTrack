<?php
require_once '../../includes/config.php';

if (isLoggedIn()) {
    redirect('../../dashboard.php');
}

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true) {
    redirect('forgot_password.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $email = $_SESSION['reset_email'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        if ($stmt->execute([$hashed_password, $email])) {
            // Clear reset session
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_verified']);
            
            // Cleanup used code
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            $success = 'Password successfully reset! You can now login.';
            // JavaScript redirect after short delay
            echo '<script>setTimeout(function(){ window.location.href = "login.php"; }, 2000);</script>';
        } else {
            $error = 'Failed to update password. Please try again.';
        }
    }
}

$pageTitle = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center" style="min-height: 100vh; background: linear-gradient(135deg, #1b5e20 0%, #4caf50 50%, #81c784 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7 col-12">
                <div class="bg-white rounded-4 shadow-lg p-5">
                    <div class="text-center mb-4">
                        <h3 class="mt-2">Reset Password</h3>
                        <p class="text-muted">Enter your new password</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <p class="text-center">Redirecting to login...</p>
                    <?php else: ?>

                    <form method="POST" class="custom-form">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="New password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                        </div>

                        <button type="submit" class="custom-btn w-100 mb-3">Reset Password</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
