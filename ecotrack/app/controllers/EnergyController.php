<?php
class EnergyController extends Controller {
    private $energyModel;
    private $sourceModel;

    public function __construct() {
        parent::__construct();
        $this->energyModel = new EnergyConsumption();
        $this->sourceModel = new EnergySource();
    }

    public function index() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $entries = $this->energyModel->getByUser($userId);
        $totalEmissions = $this->energyModel->getTotalEmissions($userId);

        $data = [
            'pageTitle' => 'Energy Tracking',
            'entries' => $entries,
            'totalEmissions' => $totalEmissions,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('energy/index', $data);
    }

    public function add() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $error = '';
        $sources = $this->sourceModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sourceId = (int)($_POST['source_id'] ?? 0);
            $amount = (float)($_POST['amount'] ?? 0);
            $date = $_POST['date'] ?? '';
            $notes = $this->sanitize($_POST['notes'] ?? '');

            if ($sourceId <= 0 || $amount <= 0 || empty($date)) {
                $error = 'Please fill in all required fields.';
            } else {
                $entryId = $this->energyModel->create($userId, $sourceId, $amount, $date, $notes);
                
                if ($entryId) {
                    $source = $this->sourceModel->findById($sourceId);
                    $isRenewable = $source && (stripos($source['name'], 'Solar') !== false || stripos($source['name'], 'Wind') !== false);
                    $points = PointsManager::getPointsForAction('energy_entry', $isRenewable);
                    $description = $isRenewable ? 'Used renewable energy' : 'Logged energy consumption';
                    
                    $pointsManager = new PointsManager();
                    $pointsManager->awardPoints($userId, $points, 'energy_entry', $description, $entryId, 'energy_consumption');
                    
                    $_SESSION['points_earned'] = $points;
                    $_SESSION['points_message'] = $description;
                    $this->redirect('/energy');
                } else {
                    $error = 'Failed to add entry. Please try again.';
                }
            }
        }

        $data = [
            'pageTitle' => 'Add Energy Entry',
            'sources' => $sources,
            'error' => $error,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('energy/add', $data);
    }

    public function edit($id) {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $entry = $this->energyModel->getById($id, $userId);
        if (!$entry) {
            $this->redirect('/energy');
        }

        $error = '';
        $sources = $this->sourceModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sourceId = (int)($_POST['source_id'] ?? 0);
            $amount = (float)($_POST['amount'] ?? 0);
            $date = $_POST['date'] ?? '';
            $notes = $this->sanitize($_POST['notes'] ?? '');

            if ($sourceId <= 0 || $amount <= 0 || empty($date)) {
                $error = 'Please fill in all required fields.';
            } else {
                $this->energyModel->updateEntry($id, $sourceId, $amount, $date, $notes);
                $this->redirect('/energy');
            }
        }

        $data = [
            'pageTitle' => 'Edit Energy Entry',
            'entry' => $entry,
            'sources' => $sources,
            'error' => $error,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('energy/edit', $data);
    }

    public function delete($id) {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];
        $this->energyModel->deleteByUser($id, $userId);
        $this->redirect('/energy');
    }
}
