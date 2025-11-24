<?php
// Controller for handling posts and comments, using two tables: post (author, created_at) and comments (contenu, post_id)
require_once __DIR__ . '/../model/config.php';
require_once __DIR__ . '/../model/post.php';
require_once __DIR__ . '/../model/comment.php';

class CommunityController {
    // Add a new post and its comment (message)
    public function addPostWithComment($send_by, $contenu) {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();
            // Insert post (send_by, created_at)
            $now = date('Y-m-d H:i:s');
            $postSql = "INSERT INTO post (send_by, created_at) VALUES (:send_by, :created_at)";
            $postStmt = $db->prepare($postSql);
            $postStmt->execute([
                ':send_by' => $send_by,
                ':created_at' => $now
            ]);
            $postId = $db->lastInsertId();
            // Insert comment (contenu, post_id)
            $commentSql = "INSERT INTO comments (contenu, post_id) VALUES (:contenu, :post_id)";
            $commentStmt = $db->prepare($commentSql);
            $commentStmt->execute([
                ':contenu' => $contenu,
                ':post_id' => $postId
            ]);
            $commentId = $db->lastInsertId();
            $db->commit();
            return ['post_id' => $postId, 'comment_id' => $commentId];
        } catch (Exception $e) {
            try { $db->rollBack(); } catch (Exception $_) {}
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, __DIR__ . '/../log/error.log');
            throw $e;
        }
    }
    // Get all posts with their comments
    public function getAllPostsWithComments() {
        $db = config::getConnexion();
        $sql = 'SELECT p.id as post_id, p.author, p.created_at, c.id as comment_id, c.contenu FROM post p LEFT JOIN comments c ON c.post_id = p.id ORDER BY p.created_at DESC, c.id ASC';
        $query = $db->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        $posts = [];
        foreach ($rows as $row) {
            $pid = $row['post_id'];
            if (!isset($posts[$pid])) {
                $posts[$pid] = [
                    'id' => $pid,
                    'author' => $row['author'],
                    'created_at' => $row['created_at'],
                    'comments' => []
                ];
            }
            if ($row['comment_id']) {
                $posts[$pid]['comments'][] = [
                    'id' => $row['comment_id'],
                    'contenu' => $row['contenu']
                ];
            }
        }
        return array_values($posts);
    }
    // Update a comment
    public function updateComment($id, $contenu) {
        $db = config::getConnexion();
        $sql = "UPDATE comments SET contenu = :contenu WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':contenu' => $contenu
        ]);
        return $stmt->rowCount();
    }
    // Delete a comment
    public function deleteComment($id) {
        $db = config::getConnexion();
        $sql = "DELETE FROM comments WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }
}

// API endpoint logic
header('Content-Type: application/json; charset=utf-8');
$controller = new CommunityController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add post and comment
    if (isset($_POST['send_by']) && isset($_POST['contenu']) && !isset($_POST['id'])) {
        $send_by = $_POST['send_by'] ?: 'Anonyme';
        $contenu = $_POST['contenu'] ?: '';
        if (trim($contenu) !== '') {
            try {
                $result = $controller->addPostWithComment($send_by, $contenu);
                echo json_encode(['success' => true, 'post_id' => $result['post_id'], 'comment_id' => $result['comment_id']]);
            } catch (Exception $e) {
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
            $controller->updateComment($id, $contenu);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
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
            echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
        }
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Requête non valide']);
    exit;
}
// Get all posts with comments
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $posts = $controller->getAllPostsWithComments();
    echo json_encode($posts);
    exit;
}
