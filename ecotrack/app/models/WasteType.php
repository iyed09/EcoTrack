<?php
class WasteType extends Model {
    protected $table = 'waste_types';

    public function getAll() {
        return $this->fetchAll("SELECT * FROM waste_types ORDER BY name");
    }
}
