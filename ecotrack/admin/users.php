<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

if (isset($_GET['delete']) && $_GET['delete'] != $_SESSION['user_id']) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    redirect('users.php');
}

if (isset($_GET['toggle_role'])) {
    $id = (int)$_GET['toggle_role'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        $newRole = $user['role'] === 'admin' ? 'user' : 'admin';
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$newRole, $id]);
    }
    redirect('users.php');
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Manage Users';
include '../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h2><i class="bi-people me-2"></i>Manage Users</h2>
        <p>View and manage all registered users</p>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-12 mb-4">
                <div class="sidebar">
                    <h5 class="mb-4">Admin Menu</h5>
                    <ul class="sidebar-menu">
                        <li><a href="index.php"><i class="bi-speedometer2"></i> Dashboard</a></li>
                        <li><a href="users.php" class="active"><i class="bi-people"></i> Manage Users</a></li>
                        <li><a href="reports.php"><i class="bi-flag"></i> Manage Reports</a></li>
                        <li><a href="statistics.php"><i class="bi-bar-chart"></i> Statistics</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-9 col-12">
                <div class="module-card">
                    <div class="module-card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $user['role'] === 'admin' ? 'status-resolved' : 'status-pending'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="users.php?toggle_role=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary" title="Toggle Role">
                                                <i class="bi-arrow-repeat"></i>
                                            </a>
                                            <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
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
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
