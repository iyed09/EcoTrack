<?php
require_once __DIR__ . '/../model/config.php';
require_once __DIR__ . '/../model/post.php';
require_once __DIR__ . '/../model/comment.php';

class PostsController {
    public function getAllPostsWithComments() {
        $db = config::getConnexion();
        // Ensure post.contenu and comments.send_by exist to avoid SQL errors on older schemas
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
        $sql = 'SELECT p.ID as post_id, p.send_by, p.time as post_time, p.contenu as post_contenu, c.id as comment_id, c.send_by as comment_send_by, c.contenu as comment_contenu, c.`comment id` as comment_post_id
            FROM post p
            LEFT JOIN comments c ON c.`comment id` = p.ID
            ORDER BY p.time DESC, c.id ASC';
        $query = $db->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();
        $posts = [];
        foreach ($rows as $row) {
            $pid = $row['post_id'];
            if (!isset($posts[$pid])) {
                $posts[$pid] = [
                    'id' => $pid,
                    'send_by' => $row['send_by'],
                    'time' => $row['post_time'],
                    'contenu' => $row['post_contenu'],
                    'comments' => []
                ];
            }
            if ($row['comment_id']) {
                $posts[$pid]['comments'][] = [
                    'id' => $row['comment_id'],
                    'send_by' => $row['comment_send_by'] ?: 'Anonyme',
                    'contenu' => $row['comment_contenu'],
                    'comment_post_id' => $row['comment_post_id']
                ];
            }
        }
        return array_values($posts);
    }
    public function addPost($post) {
        // Delegate insert to model helper for consistency
        $sendBy = method_exists($post, 'getSendBy') ? $post->getSendBy() : ($post->getAuthor() ?? 'Anonyme');
        $time = method_exists($post, 'getTime') ? $post->getTime() : ($post->getCreatedAt() ?? date('Y-m-d H:i:s'));
        $contenu = method_exists($post, 'getContent') ? $post->getContent() : '';
        try {
            $id = \PostCRUD::addPost($sendBy, $contenu);
            return $id;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function addComment($comment) {
        $db = config::getConnexion();
        $sql = 'INSERT INTO comments (`comment id`, contenu) VALUES (:comment_id, :contenu)';
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':comment_id' => $comment->getCommentId(),
            ':contenu' => $comment->getContenu()
        ]);
        return $db->lastInsertId();
    }
    public function updateComment($comment) {
        $db = config::getConnexion();
        $sql = 'UPDATE comments SET contenu=:contenu WHERE id=:id';
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':id' => $comment->getId(),
            ':contenu' => $comment->getContenu()
        ]);
    }
    public function deleteComment($id) {
        $db = config::getConnexion();
        $sql = 'DELETE FROM comments WHERE id=:id';
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
