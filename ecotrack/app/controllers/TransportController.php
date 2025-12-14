<?php
class TransportController extends Controller {
    private $transportModel;
    private $typeModel;

    public function __construct() {
        parent::__construct();
        $this->transportModel = new TransportEntry();
        $this->typeModel = new TransportType();
    }

    public function index() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $entries = $this->transportModel->getByUser($userId);
        $totalEmissions = $this->transportModel->getTotalEmissions($userId);

        $totalDistance = 0;
        foreach ($entries as $entry) {
            $totalDistance += $entry['distance_km'];
        }

        $data = [
            'pageTitle' => 'Transport Tracking',
            'entries' => $entries,
            'totalEmissions' => $totalEmissions,
            'totalDistance' => $totalDistance,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('transport/index', $data);
    }

    public function add() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $error = '';
        $types = $this->typeModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transportId = (int)($_POST['transport_id'] ?? 0);
            $distanceKm = (float)($_POST['distance_km'] ?? 0);
            $date = $_POST['date'] ?? '';
            $notes = $this->sanitize($_POST['notes'] ?? '');

            if ($transportId <= 0 || $distanceKm <= 0 || empty($date)) {
                $error = 'Please fill in all required fields.';
            } else {
                $entryId = $this->transportModel->create($userId, $transportId, $distanceKm, $date, $notes);
                
                if ($entryId) {
                    $type = $this->typeModel->findById($transportId);
                    $isEco = $type && ($type['emission_per_km'] == 0 || stripos($type['name'], 'Electric') !== false);
                    $points = PointsManager::getPointsForAction('transport_entry', $isEco);
                    $description = $isEco ? 'Used eco-friendly transport' : 'Logged transport entry';
                    
                    $pointsManager = new PointsManager();
                    $pointsManager->awardPoints($userId, $points, 'transport_entry', $description, $entryId, 'transport_entries');
                    
                    $_SESSION['points_earned'] = $points;
                    $_SESSION['points_message'] = $description;
                    $this->redirect('/transport');
                } else {
                    $error = 'Failed to add entry. Please try again.';
                }
            }
        }

        $data = [
            'pageTitle' => 'Add Transport Entry',
            'types' => $types,
            'error' => $error,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('transport/add', $data);
    }

    public function edit($id) {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $entry = $this->transportModel->getById($id, $userId);
        if (!$entry) {
            $this->redirect('/transport');
        }

        $error = '';
        $types = $this->typeModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transportId = (int)($_POST['transport_id'] ?? 0);
            $distanceKm = (float)($_POST['distance_km'] ?? 0);
            $date = $_POST['date'] ?? '';
            $notes = $this->sanitize($_POST['notes'] ?? '');

            if ($transportId <= 0 || $distanceKm <= 0 || empty($date)) {
                $error = 'Please fill in all required fields.';
            } else {
                $this->transportModel->updateEntry($id, $transportId, $distanceKm, $date, $notes);
                $this->redirect('/transport');
            }
        }

        $data = [
            'pageTitle' => 'Edit Transport Entry',
            'entry' => $entry,
            'types' => $types,
            'error' => $error,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('transport/edit', $data);
    }

    public function delete($id) {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];
        $this->transportModel->deleteByUser($id, $userId);
        $this->redirect('/transport');
    }
}
