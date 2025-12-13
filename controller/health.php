<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../model/config.php';
try {
    $db = config::getConnexion();
    $posts = $db->query('SELECT COUNT(*) as c FROM post')->fetch();
    $comments = $db->query('SELECT COUNT(*) as c FROM comments')->fetch();
    echo json_encode(['ok' => true, 'posts' => (int)($posts['c'] ?? 0), 'comments' => (int)($comments['c'] ?? 0)]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
