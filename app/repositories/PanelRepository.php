<?php
require_once __DIR__.'/../validator/TransmisionValidator.php';

// Consultas de solo lectura compartidas por el panel y el reporte PDF.
class PanelRepository {
    public function __construct(private PDO $db) {}

    public function datosResumen(): array {
        $enVivo = $this->transmisionesPorEstado('en_vivo');
        foreach ($enVivo as &$transmision) {
            $transmision['youtube_id'] = TransmisionValidator::videoId($transmision['youtube'] ?? '');
        }
        unset($transmision);

        $q = $this->db->prepare("SELECT t.titulo, t.fecha_hora, i.nombre AS iglesia
            FROM transmisiones t JOIN iglesias i ON i.id = t.iglesia_id
            WHERE t.estado = 'programada' AND t.fecha_hora >= ?
            ORDER BY t.fecha_hora ASC, t.id ASC LIMIT 5");
        $q->execute([date('Y-m-d H:i:s')]);

        return [
            'totales' => $this->totales(),
            'enVivo' => $enVivo,
            'proximas' => $q->fetchAll(),
            'incompletas' => $this->db->query("SELECT id, nombre, latitud, longitud, horarios
                FROM iglesias WHERE latitud IS NULL OR longitud IS NULL
                    OR horarios IS NULL OR TRIM(horarios) = ''
                ORDER BY nombre")->fetchAll(),
        ];
    }

    public function datosReporte(): array {
        return [
            'totales' => $this->totales(),
            'porZona' => $this->db->query('SELECT z.nombre AS zona, COUNT(i.id) AS iglesias
                FROM zonas z LEFT JOIN iglesias i ON i.zona_id = z.id
                GROUP BY z.id, z.nombre ORDER BY z.nombre')->fetchAll(),
            'iglesias' => $this->db->query('SELECT i.nombre, z.nombre AS zona, i.pastor, i.telefono, i.activa
                FROM iglesias i JOIN zonas z ON z.id = i.zona_id
                ORDER BY z.nombre, i.nombre')->fetchAll(),
            'transmisiones' => $this->db->query('SELECT t.titulo, i.nombre AS iglesia, t.fecha_hora, t.estado
                FROM transmisiones t JOIN iglesias i ON i.id = t.iglesia_id
                ORDER BY t.fecha_hora DESC, t.id DESC LIMIT 15')->fetchAll(),
        ];
    }

    private function totales(): array {
        $transmisiones = ['en_vivo' => 0, 'programada' => 0, 'finalizada' => 0];
        foreach ($this->db->query('SELECT estado, COUNT(*) AS cantidad FROM transmisiones GROUP BY estado') as $fila) {
            if (array_key_exists($fila['estado'], $transmisiones)) {
                $transmisiones[$fila['estado']] = (int) $fila['cantidad'];
            }
        }
        $iglesias = $this->db->query('SELECT
            SUM(CASE WHEN activa THEN 1 ELSE 0 END) AS activas,
            SUM(CASE WHEN activa THEN 0 ELSE 1 END) AS inactivas FROM iglesias')->fetch();
        return [
            'transmisiones' => $transmisiones,
            'iglesias_activas' => (int) $iglesias['activas'],
            'iglesias_inactivas' => (int) $iglesias['inactivas'],
            'zonas' => (int) $this->db->query('SELECT COUNT(*) FROM zonas')->fetchColumn(),
            'usuarios' => (int) $this->db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),
        ];
    }

    private function transmisionesPorEstado(string $estado): array {
        $q = $this->db->prepare("SELECT t.id, t.titulo, t.fecha_hora, i.nombre AS iglesia, tr.url AS youtube
            FROM transmisiones t JOIN iglesias i ON i.id = t.iglesia_id
            LEFT JOIN transmision_red tr ON tr.transmision_id = t.id
                AND tr.red_id = (SELECT id FROM redes WHERE LOWER(nombre) = 'youtube' LIMIT 1)
            WHERE t.estado = ? ORDER BY t.fecha_hora DESC, t.id DESC");
        $q->execute([$estado]);
        return $q->fetchAll();
    }
}
