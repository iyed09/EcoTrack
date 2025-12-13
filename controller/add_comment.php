<?php
require_once '../controller/communityController.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $send_by = isset($_POST['send_by']) ? $_POST['send_by'] : 'Anonyme';
    $contenu = isset($_POST['contenu']) ? $_POST['contenu'] : '';
    $post_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : null;
    if (trim($contenu) !== '') {
        $controller = new CommunityController();
        $controller->addCommentToPost($post_id ?? null, $contenu, $send_by);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Contenu vide']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}
?>
