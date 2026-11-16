<?php
// Este modelo solo representa los datos de una transmisión.
class Transmision {
    public int $id = 0;
    public int $iglesia_id = 0;
    public string $titulo = '';
    public string $fecha_hora = '';
    public string $estado = 'programada';
    public string $iglesia = '';
    public ?string $youtube = null;
    public ?string $facebook = null;
    public ?string $tiktok = null;
    public ?string $youtube_id = null;
}
