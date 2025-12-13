<?php
class AdminController extends Controller {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->requireAdmin();

        $userModel = new User();
        $reportModel = new TrashReport();
        $energyModel = new EnergyConsumption();
        $transportModel = new TransportEntry();

        $totalUsers = $userModel->count();
        $totalReports = $reportModel->count();
        $pendingReports = $reportModel->countPending();
        $totalEnergy = $energyModel->getGlobalTotal();
        $totalTransport = $transportModel->getGlobalTotal();

        $recentUsers = $userModel->fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
        $recentReports = $reportModel->getRecentWithReporter(5);

        $data = [
            'pageTitle' => 'Admin Dashboard',
            'totalUsers' => $totalUsers,
            'totalReports' => $totalReports,
            'pendingReports' => $pendingReports,
            'totalEnergy' => $totalEnergy,
            'totalTransport' => $totalTransport,
            'recentUsers' => $recentUsers,
            'recentReports' => $recentReports,
            'isLoggedIn' => true,
            'isAdmin' => true
        ];

        $this->render('admin/index', $data, 'admin');
    }

    public function users() {
        $this->requireAdmin();
        $userModel = new User();
        $users = $userModel->findAll('created_at DESC');

        $data = [
            'pageTitle' => 'Manage Users',
            'users' => $users,
            'isLoggedIn' => true,
            'isAdmin' => true
        ];

        $this->render('admin/users', $data, 'admin');
    }

    public function reports() {
        $this->requireAdmin();
        $reportModel = new TrashReport();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
            $reportId = (int)$_POST['report_id'];
            $status = $this->sanitize($_POST['status'] ?? 'pending');
            $response = $this->sanitize($_POST['admin_response'] ?? '');
            $reportModel->updateStatus($reportId, $status, $response);
        }

        $reports = $reportModel->getAllWithReporter();

        $data = [
            'pageTitle' => 'Manage Reports',
            'reports' => $reports,
            'isLoggedIn' => true,
            'isAdmin' => true
        ];

        $this->render('admin/reports', $data, 'admin');
    }

    public function statistics() {
        $this->requireAdmin();

        $data = [
            'pageTitle' => 'Statistics',
            'isLoggedIn' => true,
            'isAdmin' => true
        ];

        $this->render('admin/statistics', $data, 'admin');
    }

    public function posts() {
        $this->requireAdmin();

        $data = [
            'pageTitle' => 'Posts',
            'isLoggedIn' => true,
            'isAdmin' => true
        ];

        $this->render('admin/posts', $data, 'admin');
    }

    public function productsWaste() {
        $this->requireAdmin();

        $data = [
            'pageTitle' => 'Products & Waste',
            'isLoggedIn' => true,
            'isAdmin' => true
        ];

        $this->render('admin/products-waste', $data, 'admin');
    }

    public function score() {
        $this->requireAdmin();

        $data = [
            'pageTitle' => 'Score Management',
            'isLoggedIn' => true,
            'isAdmin' => true
        ];

        $this->render('admin/score', $data, 'admin');
    }

    public function apiSearch() {
        $this->requireAdmin();
        $query = $_GET['q'] ?? '';
        $this->json(['results' => [], 'query' => $query]);
    }
}
