<?php
class Controller {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    protected function view($view, $data = []) {
        extract($data);
        $viewFile = APP_ROOT . '/app/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            ob_start();
            require $viewFile;
            $content = ob_get_clean();
            return $content;
        } else {
            die("View file not found: " . $viewFile);
        }
    }

    protected function render($view, $data = [], $layout = 'main') {
        $data['content'] = $this->view($view, $data);
        $layoutFile = APP_ROOT . '/app/views/layouts/' . $layout . '.php';
        
        if (file_exists($layoutFile)) {
            extract($data);
            require $layoutFile;
        } else {
            echo $data['content'];
        }
    }

    protected function redirect($url) {
        header("Location: " . URL_ROOT . $url);
        exit;
    }

    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    protected function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('/auth/login');
        }
    }

    protected function requireAdmin() {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            $this->redirect('/');
        }
    }

    protected function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    protected function formatDate($date) {
        return date('M d, Y', strtotime($date));
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
