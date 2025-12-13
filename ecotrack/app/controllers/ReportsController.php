<?php
class ReportsController extends Controller {
    private $reportModel;

    public function __construct() {
        parent::__construct();
        $this->reportModel = new TrashReport();
    }

    public function index() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $reports = $this->reportModel->getByUser($userId);

        $data = [
            'pageTitle' => 'My Reports',
            'reports' => $reports,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('reports/index', $data);
    }

    public function add() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $locationDescription = $this->sanitize($_POST['location_description'] ?? '');
            $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
            $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
            $description = $this->sanitize($_POST['description'] ?? '');
            $photoPath = null;

            if (empty($locationDescription) || empty($description)) {
                $error = 'Please fill in all required fields.';
            } else {
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = UPLOAD_PATH . 'reports/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileName = 'report_' . uniqid() . '.' . pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                        $photoPath = 'uploads/reports/' . $fileName;
                    }
                }

                $reportId = $this->reportModel->create($userId, $locationDescription, $latitude, $longitude, $description, $photoPath);
                
                if ($reportId) {
                    $points = PointsManager::getPointsForAction('trash_report');
                    $pointsManager = new PointsManager();
                    $pointsManager->awardPoints($userId, $points, 'trash_report', 'Reported trash in community', $reportId, 'trash_reports');
                    
                    $_SESSION['points_earned'] = $points;
                    $_SESSION['points_message'] = 'Thanks for reporting! You earned points.';
                    $this->redirect('/reports');
                } else {
                    $error = 'Failed to submit report. Please try again.';
                }
            }
        }

        $data = [
            'pageTitle' => 'Report Trash',
            'error' => $error,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('reports/add', $data);
    }

    public function radar() {
        $this->requireLogin();

        $reports = $this->reportModel->getForRadar();

        $data = [
            'pageTitle' => 'Eco-Radar',
            'reports' => $reports,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('reports/radar', $data);
    }

    public function analyze() {
        $this->requireLogin();
        $this->json(['status' => 'ok', 'message' => 'Analysis feature']);
    }
}
