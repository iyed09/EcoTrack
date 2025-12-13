<?php
class Model {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll($orderBy = 'id DESC') {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql);
    }

    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function findBy($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return $this->db->fetch($sql, [$value]);
    }

    public function findAllBy($column, $value, $orderBy = 'id DESC') {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ? ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql, [$value]);
    }

    public function count($where = null, $params = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        return $this->db->fetchColumn($sql, $params);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->delete($sql, [$id]);
    }

    public function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }

    public function fetchAll($sql, $params = []) {
        return $this->db->fetchAll($sql, $params);
    }

    public function fetch($sql, $params = []) {
        return $this->db->fetch($sql, $params);
    }

    public function fetchColumn($sql, $params = []) {
        return $this->db->fetchColumn($sql, $params);
    }

    public function insert($sql, $params = []) {
        return $this->db->insert($sql, $params);
    }

    public function update($sql, $params = []) {
        return $this->db->update($sql, $params);
    }
}
