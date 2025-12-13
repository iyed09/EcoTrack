<?php
class EnergySource extends Model {
    protected $table = 'energy_sources';

    public function getAll() {
        return $this->fetchAll("SELECT * FROM energy_sources ORDER BY name");
    }
}
