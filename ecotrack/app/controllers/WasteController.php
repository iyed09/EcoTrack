<?php
class WasteController extends Controller {
    private $wasteModel;
    private $typeModel;

    public function __construct() {
        parent::__construct();
        $this->wasteModel = new WasteEntry();
        $this->typeModel = new WasteType();
    }

    public function index() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $entries = $this->wasteModel->getByUser($userId);
        $totalImpact = $this->wasteModel->getTotalImpact($userId);

        $totalWeight = 0;
        foreach ($entries as $entry) {
            $totalWeight += $entry['weight_kg'];
        }

        $data = [
            'pageTitle' => 'Waste Management',
            'entries' => $entries,
            'totalImpact' => $totalImpact,
            'totalWeight' => $totalWeight,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('waste/index', $data);
    }

    public function add() {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $error = '';
        $types = $this->typeModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $wasteTypeId = (int)($_POST['waste_type_id'] ?? 0);
            $weightKg = (float)($_POST['weight_kg'] ?? 0);
            $properlyDisposed = isset($_POST['properly_disposed']) ? 1 : 0;
            $date = $_POST['date'] ?? '';
            $notes = $this->sanitize($_POST['notes'] ?? '');

            if ($wasteTypeId <= 0 || $weightKg <= 0 || empty($date)) {
                $error = 'Please fill in all required fields.';
            } else {
                $entryId = $this->wasteModel->create($userId, $wasteTypeId, $weightKg, $properlyDisposed, $date, $notes);
                
                if ($entryId) {
                    $type = $this->typeModel->findById($wasteTypeId);
                    $isRecyclable = $type && $type['recyclable'] && $properlyDisposed;
                    $points = PointsManager::getPointsForAction('waste_entry', $isRecyclable);
                    $description = $isRecyclable ? 'Properly recycled waste' : 'Logged waste entry';
                    
                    $pointsManager = new PointsManager();
                    $pointsManager->awardPoints($userId, $points, 'waste_entry', $description, $entryId, 'waste_entries');
                    
                    $_SESSION['points_earned'] = $points;
                    $_SESSION['points_message'] = $description;
                    $this->redirect('/waste');
                } else {
                    $error = 'Failed to add entry. Please try again.';
                }
            }
        }

        $data = [
            'pageTitle' => 'Add Waste Entry',
            'types' => $types,
            'error' => $error,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('waste/add', $data);
    }

    public function edit($id) {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];

        $entry = $this->wasteModel->getById($id, $userId);
        if (!$entry) {
            $this->redirect('/waste');
        }

        $error = '';
        $types = $this->typeModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $wasteTypeId = (int)($_POST['waste_type_id'] ?? 0);
            $weightKg = (float)($_POST['weight_kg'] ?? 0);
            $properlyDisposed = isset($_POST['properly_disposed']) ? 1 : 0;
            $date = $_POST['date'] ?? '';
            $notes = $this->sanitize($_POST['notes'] ?? '');

            if ($wasteTypeId <= 0 || $weightKg <= 0 || empty($date)) {
                $error = 'Please fill in all required fields.';
            } else {
                $this->wasteModel->updateEntry($id, $wasteTypeId, $weightKg, $properlyDisposed, $date, $notes);
                $this->redirect('/waste');
            }
        }

        $data = [
            'pageTitle' => 'Edit Waste Entry',
            'entry' => $entry,
            'types' => $types,
            'error' => $error,
            'isLoggedIn' => true,
            'isAdmin' => $this->isAdmin()
        ];

        $this->render('waste/edit', $data);
    }

    public function delete($id) {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];
        $this->wasteModel->deleteByUser($id, $userId);
        $this->redirect('/waste');
    }
}
