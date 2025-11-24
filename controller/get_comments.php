<?php
require_once '../controller/commentcontroller.php';
header('Content-Type: application/json');

$controller = new CommentController();
$comments = $controller->getAllComments();
echo json_encode($comments);
?>
