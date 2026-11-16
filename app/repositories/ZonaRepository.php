<?php
require_once __DIR__.'/../models/Zona.php';

/**
 * Todo el acceso a datos de zonas.
 * No conoce sesión, HTTP ni validación.
 */
class ZonaRepository {
    public function __construct(private PDO $db) {}

    /** Todas las zonas, como objetos Zona. */
    public function todas(): array {
        return $this->db->query('SELECT id, nombre FROM zonas ORDER BY nombre')
                        ->fetchAll(PDO::FETCH_CLASS, Zona::class);
    }

    public function buscar(int $id): ?array {
        $q = $this->db->prepare('SELECT id, nombre FROM zonas WHERE id = ?');
        $q->execute([$id]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** ¿Ya hay otra zona con ese nombre? ($ignorarId excluye la que se está editando). */
    public function nombreExiste(string $nombre, int $ignorarId = 0): bool {
        $q = $this->db->prepare(
            'SELECT id FROM zonas WHERE LOWER(TRIM(nombre)) = LOWER(?) AND id <> ? LIMIT 1'
        );
        $q->execute([$nombre, $ignorarId]);
        return (bool) $q->fetchColumn();
    }

    public function tieneIglesias(int $id): bool {
        $q = $this->db->prepare('SELECT COUNT(*) FROM iglesias WHERE zona_id = ?');
        $q->execute([$id]);
        return (int) $q->fetchColumn() > 0;
    }

    /** Inserta o actualiza según $zona->id. */
    public function guardar(Zona $zona): void {
        if ($zona->id > 0) {
            $q = $this->db->prepare('UPDATE zonas SET nombre = ? WHERE id = ?');
            $q->execute([$zona->nombre, $zona->id]);
        } else {
            $q = $this->db->prepare('INSERT INTO zonas(nombre) VALUES (?)');
            $q->execute([$zona->nombre]);
        }
    }

    /**
     * Puede lanzar PDOException (23000 / 23503) si hay registros asociados;
     * el controlador decide qué mensaje mostrar.
     */
    public function eliminar(int $id): void {
        $this->db->prepare('DELETE FROM zonas WHERE id = ?')->execute([$id]);
    }
}