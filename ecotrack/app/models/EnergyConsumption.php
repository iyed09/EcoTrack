<?php
class EnergyConsumption extends Model {
    protected $table = 'energy_consumption';

    public function getByUser($userId) {
        $sql = "SELECT ec.*, es.name as source_name, es.unit, es.emission_factor 
                FROM energy_consumption ec 
                JOIN energy_sources es ON ec.source_id = es.id 
                WHERE ec.user_id = ? 
                ORDER BY ec.date DESC";
        return $this->fetchAll($sql, [$userId]);
    }

    public function getRecentByUser($userId, $limit = 5) {
        $sql = "SELECT ec.*, es.name as source_name, es.unit 
                FROM energy_consumption ec 
                JOIN energy_sources es ON ec.source_id = es.id 
                WHERE ec.user_id = ? 
                ORDER BY ec.date DESC 
                LIMIT ?";
        return $this->fetchAll($sql, [$userId, $limit]);
    }

    public function getTotalEmissions($userId) {
        $sql = "SELECT COALESCE(SUM(ec.amount * es.emission_factor), 0) as total 
                FROM energy_consumption ec 
                JOIN energy_sources es ON ec.source_id = es.id 
                WHERE ec.user_id = ?";
        return $this->fetchColumn($sql, [$userId]);
    }

    public function create($userId, $sourceId, $amount, $date, $notes = '') {
        $sql = "INSERT INTO energy_consumption (user_id, source_id, amount, date, notes) VALUES (?, ?, ?, ?, ?)";
        return $this->insert($sql, [$userId, $sourceId, $amount, $date, $notes]);
    }

    public function updateEntry($id, $sourceId, $amount, $date, $notes = '') {
        $sql = "UPDATE energy_consumption SET source_id = ?, amount = ?, date = ?, notes = ? WHERE id = ?";
        return $this->update($sql, [$sourceId, $amount, $date, $notes, $id]);
    }

    public function deleteByUser($id, $userId) {
        $sql = "DELETE FROM energy_consumption WHERE id = ? AND user_id = ?";
        return $this->db->delete($sql, [$id, $userId]);
    }

    public function getById($id, $userId) {
        $sql = "SELECT ec.*, es.name as source_name, es.unit 
                FROM energy_consumption ec 
                JOIN energy_sources es ON ec.source_id = es.id 
                WHERE ec.id = ? AND ec.user_id = ?";
        return $this->fetch($sql, [$id, $userId]);
    }

    public function getGlobalTotal() {
        $sql = "SELECT COALESCE(SUM(ec.amount * es.emission_factor), 0) 
                FROM energy_consumption ec 
                JOIN energy_sources es ON ec.source_id = es.id";
        return $this->fetchColumn($sql);
    }
}
