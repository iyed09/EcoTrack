<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$query = isset($_GET['q']) ? sanitize($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$searchTerm = '%' . $query . '%';
$results = [];

$stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE name LIKE ? OR email LIKE ? LIMIT 5");
$stmt->execute([$searchTerm, $searchTerm]);
$users = $stmt->fetchAll();

foreach ($users as $user) {
    $results[] = [
        'type' => 'user',
        'icon' => 'bx-user',
        'title' => $user['name'],
        'subtitle' => $user['email'] . ' (' . ucfirst($user['role']) . ')',
        'url' => 'users.php?highlight=' . $user['id']
    ];
}

$stmt = $pdo->prepare("
    SELECT tr.id, tr.location_description, tr.status, u.name as reporter_name 
    FROM trash_reports tr 
    LEFT JOIN users u ON tr.reporter_id = u.id 
    WHERE tr.location_description LIKE ? OR tr.description LIKE ? OR u.name LIKE ?
    LIMIT 5
");
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
$reports = $stmt->fetchAll();

foreach ($reports as $report) {
    $results[] = [
        'type' => 'report',
        'icon' => 'bx-flag',
        'title' => 'Report #' . $report['id'],
        'subtitle' => substr($report['location_description'], 0, 50) . '... (' . ucfirst($report['status']) . ')',
        'url' => 'reports.php?id=' . $report['id']
    ];
}

$pages = [
    ['title' => 'Dashboard', 'url' => 'index.php', 'keywords' => ['dashboard', 'home', 'overview']],
    ['title' => 'Users Management', 'url' => 'users.php', 'keywords' => ['users', 'manage', 'accounts']],
    ['title' => 'Reports', 'url' => 'reports.php', 'keywords' => ['reports', 'trash', 'complaints']],
    ['title' => 'Statistics', 'url' => 'statistics.php', 'keywords' => ['statistics', 'stats', 'charts', 'analytics']],
    ['title' => 'Posts', 'url' => 'posts.php', 'keywords' => ['posts', 'blog', 'news']],
    ['title' => 'Score', 'url' => 'score.php', 'keywords' => ['score', 'points', 'leaderboard']],
    ['title' => 'Gestion Produits & Déchets', 'url' => 'products-waste.php', 'keywords' => ['products', 'waste', 'gestion', 'déchets']]
];

foreach ($pages as $page) {
    $matches = false;
    if (stripos($page['title'], $query) !== false) {
        $matches = true;
    } else {
        foreach ($page['keywords'] as $keyword) {
            if (stripos($keyword, $query) !== false) {
                $matches = true;
                break;
            }
        }
    }
    
    if ($matches) {
        $results[] = [
            'type' => 'page',
            'icon' => 'bx-file',
            'title' => $page['title'],
            'subtitle' => 'Go to page',
            'url' => $page['url']
        ];
    }
}

echo json_encode(['results' => $results]);
