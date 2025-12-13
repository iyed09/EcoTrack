<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('modules/auth/login.php');
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');

        if (empty($name) || empty($email)) {
            $error = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkEmail->execute([$email, $userId]);
            
            if ($checkEmail->fetch()) {
                $error = 'This email is already in use.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                if ($stmt->execute([$name, $email, $userId])) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $success = 'Profile updated successfully!';
                    $user['name'] = $name;
                    $user['email'] = $email;
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        }
    }

    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required.';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            if ($stmt->execute([$hashedPassword, $userId])) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password.';
            }
        }
    }

    if (isset($_POST['delete_account'])) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$userId])) {
            session_destroy();
            header("Location: index.php");
            exit;
        } else {
            $error = 'Failed to delete account.';
        }
    }
}

$pageTitle = 'Profile';
include 'includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2>My Profile</h2>
        <p>Manage your account settings</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-12 mb-4">
                <div class="sidebar text-center">
                    <div class="mb-4">
                        <i class="bi-person-circle" style="font-size: 80px; color: var(--primary-color);"></i>
                    </div>
                    <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="status-badge <?php echo $user['role'] === 'admin' ? 'status-resolved' : 'status-pending'; ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                    <hr class="my-4">
                    <p class="text-muted mb-0">
                        <small>Member since <?php echo formatDate($user['created_at']); ?></small>
                    </p>
                </div>
            </div>

            <div class="col-lg-8 col-12">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="module-card mb-4">
                    <div class="module-card-header" style="background: linear-gradient(135deg, #4caf50, #81c784);">
                        <h5 class="mb-0"><i class="bi-person me-2"></i>Update Profile</h5>
                    </div>
                    <div class="module-card-body">
                        <form method="POST" class="custom-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                            <button type="submit" name="update_profile" class="custom-btn">Update Profile</button>
                        </form>
                    </div>
                </div>

                <div class="module-card mb-4">
                    <div class="module-card-header" style="background: linear-gradient(135deg, #2196f3, #03a9f4);">
                        <h5 class="mb-0"><i class="bi-key me-2"></i>Change Password</h5>
                    </div>
                    <div class="module-card-body">
                        <form method="POST" class="custom-form">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            <button type="submit" name="change_password" class="custom-btn">Change Password</button>
                        </form>
                    </div>
                </div>

                <div class="module-card">
                    <div class="module-card-header report">
                        <h5 class="mb-0"><i class="bi-exclamation-triangle me-2"></i>Danger Zone</h5>
                    </div>
                    <div class="module-card-body">
                        <p class="text-muted">Once you delete your account, all your data will be permanently removed.</p>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                            <button type="submit" name="delete_account" class="btn btn-danger">Delete Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
