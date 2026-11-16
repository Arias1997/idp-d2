<?php
require_once __DIR__.'/../models/Transmision.php';
require_once __DIR__.'/../validator/TransmisionValidator.php';

// Consultas y transacciones de las transmisiones. No depende de la sesión ni del HTML.
class TransmisionRepository {
    public function __construct(private PDO $db) {}

    public function listar(): array {
        $sql = "SELECT t.*, i.nombre AS iglesia, tr.url AS youtube, fb.url AS facebook, tk.url AS tiktok
                FROM transmisiones t
                JOIN iglesias i ON i.id = t.iglesia_id
                LEFT JOIN transmision_red tr ON tr.transmision_id = t.id
                    AND tr.red_id = (SELECT id FROM redes WHERE LOWER(nombre) = 'youtube' LIMIT 1)
                LEFT JOIN transmision_red fb ON fb.transmision_id = t.id
                    AND fb.red_id = (SELECT id FROM redes WHERE LOWER(nombre) = 'facebook' LIMIT 1)
                LEFT JOIN transmision_red tk ON tk.transmision_id = t.id
                    AND tk.red_id = (SELECT id FROM redes WHERE LOWER(nombre) = 'tiktok' LIMIT 1)
                ORDER BY CASE t.estado WHEN 'en_vivo' THEN 0 WHEN 'programada' THEN 1 ELSE 2 END,
                         t.fecha_hora DESC, t.id DESC";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_CLASS, Transmision::class);
        foreach ($rows as $transmision) {
            $transmision->youtube_id = TransmisionValidator::videoId($transmision->youtube ?? '');
        }
        return $rows;
    }

    public function iglesias(): array {
        return $this->db->query('SELECT id, nombre FROM iglesias ORDER BY nombre')->fetchAll();
    }

    public function buscar(int $id) {
        $q = $this->db->prepare('SELECT * FROM transmisiones WHERE id = ?');
        $q->execute([$id]);
        return $q->fetch();
    }

    public function iglesiaExiste(int $id): bool {
        $q = $this->db->prepare('SELECT id FROM iglesias WHERE id = ?');
        $q->execute([$id]);
        return (bool) $q->fetch();
    }

    public function guardar(int $id, int $iglesia, string $titulo, string $fecha, string $estado, string $youtube, ?string $facebook = null, ?string $tiktok = null): void {
        $esPostgres = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';
        // Guarda la publicación y todos sus enlaces juntos en ambos motores.
        $this->db->beginTransaction();
        try {
            if ($id > 0) {
                if (!$this->buscar($id)) {
                    throw new RuntimeException('La transmisión ya no existe.');
                }
                $q = $this->db->prepare('UPDATE transmisiones SET iglesia_id = ?, titulo = ?, fecha_hora = ?, estado = ? WHERE id = ?');
                $q->execute([$iglesia, $titulo, $fecha, $estado, $id]);
            } else {
                $sql = 'INSERT INTO transmisiones(iglesia_id, titulo, fecha_hora, estado) VALUES (?, ?, ?, ?)';
                if ($esPostgres) {
                    $sql .= ' RETURNING id';
                }
                $q = $this->db->prepare($sql);
                $q->execute([$iglesia, $titulo, $fecha, $estado]);
                $id = (int) ($esPostgres ? $q->fetchColumn() : $this->db->lastInsertId());
            }

            foreach (['YouTube' => $youtube, 'Facebook' => $facebook, 'TikTok' => $tiktok] as $nombre => $url) {
                if ($url !== null) {
                    $this->guardarEnlace($id, $nombre, $url, $esPostgres);
                }
            }
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function guardarEnlace(int $id, string $nombre, string $url, bool $esPostgres): void {
        $q = $this->db->prepare('SELECT id FROM redes WHERE LOWER(nombre) = LOWER(?) LIMIT 1');
        $q->execute([$nombre]);
        $red = $q->fetchColumn();

        // Un campo vacío elimina solo su asociación; no crea una red vacía.
        if ($url === '') {
            if ($red !== false) {
                $q = $this->db->prepare('DELETE FROM transmision_red WHERE transmision_id = ? AND red_id = ?');
                $q->execute([$id, $red]);
            }
            return;
        }

        if ($red === false) {
            $sql = 'INSERT INTO redes(nombre) VALUES (?)';
            if ($esPostgres) {
                $sql .= ' RETURNING id';
            }
            $q = $this->db->prepare($sql);
            $q->execute([$nombre]);
            $red = $esPostgres ? $q->fetchColumn() : $this->db->lastInsertId();
        }

        // La forma de guardar o actualizar un enlace cambia según el motor.
        if ($esPostgres) {
            $q = $this->db->prepare('INSERT INTO transmision_red(transmision_id, red_id, url) VALUES (?, ?, ?) ON CONFLICT (transmision_id, red_id) DO UPDATE SET url = EXCLUDED.url');
            $q->execute([$id, $red, $url]);
        } else {
            $q = $this->db->prepare('INSERT INTO transmision_red(transmision_id, red_id, url) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE url = ?');
            $q->execute([$id, $red, $url, $url]);
        }
    }

    public function eliminar(int $id): void {
        // El respaldo ya define ON DELETE CASCADE para transmision_red.
        $q = $this->db->prepare('DELETE FROM transmisiones WHERE id = ?');
        $q->execute([$id]);
    }
}
