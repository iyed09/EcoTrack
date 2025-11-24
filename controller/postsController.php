<?php
require_once __DIR__ . '/../model/config.php';
require_once __DIR__ . '/../model/post.php';
require_once __DIR__ . '/../model/comment.php';

class PostsController {
    public function getAllPostsWithComments() {
        $db = config::getConnexion();
        $sql = 'SELECT p.ID as post_id, p.send_by, p.time as post_time, c.id as comment_id, c.contenu, c.`comment id` as comment_post_id
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
                    'comments' => []
                ];
            }
            if ($row['comment_id']) {
                $posts[$pid]['comments'][] = [
                    'id' => $row['comment_id'],
                    'contenu' => $row['contenu'],
                    'comment_post_id' => $row['comment_post_id']
                ];
            }
        }
        return array_values($posts);
    }
    public function addPost($post) {
        $db = config::getConnexion();
        $sql = 'INSERT INTO post (send_by, time) VALUES (:send_by, :time)';
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':send_by' => $post->getSendBy(),
            ':time' => $post->getTime()
        ]);
        return $db->lastInsertId();
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
