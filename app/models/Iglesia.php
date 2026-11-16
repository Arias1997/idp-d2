<?php
// Solo representa los datos de una iglesia. No contiene consultas.
class Iglesia {
    public int $id = 0;
    public int $zona_id = 0;
    public string $zona = '';
    public string $nombre = '';
    public string $direccion = '';
    public ?string $latitud = null;
    public ?string $longitud = null;
    public ?string $pastor = null;
    public ?string $telefono = null;
    public ?string $horarios = null;
    public bool $activa = true;
    public static function desdeArray(array $datos): self {
        $i = new self();
        $i->zona_id   = (int) $datos['zona_id'];
        $i->nombre    = $datos['nombre'];
        $i->direccion = $datos['direccion'];
        $i->latitud   = $datos['latitud'] !== '' ? $datos['latitud'] : null;
        $i->longitud  = $datos['longitud'] !== '' ? $datos['longitud'] : null;
        $i->pastor    = $datos['pastor'] !== '' ? $datos['pastor'] : null;
        $i->telefono  = $datos['telefono'] !== '' ? $datos['telefono'] : null;
        $i->horarios  = $datos['horarios'] !== '' ? $datos['horarios'] : null;
        $i->activa    = $datos['activa'] === '1';
        return $i;
    }
}
