<?php
// Controller for handling posts and comments, using two tables: post (author, created_at) and comments (contenu, post_id)
require_once __DIR__ . '/../model/config.php';
require_once __DIR__ . '/../model/post.php';
require_once __DIR__ . '/../model/comment.php';

class CommunityController {
    // Add a new post and its comment (message)
    public function addPost($send_by, $contenu) {
        $db = config::getConnexion();
        try {
            // Use the PostCRUD helper (model) to ensure table and insert
            // PostCRUD::createTable() already called at model include
            $postId = \PostCRUD::addPost($send_by, $contenu);
            // Automatic comment creation removed as per user request
            error_log(date('[Y-m-d H:i:s] ') . "New post insert ok: id=" . $postId . "\n", 3, __DIR__ . '/../log/error.log');
            return ['post_id' => $postId];
        } catch (Exception $e) {
            try { $db->rollBack(); } catch (Exception $_) {}
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, __DIR__ . '/../log/error.log');
            throw $e;
        }
    }
    // Get all posts with their comments
    public function getAllPostsWithComments() {
        $db = config::getConnexion();
        // Ensure migrations: add post.contenu if missing and comments.send_by if missing
        try {
            $col = $db->query("SHOW COLUMNS FROM post LIKE 'contenu'")->fetch();
            if (!$col) {
                $db->exec("ALTER TABLE post ADD COLUMN contenu TEXT NULL AFTER time");
            }
        } catch (Exception $_) {}
        try {
            $col = $db->query("SHOW COLUMNS FROM comments LIKE 'send_by'")->fetch();
            if (!$col) {
                $db->exec("ALTER TABLE comments ADD COLUMN send_by VARCHAR(100) DEFAULT 'Anonyme' AFTER id");
            }
            $col = $db->query("SHOW COLUMNS FROM comments LIKE 'time'")->fetch();
            if (!$col) {
                $db->exec("ALTER TABLE comments ADD COLUMN time DATETIME NULL AFTER contenu");
                $db->exec("UPDATE comments SET time = NOW() WHERE time IS NULL");
            }
            $col = $db->query("SHOW COLUMNS FROM comments LIKE 'comment id'")->fetch();
            if (!$col) {
                $db->exec("ALTER TABLE comments ADD COLUMN `comment id` INT NULL AFTER time");
            }
        } catch (Exception $_) {}
        // Use actual DB columns: p.ID, p.send_by, p.time, p.contenu (post text), c.id, c.contenu and c.`comment id`
        $sql = 'SELECT p.ID as post_id, p.send_by, p.time as post_time, p.contenu as post_contenu, c.id as comment_id, c.send_by as comment_send_by, c.contenu as comment_contenu, c.`comment id` as comment_post_id FROM post p LEFT JOIN comments c ON c.`comment id` = p.ID ORDER BY p.time DESC, c.id ASC';
        $query = $db->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        error_log(date('[Y-m-d H:i:s] ') . "Fetched posts rows: " . count($rows) . "\n", 3, __DIR__ . '/../log/error.log');
        $posts = [];
        foreach ($rows as $row) {
            $pid = $row['post_id'];
            if (!isset($posts[$pid])) {
                $posts[$pid] = [
                    'id' => $pid,
                    'send_by' => $row['send_by'],
                    'time' => $row['post_time'],
                    'contenu' => $row['post_contenu'] ?? '',
                    'comments' => []
                ];
            }
            if ($row['comment_id']) {
                $posts[$pid]['comments'][] = [
                    'id' => $row['comment_id'],
                    'send_by' => $row['comment_send_by'] ?: 'Anonyme',
                    'contenu' => $row['comment_contenu']
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

    // Add a comment to an existing post
    public function addCommentToPost($postId, $contenu, $send_by = 'Anonyme') {
        $db = config::getConnexion();
        $sql = "INSERT INTO comments (send_by, contenu, time, `comment id`) VALUES (:send_by, :contenu, :time, :post_id)";
        $stmt = $db->prepare($sql);
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            ':send_by' => $send_by,
            ':contenu' => $contenu,
            ':time' => $now,
            ':post_id' => $postId
        ]);
        return $db->lastInsertId();
    }

    // Delete a post and its comments
    public function deletePost($postId) {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();
            $stmt1 = $db->prepare("DELETE FROM comments WHERE `comment id` = :post_id");
            $stmt1->execute([':post_id' => $postId]);
            $stmt2 = $db->prepare("DELETE FROM post WHERE ID = :post_id");
            $stmt2->execute([':post_id' => $postId]);
            $db->commit();
            return $stmt2->rowCount();
        } catch (Exception $e) {
            try { $db->rollBack(); } catch (Exception $_) {}
            throw $e;
        }
    }

    // Update post: update post send_by and ensure the main comment exists (update it) with provided content
    public function updatePost($postId, $contenu, $send_by) {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();
            // Update send_by and post content on post
            $stmt = $db->prepare('UPDATE post SET send_by = :send_by, contenu = :contenu WHERE ID = :post_id');
            $stmt->execute([':send_by' => $send_by, ':contenu' => $contenu, ':post_id' => $postId]);
            $db->commit();
            return true;
        } catch (Exception $e) {
            try { $db->rollBack(); } catch (Exception $_) {}
            throw $e;
        }
    }
}

