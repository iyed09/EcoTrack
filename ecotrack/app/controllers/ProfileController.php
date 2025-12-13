<?php
class ProfileController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    public function index() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $user = $this->userModel->findById($userId);
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update_profile'])) {
                $name = $this->sanitize($_POST['name'] ?? '');
                $email = $this->sanitize($_POST['email'] ?? '');

                if (empty($name) || empty($email)) {
                    $error = 'Name and email are required.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Please enter a valid email address.';
                } elseif ($this->userModel->emailExists($email, $userId)) {
                    $error = 'This email is already in use.';
                } else {
                    $this->userModel->updateProfile($userId, $name, $email);
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $success = 'Profile updated successfully!';
                    $user['name'] = $name;
                    $user['email'] = $email;
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
                    $this->userModel->updatePassword($userId, $newPassword);
                    $success = 'Password changed successfully!';
                }
            }

            if (isset($_POST['delete_account'])) {
                $this->userModel->delete($userId);
                session_destroy();
                $this->redirect('/');
            }
        }

        $data = [
            'pageTitle' => 'Profile',
            'user' => $user,
            'error' => $error,
            'success' => $success,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('profile/index', $data);
    }
}
