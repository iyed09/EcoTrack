<?php
require_once '../../includes/config.php';

if (isLoggedIn()) {
    redirect('../../dashboard.php');
}

// Ensure we have an email in session from the previous step
if (!isset($_SESSION['reset_email'])) {
    redirect('forgot_password.php');
}

$error = '';
$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = sanitize($_POST['code'] ?? '');

    if (empty($code)) {
        $error = 'Please enter the verification code.';
    } else {
        // Verify code
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND code = ? AND expires_at > NOW()");
        $stmt->execute([$email, $code]);
        $resetRequest = $stmt->fetch();

        if ($resetRequest) {
            // Code valid
            $_SESSION['reset_verified'] = true;
            redirect('reset_password.php');
        } else {
            $error = 'Invalid or expired verification code.';
        }
    }
}

$pageTitle = 'Verify Code';
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
                        <h3 class="mt-2">Verify Code</h3>
                        <p class="text-muted">Enter the 6-digit code sent to <?php echo htmlspecialchars($email); ?></p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" class="custom-form">
                        <div class="mb-3">
                            <label class="form-label">Verification Code</label>
                            <input type="text" name="code" class="form-control text-center fs-4 letter-spacing-2" placeholder="000000" maxlength="6" required>
                        </div>

                        <button type="submit" class="custom-btn w-100 mb-3">Verify Code</button>
                        
                        <p class="text-center text-muted mb-0">
                            Didn't receive code? <a href="forgot_password.php">Resend</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
