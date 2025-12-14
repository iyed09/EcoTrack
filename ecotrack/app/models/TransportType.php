<?php
class TransportType extends Model {
    protected $table = 'transport_types';

    public function getAll() {
        return $this->fetchAll("SELECT * FROM transport_types ORDER BY name");
    }
}
