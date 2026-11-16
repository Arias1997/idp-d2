<?php
require_once __DIR__.'/../models/Iglesia.php';

/**
 * Todo el acceso a datos de iglesias (y las zonas que usa).
 * No conoce sesión, HTTP ni validación.
 */
class IglesiaRepository {
    public function __construct(private PDO $db) {}

    /** Todas las iglesias (admin), como objetos Iglesia. */
    public function todas(): array {
        $sql = 'SELECT i.*, z.nombre AS zona
                FROM iglesias i
                JOIN zonas z ON z.id = i.zona_id
                ORDER BY i.id DESC';
        return $this->db->query($sql)->fetchAll(PDO::FETCH_CLASS, Iglesia::class);
    }

    /** Iglesias activas para la página pública "Encuéntranos". */
    public function activasConZona(): array {
        $sql = 'SELECT i.nombre, i.direccion, i.latitud, i.longitud,
                       i.pastor, i.telefono, i.horarios, z.nombre AS zona
                FROM iglesias i
                JOIN zonas z ON z.id = i.zona_id
                WHERE i.activa = TRUE
                ORDER BY z.nombre, i.nombre';
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscar(int $id): ?array {
        $q = $this->db->prepare('SELECT * FROM iglesias WHERE id = ?');
        $q->execute([$id]);
        return $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function zonas(): array {
        return $this->db->query('SELECT id, nombre FROM zonas ORDER BY nombre')
                        ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function zonaExiste(int $id): bool {
        $q = $this->db->prepare('SELECT id FROM zonas WHERE id = ?');
        $q->execute([$id]);
        return (bool) $q->fetchColumn();
    }

    public function tieneTransmisiones(int $id): bool {
        $q = $this->db->prepare('SELECT COUNT(*) FROM transmisiones WHERE iglesia_id = ?');
        $q->execute([$id]);
        return (int) $q->fetchColumn() > 0;
    }

    /** Inserta o actualiza según $iglesia->id. */
    public function guardar(Iglesia $iglesia): void {
        $valores = [
            $iglesia->zona_id, $iglesia->nombre, $iglesia->direccion,
            $iglesia->latitud, $iglesia->longitud, $iglesia->pastor,
            $iglesia->telefono, $iglesia->horarios, $iglesia->activa ? 1 : 0,
        ];

        if ($iglesia->id > 0) {
            $sql = 'UPDATE iglesias SET zona_id = ?, nombre = ?, direccion = ?, latitud = ?,
                    longitud = ?, pastor = ?, telefono = ?, horarios = ?, activa = ? WHERE id = ?';
            $valores[] = $iglesia->id;
        } else {
            $sql = 'INSERT INTO iglesias(zona_id, nombre, direccion, latitud, longitud, pastor, telefono, horarios, activa)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        }
        $this->db->prepare($sql)->execute($valores);
    }

    /**
     * Elimina la iglesia. Puede lanzar PDOException (23000 / 23503)
     * si otras tablas tienen registros asociados; el controlador decide qué mostrar.
     */
    public function eliminar(int $id): void {
        $this->db->prepare('DELETE FROM iglesias WHERE id = ?')->execute([$id]);
    }
}