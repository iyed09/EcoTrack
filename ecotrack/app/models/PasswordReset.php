<?php
class PasswordReset extends Model {
    protected $table = 'password_resets';

    public function createReset($email, $code, $expiresAt) {
        $this->deleteByEmail($email);
        $sql = "INSERT INTO password_resets (email, code, expires_at) VALUES (?, ?, ?)";
        return $this->insert($sql, [$email, $code, $expiresAt]);
    }

    public function findValidCode($email, $code) {
        $sql = "SELECT * FROM password_resets WHERE email = ? AND code = ? AND expires_at > NOW()";
        return $this->fetch($sql, [$email, $code]);
    }

    public function deleteByEmail($email) {
        $sql = "DELETE FROM password_resets WHERE email = ?";
        return $this->db->delete($sql, [$email]);
    }
}
