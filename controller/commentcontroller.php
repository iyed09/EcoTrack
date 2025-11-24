<?php
// Configure error handling: disable display to keep API JSON clean
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Buffer and discard any accidental output from includes so we always return clean JSON
ob_start();
require_once __DIR__ . '/../model/comment.php';
ob_end_clean();

// Ensure comments table exists; if creation fails return JSON error
try {
    CommentCRUD::createTable();
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'DB init error: ' . $e->getMessage()]);
    exit;
}

require_once __DIR__ . '/../model/post.php';

class CommentController {
    // Add both post and comment, link comment to post
    public function addPostAndComment($send_by, $contenu) {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();
            // Insert post
            $postSql = "INSERT INTO post (send_by, time) VALUES (:send_by, :time)";
            $postStmt = $db->prepare($postSql);
            $now = date('Y-m-d H:i:s');
            $postStmt->execute([
                ':send_by' => $send_by,
                ':time' => $now
            ]);
            $postId = $db->lastInsertId();
            // Insert comment linked to post
            $commentSql = "INSERT INTO comments (`comment id`, contenu) VALUES (:comment_id, :contenu)";
            $commentStmt = $db->prepare($commentSql);
            $commentStmt->execute([
                ':comment_id' => $postId,
                ':contenu' => $contenu
            ]);
            $commentId = $db->lastInsertId();
            $db->commit();
            return ['post_id' => $postId, 'comment_id' => $commentId];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    public function getAllComments() {
        return CommentCRUD::getAllComments();
    }
    public function updateComment($id, $contenu, $author = null) {
        CommentCRUD::updateComment($id, $contenu, $author);
    }
    public function deleteComment($id) {
        CommentCRUD::deleteComment($id);
    }
}

// API endpoint logic
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CommentController();
    // Add post and comment
    if (isset($_POST['send_by']) && isset($_POST['contenu']) && !isset($_POST['id'])) {
        $send_by = $_POST['send_by'] ?: 'Anonyme';
        $contenu = $_POST['contenu'] ?: '';
        if (trim($contenu) !== '') {
            try {
                $result = $controller->addPostAndComment($send_by, $contenu);
                echo json_encode(['success' => true, 'post_id' => $result['post_id'], 'comment_id' => $result['comment_id']]);
            } catch (Exception $e) {
                error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, __DIR__ . '/../log/error.log');
                echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Contenu vide']);
        }
        exit;
    }
    // Update comment
    if (isset($_POST['id']) && isset($_POST['contenu'])) {
        try {
            $id = $_POST['id'];
            $contenu = $_POST['contenu'];
            $send_by = isset($_POST['send_by']) ? $_POST['send_by'] : null;
            if ($send_by !== null) {
                CommentCRUD::updateComment($id, $contenu, $send_by);
            } else {
                CommentCRUD::updateComment($id, $contenu);
            }
            echo json_encode(['success' => true]);
            } catch (Exception $e) {
                error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, __DIR__ . '/../log/error.log');
                echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
            }
        exit;
    }
    // Delete comment
    if (isset($_POST['id']) && !isset($_POST['contenu'])) {
        try {
            $controller->deleteComment($_POST['id']);
            echo json_encode(['success' => true]);
            } catch (Exception $e) {
                error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, __DIR__ . '/../log/error.log');
                echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
            }
        exit;
    }
    // POST data debugging removed to avoid writing to a removed/unknown log file
    echo json_encode(['success' => false, 'error' => 'Requête non valide']);
    exit;
}
// Get all comments
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new CommentController();
    $comments = $controller->getAllComments();
    echo json_encode($comments);
    exit;
}