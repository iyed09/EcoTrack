<?php
class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Please fill in all fields.';
            } else {
                $user = $this->userModel->findByEmail($email);

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    $this->redirect('/dashboard');
                } else {
                    $error = 'Invalid email or password.';
                }
            }
        }

        $data = [
            'pageTitle' => 'Login',
            'error' => $error,
            'email' => $_POST['email'] ?? ''
        ];
        $this->render('auth/login', $data, 'auth');
    }

    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $this->sanitize($_POST['name'] ?? '');
            $email = $this->sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
                $error = 'Please fill in all fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif ($this->userModel->emailExists($email)) {
                $error = 'This email is already registered.';
            } else {
                $userId = $this->userModel->create($name, $email, $password);
                
                if ($userId) {
                    $pointsManager = new PointsManager();
                    $pointsManager->awardPoints($userId, PointsManager::getPointsForAction('register'), 'register', 'Welcome bonus for joining EcoTrack');
                    
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }

        $data = [
            'pageTitle' => 'Register',
            'error' => $error,
            'success' => $success,
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];
        $this->render('auth/register', $data, 'auth');
    }

    public function logout() {
        session_destroy();
        $this->redirect('/');
    }

    public function forgotPassword() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->sanitize($_POST['email'] ?? '');

            if (empty($email)) {
                $error = 'Please enter your email address.';
            } elseif (!$this->userModel->findByEmail($email)) {
                $error = 'No account found with this email.';
            } else {
                $code = sprintf("%06d", mt_rand(0, 999999));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $passwordReset = new PasswordReset();
                $passwordReset->createReset($email, $code, $expiresAt);
                
                $_SESSION['reset_email'] = $email;
                $success = "Your reset code is: $code (In production, this would be sent via email)";
            }
        }

        $data = [
            'pageTitle' => 'Forgot Password',
            'error' => $error,
            'success' => $success
        ];
        $this->render('auth/forgot-password', $data, 'auth');
    }

    public function verifyCode() {
        if (!isset($_SESSION['reset_email'])) {
            $this->redirect('/auth/forgot-password');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = $this->sanitize($_POST['code'] ?? '');
            $email = $_SESSION['reset_email'];

            $passwordReset = new PasswordReset();
            $reset = $passwordReset->findValidCode($email, $code);

            if ($reset) {
                $_SESSION['reset_verified'] = true;
                $this->redirect('/auth/reset-password');
            } else {
                $error = 'Invalid or expired code.';
            }
        }

        $data = [
            'pageTitle' => 'Verify Code',
            'error' => $error
        ];
        $this->render('auth/verify-code', $data, 'auth');
    }

    public function resetPassword() {
        if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_verified'])) {
            $this->redirect('/auth/forgot-password');
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } else {
                $email = $_SESSION['reset_email'];
                $user = $this->userModel->findByEmail($email);
                
                if ($user) {
                    $this->userModel->updatePassword($user['id'], $password);
                    
                    $passwordReset = new PasswordReset();
                    $passwordReset->deleteByEmail($email);
                    
                    unset($_SESSION['reset_email'], $_SESSION['reset_verified']);
                    
                    $success = 'Password reset successful! You can now login.';
                }
            }
        }

        $data = [
            'pageTitle' => 'Reset Password',
            'error' => $error,
            'success' => $success
        ];
        $this->render('auth/reset-password', $data, 'auth');
    }
}
