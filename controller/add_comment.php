<?php
require_once '../controller/commentcontroller.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $send_by = isset($_POST['send_by']) ? $_POST['send_by'] : 'Anonyme';
    $contenu = isset($_POST['contenu']) ? $_POST['contenu'] : '';
    if (trim($contenu) !== '') {
        $controller = new CommentController();
        $controller->addComment($send_by, $contenu);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Contenu vide']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}
?>
