<?php
class App {
    protected $router;

    public function __construct() {
        $this->router = new Router();
        $this->loadRoutes();
    }

    private function loadRoutes() {
        $this->router->add('', ['controller' => 'home', 'action' => 'index']);
        $this->router->add('/', ['controller' => 'home', 'action' => 'index']);
        
        $this->router->add('auth/login', ['controller' => 'auth', 'action' => 'login']);
        $this->router->add('auth/register', ['controller' => 'auth', 'action' => 'register']);
        $this->router->add('auth/logout', ['controller' => 'auth', 'action' => 'logout']);
        $this->router->add('auth/forgot-password', ['controller' => 'auth', 'action' => 'forgotPassword']);
        $this->router->add('auth/reset-password', ['controller' => 'auth', 'action' => 'resetPassword']);
        $this->router->add('auth/verify-code', ['controller' => 'auth', 'action' => 'verifyCode']);
        
        $this->router->add('dashboard', ['controller' => 'dashboard', 'action' => 'index']);
        $this->router->add('profile', ['controller' => 'profile', 'action' => 'index']);
        
        $this->router->add('energy', ['controller' => 'energy', 'action' => 'index']);
        $this->router->add('energy/add', ['controller' => 'energy', 'action' => 'add']);
        $this->router->add('energy/edit/{id}', ['controller' => 'energy', 'action' => 'edit']);
        $this->router->add('energy/delete/{id}', ['controller' => 'energy', 'action' => 'delete']);
        
        $this->router->add('transport', ['controller' => 'transport', 'action' => 'index']);
        $this->router->add('transport/add', ['controller' => 'transport', 'action' => 'add']);
        $this->router->add('transport/edit/{id}', ['controller' => 'transport', 'action' => 'edit']);
        $this->router->add('transport/delete/{id}', ['controller' => 'transport', 'action' => 'delete']);
        
        $this->router->add('waste', ['controller' => 'waste', 'action' => 'index']);
        $this->router->add('waste/add', ['controller' => 'waste', 'action' => 'add']);
        $this->router->add('waste/edit/{id}', ['controller' => 'waste', 'action' => 'edit']);
        $this->router->add('waste/delete/{id}', ['controller' => 'waste', 'action' => 'delete']);
        
        $this->router->add('reports', ['controller' => 'reports', 'action' => 'index']);
        $this->router->add('reports/add', ['controller' => 'reports', 'action' => 'add']);
        $this->router->add('reports/radar', ['controller' => 'reports', 'action' => 'radar']);
        $this->router->add('reports/analyze', ['controller' => 'reports', 'action' => 'analyze']);
        
        $this->router->add('rewards', ['controller' => 'rewards', 'action' => 'index']);
        $this->router->add('rewards/leaderboard', ['controller' => 'rewards', 'action' => 'leaderboard']);
        $this->router->add('rewards/achievements', ['controller' => 'rewards', 'action' => 'achievements']);
        
        $this->router->add('admin', ['controller' => 'admin', 'action' => 'index']);
        $this->router->add('admin/users', ['controller' => 'admin', 'action' => 'users']);
        $this->router->add('admin/reports', ['controller' => 'admin', 'action' => 'reports']);
        $this->router->add('admin/statistics', ['controller' => 'admin', 'action' => 'statistics']);
        $this->router->add('admin/posts', ['controller' => 'admin', 'action' => 'posts']);
        $this->router->add('admin/products-waste', ['controller' => 'admin', 'action' => 'productsWaste']);
        $this->router->add('admin/score', ['controller' => 'admin', 'action' => 'score']);
        $this->router->add('admin/api/search', ['controller' => 'admin', 'action' => 'apiSearch']);
    }

    public function run() {
        $url = isset($_GET['url']) ? $_GET['url'] : '';
        $this->router->dispatch($url);
    }
}
