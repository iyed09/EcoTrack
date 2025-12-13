<?php
class TransportEntry extends Model {
    protected $table = 'transport_entries';

    public function getByUser($userId) {
        $sql = "SELECT te.*, tt.name as transport_name, tt.emission_per_km, tt.icon 
                FROM transport_entries te 
                JOIN transport_types tt ON te.transport_id = tt.id 
                WHERE te.user_id = ? 
                ORDER BY te.date DESC";
        return $this->fetchAll($sql, [$userId]);
    }

    public function getRecentByUser($userId, $limit = 5) {
        $sql = "SELECT te.*, tt.name as transport_name 
                FROM transport_entries te 
                JOIN transport_types tt ON te.transport_id = tt.id 
                WHERE te.user_id = ? 
                ORDER BY te.date DESC 
                LIMIT ?";
        return $this->fetchAll($sql, [$userId, $limit]);
    }

    public function getTotalEmissions($userId) {
        $sql = "SELECT COALESCE(SUM(te.distance_km * tt.emission_per_km), 0) as total 
                FROM transport_entries te 
                JOIN transport_types tt ON te.transport_id = tt.id 
                WHERE te.user_id = ?";
        return $this->fetchColumn($sql, [$userId]);
    }

    public function create($userId, $transportId, $distanceKm, $date, $notes = '') {
        $sql = "INSERT INTO transport_entries (user_id, transport_id, distance_km, date, notes) VALUES (?, ?, ?, ?, ?)";
        return $this->insert($sql, [$userId, $transportId, $distanceKm, $date, $notes]);
    }

    public function updateEntry($id, $transportId, $distanceKm, $date, $notes = '') {
        $sql = "UPDATE transport_entries SET transport_id = ?, distance_km = ?, date = ?, notes = ? WHERE id = ?";
        return $this->update($sql, [$transportId, $distanceKm, $date, $notes, $id]);
    }

    public function deleteByUser($id, $userId) {
        $sql = "DELETE FROM transport_entries WHERE id = ? AND user_id = ?";
        return $this->db->delete($sql, [$id, $userId]);
    }

    public function getById($id, $userId) {
        $sql = "SELECT te.*, tt.name as transport_name 
                FROM transport_entries te 
                JOIN transport_types tt ON te.transport_id = tt.id 
                WHERE te.id = ? AND te.user_id = ?";
        return $this->fetch($sql, [$id, $userId]);
    }

    public function getGlobalTotal() {
        $sql = "SELECT COALESCE(SUM(te.distance_km * tt.emission_per_km), 0) 
                FROM transport_entries te 
                JOIN transport_types tt ON te.transport_id = tt.id";
        return $this->fetchColumn($sql);
    }
}