// API endpoint logic
header('Content-Type: application/json; charset=utf-8');
$controller = new CommunityController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add post and comment (new post)
    // Ensure we don't accidentally match update requests that send a 'post_id'
    if (isset($_POST['send_by']) && isset($_POST['contenu']) && !isset($_POST['id']) && !isset($_POST['parent_id']) && !isset($_POST['post_id'])) {
        $send_by = $_POST['send_by'] ?: 'Anonyme';
        $contenu = $_POST['contenu'] ?: '';
        error_log(date('[Y-m-d H:i:s] ') . "New post create request: send_by=" . $send_by . ", contenu=" . substr($contenu, 0, 200) . "\n", 3, __DIR__ . '/../log/error.log');
        if (trim($contenu) !== '') {
            try {
                $result = $controller->addPost($send_by, $contenu);
                echo json_encode(['success' => true, 'post_id' => $result['post_id']]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Contenu vide']);
        }
        exit;
    }
    // Add a comment to an existing post
    if (isset($_POST['parent_id']) && isset($_POST['contenu']) && !isset($_POST['id'])) {
        try {
            $send_by = isset($_POST['send_by']) ? $_POST['send_by'] : 'Anonyme';
            error_log(date('[Y-m-d H:i:s] ') . "Add comment request parent_id=" . $_POST['parent_id'] . ", send_by=" . substr($send_by,0,200) . ", contenu=" . substr($_POST['contenu'],0,200) . "\n", 3, __DIR__ . '/../log/error.log');
            $parentId = $_POST['parent_id'];
            $contenu = $_POST['contenu'];
            $send_by = isset($_POST['send_by']) ? $_POST['send_by'] : 'Anonyme';
            $resultId = $controller->addCommentToPost($parentId, $contenu, $send_by);
            echo json_encode(['success' => true, 'comment_id' => $resultId]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
        }
        exit;
    }

    // Update post (update send_by and the main comment). Checks post_id + contenu + send_by
    if (isset($_POST['post_id']) && isset($_POST['contenu']) && isset($_POST['send_by'])) {
        try {
            error_log(date('[Y-m-d H:i:s] ') . "Update post request post_id=" . $_POST['post_id'] . ", send_by=" . substr($_POST['send_by'],0,200) . ", contenu=" . substr($_POST['contenu'],0,200) . "\n", 3, __DIR__ . '/../log/error.log');
            $postId = $_POST['post_id'];
            $contenu = $_POST['contenu'];
            $send_by = $_POST['send_by'];
            $controller->updatePost($postId, $contenu, $send_by);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
        }
        exit;
    }

    // Update comment
    if (isset($_POST['id']) && isset($_POST['contenu'])) {
        error_log(date('[Y-m-d H:i:s] ') . "Update comment request id=" . $_POST['id'] . ", contenu=" . substr($_POST['contenu'],0,200) . "\n", 3, __DIR__ . '/../log/error.log');
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
        error_log(date('[Y-m-d H:i:s] ') . "Delete comment request id=" . $_POST['id'] . "\n", 3, __DIR__ . '/../log/error.log');
        try {
            $controller->deleteComment($_POST['id']);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
        }
        exit;
    }
    // Delete post (post_id provided) - takes precedence over delete comment
    if (isset($_POST['post_id']) && !isset($_POST['contenu'])) {
        try {
            $controller->deletePost($_POST['post_id']);
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
    try {
        $posts = $controller->getAllPostsWithComments();
        echo json_encode($posts);
    } catch (Exception $e) {
        error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n", 3, __DIR__ . '/../log/error.log');
        // Return an empty array instead of a server error page to keep frontend stable
        echo json_encode([]);
    }
    exit;
}
