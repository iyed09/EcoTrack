<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-person-circle me-2"></i>My Profile</h2>
        <p>Manage your account settings</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success) && $success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="module-card">
                    <div class="module-card-header">
                        <h5 class="mb-0"><i class="bi-person me-2"></i>Profile Information</h5>
                    </div>
                    <div class="module-card-body">
                        <form method="POST" class="custom-form">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Member Since</label>
                                <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" disabled>
                            </div>

                            <button type="submit" class="custom-btn">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="module-card">
                    <div class="module-card-header">
                        <h5 class="mb-0"><i class="bi-key me-2"></i>Change Password</h5>
                    </div>
                    <div class="module-card-body">
                        <form method="POST" class="custom-form">
                            <input type="hidden" name="change_password" value="1">
                            
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>

                            <button type="submit" class="custom-btn">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="module-card border-danger">
                    <div class="module-card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi-exclamation-triangle me-2"></i>Danger Zone</h5>
                    </div>
                    <div class="module-card-body">
                        <p class="text-muted">Once you delete your account, there is no going back. Please be certain.</p>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                            <input type="hidden" name="delete_account" value="1">
                            <button type="submit" class="btn btn-danger">Delete Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
