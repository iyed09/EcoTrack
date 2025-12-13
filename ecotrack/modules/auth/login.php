<?php
require_once '../../includes/config.php';

if (isLoggedIn()) {
    redirect('../../dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            redirect('../../dashboard.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center" style="min-height: 100vh; background: linear-gradient(135deg, #1b5e20 0%, #4caf50 50%, #81c784 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7 col-12">
                <div class="bg-white rounded-4 shadow-lg p-5">
                    <div class="text-center mb-4">
                        <a href="../../index.php" class="text-decoration-none">
                            <i class="bi-globe-americas fs-1 text-success"></i>
                            <h3 class="mt-2">EcoTrack</h3>
                        </a>
                        <p class="text-muted">Sign in to your account</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" class="custom-form">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="forgot_password.php" class="text-decoration-none small">Forgot Password?</a>
                        </div>

                        <button type="submit" class="custom-btn w-100 mb-3">Sign In</button>

                        <p class="text-center text-muted mb-0">
                            Don't have an account? <a href="register.php">Sign Up</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
