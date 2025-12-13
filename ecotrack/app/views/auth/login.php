<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-12">
            <div class="bg-white rounded-4 shadow-lg p-5">
                <div class="text-center mb-4">
                    <a href="<?php echo URL_ROOT; ?>/" class="text-decoration-none">
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
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="<?php echo URL_ROOT; ?>/auth/forgot-password" class="text-decoration-none small">Forgot Password?</a>
                    </div>
                    <button type="submit" class="custom-btn w-100 mb-3">Sign In</button>
                    <p class="text-center text-muted mb-0">
                        Don't have an account? <a href="<?php echo URL_ROOT; ?>/auth/register">Sign Up</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
