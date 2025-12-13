<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4><i class="bi-people me-2"></i>Manage Users</h4>
        <p class="mb-0 text-muted">View and manage all registered users</p>
    </div>
    <span class="badge bg-primary fs-6"><?php echo count($users); ?> Users</span>
</div>

<?php if (isset($error) && $error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="module-card">
    <div class="module-card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Points</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($user['total_points'] ?? 0); ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="<?php echo URL_ROOT; ?>/admin/users?toggle_role=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary" title="Toggle Role">
                                <i class="bi-arrow-repeat"></i>
                            </a>
                            <a href="<?php echo URL_ROOT; ?>/admin/users?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?');" title="Delete">
                                <i class="bi-trash"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="<?php echo URL_ROOT; ?>/admin" class="btn btn-outline-secondary"><i class="bi-arrow-left me-2"></i>Back to Dashboard</a>
</div>
