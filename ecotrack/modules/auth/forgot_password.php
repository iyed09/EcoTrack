<?php
require_once '../../includes/config.php';
require_once '../../includes/mail_helper.php';

if (isLoggedIn()) {
    redirect('../../dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate 6-digit code
            $code = sprintf("%06d", mt_rand(1, 999999));
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            // Delete required previous codes for this email to avoid clutter
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            // Insert new code
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, code, expires_at) VALUES (?, ?, ?)");
            if ($stmt->execute([$email, $code, $expiry])) {
                // Send email
                $subject = "Password Reset Code - " . SITE_NAME;
                // Send email
                $subject = "Password Reset - " . SITE_NAME;
                
                // HTML Email Template
                $message = '
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Password Reset</title>
                </head>
                <body style="font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0;">
                    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 0; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 40px; margin-bottom: 40px;">
                        
                        <!-- Header -->
                        <div style="background: linear-gradient(135deg, #1b5e20 0%, #4caf50 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">EcoTrack</h1>
                            <p style="color: #e8f5e9; margin: 5px 0 0 0; font-size: 16px;">Password Reset Request</p>
                        </div>
                        
                        <!-- Content -->
                        <div style="padding: 40px 30px; text-align: center;">
                            <h2 style="color: #2c3e50; font-size: 22px; margin-bottom: 20px;">Forgot your password?</h2>
                            <p style="color: #555555; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
                                No worries! We received a request to reset your password. Use the code below to complete the process.
                            </p>
                            
                            <!-- Boxed Code -->
                            <div style="background-color: #e8f5e9; border: 2px dashed #4caf50; border-radius: 8px; padding: 20px; display: inline-block; margin-bottom: 30px;">
                                <span style="font-size: 32px; font-weight: bold; color: #1b5e20; letter-spacing: 5px;">' . $code . '</span>
                            </div>
                            
                            <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 0;">
                                This code will expire in 15 minutes.<br>
                                If you did not request this, you can safely ignore this email.
                            </p>
                        </div>
                        
                        <!-- Footer -->
                        <div style="background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                &copy; ' . date("Y") . ' EcoTrack. All rights reserved.<br>
                                Making the world greener, one step at a time.
                            </p>
                        </div>
                    </div>
                </body>
                </html>';
                
                if (sendMail($email, $subject, $message)) {
                    $_SESSION['reset_email'] = $email;
                    redirect('verify_code.php');
                } else {
                    $error = 'Failed to send verification code. Please try again.';
                }
            } else {
                $error = 'Database error. Please try again.';
            }
        } else {
            // For security, don't reveal if user exists, but for UX on this project we might just say "Email not found" or identical generic msg.
            // Let's go with generic.
            // But for "Mission Impossible" user request, let's be helpfully clear:
            $error = 'We could not find an account with that email.'; 
        }
    }
}

$pageTitle = 'Forgot Password';
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
                        <h3 class="mt-2">Forgot Password</h3>
                        <p class="text-muted">Enter your email to receive a code</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" class="custom-form">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>

                        <button type="submit" class="custom-btn w-100 mb-3">Send Code</button>

                        <p class="text-center text-muted mb-0">
                            Remember your password? <a href="login.php">Sign In</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
