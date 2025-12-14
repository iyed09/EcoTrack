<?php
class User extends Model {
    protected $table = 'users';

    public function findByEmail($email) {
        return $this->findBy('email', $email);
    }

    public function create($name, $email, $password, $role = 'user') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        return $this->insert($sql, [$name, $email, $hashedPassword, $role]);
    }

    public function updateProfile($id, $name, $email) {
        $sql = "UPDATE users SET name = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->update($sql, [$name, $email, $id]);
    }

    public function updatePassword($id, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->update($sql, [$hashedPassword, $id]);
    }

    public function updatePoints($id, $points) {
        $sql = "UPDATE users SET total_points = total_points + ? WHERE id = ?";
        return $this->update($sql, [$points, $id]);
    }

    public function getLeaderboard($limit = 10) {
        $sql = "SELECT id, name, total_points, avatar FROM users ORDER BY total_points DESC LIMIT ?";
        return $this->fetchAll($sql, [$limit]);
    }

    public function getUserRank($userId) {
        $sql = "SELECT COUNT(*) + 1 as rank FROM users WHERE total_points > (SELECT total_points FROM users WHERE id = ?)";
        return $this->fetchColumn($sql, [$userId]);
    }

    public function emailExists($email, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            return $this->fetch($sql, [$email, $excludeId]) !== false;
        }
        $sql = "SELECT id FROM users WHERE email = ?";
        return $this->fetch($sql, [$email]) !== false;
    }
}
