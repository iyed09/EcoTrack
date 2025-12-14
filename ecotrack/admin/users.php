<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_user') {
            $name = sanitize($_POST['name']);
            $email = sanitize($_POST['email']);
            $password = $_POST['password'];
            $role = sanitize($_POST['role']);
            
            if (empty($name) || empty($email) || empty($password)) {
                $error = 'All fields are required.';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email already exists.';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                    if ($stmt->execute([$name, $email, $hashedPassword, $role])) {
                        $success = 'User added successfully!';
                    } else {
                        $error = 'Failed to add user.';
                    }
                }
            }
        } elseif ($_POST['action'] === 'edit_user') {
            $id = (int)$_POST['user_id'];
            $name = sanitize($_POST['name']);
            $email = sanitize($_POST['email']);
            $role = sanitize($_POST['role']);
            
            if (empty($name) || empty($email)) {
                $error = 'Name and email are required.';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $id]);
                if ($stmt->fetch()) {
                    $error = 'Email already exists for another user.';
                } else {
                    if (!empty($_POST['password'])) {
                        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?");
                        $result = $stmt->execute([$name, $email, $hashedPassword, $role, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
                        $result = $stmt->execute([$name, $email, $role, $id]);
                    }
                    
                    if ($result) {
                        $success = 'User updated successfully!';
                    } else {
                        $error = 'Failed to update user.';
                    }
                }
            }
        }
    }
}

if (isset($_GET['delete']) && $_GET['delete'] != $_SESSION['user_id']) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $success = 'User deleted successfully!';
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
        $success = 'User role updated successfully!';
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Manage Users';
include 'includes/admin_header.php';
?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4><i class="bx bx-user me-2"></i>Manage Users</h4>
            <p class="mb-0">View and manage all registered users</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary fs-6"><?php echo count($users); ?> Users</span>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bx bx-user-plus me-1"></i> Add User
            </button>
        </div>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bx bx-error-circle me-1"></i> <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bx bx-check-circle me-1"></i> <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">All Users</h5>
        <div class="d-flex gap-2">
            <input type="text" class="form-control form-control-sm" id="searchUsers" placeholder="Search users..." style="width: 200px;">
        </div>
    </div>
    <div class="card-body">
        <?php if (count($users) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong>#<?php echo $user['id']; ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle <?php echo $user['role'] === 'admin' ? 'bg-label-success' : 'bg-label-primary'; ?>">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="fw-medium"><?php echo htmlspecialchars($user['name']); ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-label-success' : 'bg-label-secondary'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <small><?php echo formatDate($user['created_at']); ?></small>
                        </td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <div class="d-flex gap-1">
                                <button type="button" 
                                        class="btn btn-sm btn-label-info edit-user-btn" 
                                        title="Edit User"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal"
                                        data-user-id="<?php echo $user['id']; ?>"
                                        data-user-name="<?php echo htmlspecialchars($user['name']); ?>"
                                        data-user-email="<?php echo htmlspecialchars($user['email']); ?>"
                                        data-user-role="<?php echo $user['role']; ?>">
                                    <i class="bx bx-edit-alt"></i>
                                </button>
                                <a href="users.php?toggle_role=<?php echo $user['id']; ?>" 
                                   class="btn btn-sm btn-label-primary" 
                                   title="Toggle Role"
                                   data-bs-toggle="tooltip">
                                    <i class="bx bx-refresh"></i>
                                </a>
                                <a href="users.php?delete=<?php echo $user['id']; ?>" 
                                   class="btn btn-sm btn-label-danger delete-btn" 
                                   title="Delete User"
                                   data-bs-toggle="tooltip"
                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                    <i class="bx bx-trash"></i>
                                </a>
                            </div>
                            <?php else: ?>
                            <span class="badge bg-label-info">
                                <i class="bx bx-check me-1"></i>Current User
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bx bx-user-x text-muted" style="font-size: 4rem;"></i>
            <p class="text-muted mt-3">No users found.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4 mb-4">
        <div class="stats-card">
            <div class="stats-icon primary">
                <i class="bx bx-user"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo count(array_filter($users, fn($u) => $u['role'] === 'user')); ?></h3>
                <p>Regular Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="bx bx-shield"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></h3>
                <p>Administrators</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="stats-card">
            <div class="stats-icon info">
                <i class="bx bx-group"></i>
            </div>
            <div class="stats-content">
                <h3><?php echo count($users); ?></h3>
                <p>Total Users</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_user">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">
                        <i class="bx bx-user-plus me-2"></i>Add New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="addName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="addEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="addEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="addPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="addPassword" name="password" required minlength="6">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    <div class="mb-3">
                        <label for="addRole" class="form-label">Role</label>
                        <select class="form-select" id="addRole" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i>Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">
                        <i class="bx bx-edit me-2"></i>Edit User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPassword" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="editPassword" name="password" minlength="6">
                        <small class="text-muted">Leave empty to keep current password</small>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-select" id="editRole" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('searchUsers').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

document.querySelectorAll('.edit-user-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('editUserId').value = this.dataset.userId;
        document.getElementById('editName').value = this.dataset.userName;
        document.getElementById('editEmail').value = this.dataset.userEmail;
        document.getElementById('editRole').value = this.dataset.userRole;
        document.getElementById('editPassword').value = '';
    });
});
</script>

<?php include 'includes/admin_footer.php'; ?>
