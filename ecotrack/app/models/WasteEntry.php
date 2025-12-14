<?php
class WasteEntry extends Model {
    protected $table = 'waste_entries';

    public function getByUser($userId) {
        $sql = "SELECT we.*, wt.name as waste_name, wt.recyclable, wt.impact_score 
                FROM waste_entries we 
                JOIN waste_types wt ON we.waste_type_id = wt.id 
                WHERE we.user_id = ? 
                ORDER BY we.date DESC";
        return $this->fetchAll($sql, [$userId]);
    }

    public function getTotalImpact($userId) {
        $sql = "SELECT COALESCE(SUM(we.weight_kg * wt.impact_score), 0) as total 
                FROM waste_entries we 
                JOIN waste_types wt ON we.waste_type_id = wt.id 
                WHERE we.user_id = ?";
        return $this->fetchColumn($sql, [$userId]);
    }

    public function create($userId, $wasteTypeId, $weightKg, $properlyDisposed, $date, $notes = '') {
        $sql = "INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, properly_disposed, date, notes) VALUES (?, ?, ?, ?, ?, ?)";
        return $this->insert($sql, [$userId, $wasteTypeId, $weightKg, $properlyDisposed, $date, $notes]);
    }

    public function updateEntry($id, $wasteTypeId, $weightKg, $properlyDisposed, $date, $notes = '') {
        $sql = "UPDATE waste_entries SET waste_type_id = ?, weight_kg = ?, properly_disposed = ?, date = ?, notes = ? WHERE id = ?";
        return $this->update($sql, [$wasteTypeId, $weightKg, $properlyDisposed, $date, $notes, $id]);
    }

    public function deleteByUser($id, $userId) {
        $sql = "DELETE FROM waste_entries WHERE id = ? AND user_id = ?";
        return $this->db->delete($sql, [$id, $userId]);
    }

    public function getById($id, $userId) {
        $sql = "SELECT we.*, wt.name as waste_name, wt.recyclable 
                FROM waste_entries we 
                JOIN waste_types wt ON we.waste_type_id = wt.id 
                WHERE we.id = ? AND we.user_id = ?";
        return $this->fetch($sql, [$id, $userId]);
    }
}
