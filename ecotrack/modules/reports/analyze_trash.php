<?php
require_once '../../includes/config.php';
require_once '../../includes/TrashAnalyzer.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'] ?? '';

    if (empty($description)) {
        echo json_encode(['error' => 'No description provided']);
        exit;
    }

    // Simulate "Processing" Delay for AI effect
    sleep(1);

    $analyzer = new TrashAnalyzer();
    $result = $analyzer->analyze($description);

    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
