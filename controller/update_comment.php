<?php
require_once '../controller/commentcontroller.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['contenu'])) {
    $controller = new CommentController();
    $controller->updateComment($_POST['id'], $_POST['contenu']);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
