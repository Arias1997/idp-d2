<?php
// Consultas de cuentas y roles. Los controladores no necesitan conocer el SQL.
class UsuarioRepository {
    public function __construct(private PDO $db) {}

    public static function normalizarRol(string $rol): string {
        $rol = strtolower(trim($rol));
        return $rol === 'admin' ? 'administrador' : $rol;
    }

    public function listar(): array {
        $filas = $this->db->query('SELECT u.id, u.nombre, u.email, r.nombre AS rol
            FROM usuarios u JOIN roles r ON r.id = u.rol_id ORDER BY u.nombre, u.id')->fetchAll();
        foreach ($filas as &$fila) {
            $fila['rol'] = self::normalizarRol($fila['rol']);
        }
        return $filas;
    }

    public function buscarPorId(int $id): ?array {
        $q = $this->db->prepare('SELECT u.id, u.nombre, u.email, u.iglesia_id,
            r.nombre AS rol, i.nombre AS iglesia FROM usuarios u
            JOIN roles r ON r.id = u.rol_id LEFT JOIN iglesias i ON i.id = u.iglesia_id
            WHERE u.id = ?');
        $q->execute([$id]);
        $usuario = $q->fetch();
        if (!$usuario) return null;
        $usuario['id'] = (int) $usuario['id'];
        $usuario['iglesia_id'] = $usuario['iglesia_id'] === null ? null : (int) $usuario['iglesia_id'];
        $usuario['rol'] = self::normalizarRol($usuario['rol']);
        return $usuario;
    }

    public function buscarPorCorreo(string $correo): ?array {
        $q = $this->db->prepare('SELECT u.id, u.nombre, u.email, u.password, r.nombre AS rol
            FROM usuarios u JOIN roles r ON r.id = u.rol_id WHERE LOWER(u.email) = LOWER(?) LIMIT 1');
        $q->execute([$correo]);
        $usuario = $q->fetch();
        if (!$usuario) return null;
        $usuario['rol'] = self::normalizarRol($usuario['rol']);
        return $usuario;
    }

    public function crear(string $nombre, string $email, string $hash, string $rol): int {
        $this->comprobarRol($rol);
        $this->db->beginTransaction();
        try {
            // El bloqueo también evita crear dos veces un rol que todavía no existe.
            $roles = $this->bloquearRoles();
            if ($this->buscarPorCorreo($email)) {
                throw new DomainException('Ya existe una cuenta con ese correo.');
            }
            $rolId = $this->obtenerRolId($rol, $roles);
            $sql = 'INSERT INTO usuarios(rol_id, iglesia_id, nombre, email, password) VALUES (?, NULL, ?, ?, ?)';
            $postgres = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';
            $q = $this->db->prepare($sql.($postgres ? ' RETURNING id' : ''));
            $q->execute([$rolId, $nombre, $email, $hash]);
            $id = (int) ($postgres ? $q->fetchColumn() : $this->db->lastInsertId());
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            if ($e instanceof PDOException && in_array((string) $e->getCode(), ['23000', '23505'], true)) {
                throw new DomainException('Ya existe una cuenta con ese correo.', 0, $e);
            }
            throw $e;
        }
    }

    public function cambiarRol(int $usuarioId, string $rol, int $administradorId): void {
        $this->comprobarRol($rol);
        $this->db->beginTransaction();
        try {
            // Todos los cambios de rol usan este bloqueo antes de contar administradores.
            $roles = $this->bloquearRoles();
            $q = $this->db->prepare('SELECT id, rol_id FROM usuarios WHERE id = ? FOR UPDATE');
            $q->execute([$usuarioId]);
            $usuario = $q->fetch();
            if (!$usuario) throw new DomainException('El usuario no existe.');
            if ($usuarioId === $administradorId && $rol !== 'administrador') {
                throw new DomainException('No puedes quitarte tu propio permiso de administrador.');
            }
            $rolActual = '';
            foreach ($roles as $fila) {
                if ((int) $fila['id'] === (int) $usuario['rol_id']) {
                    $rolActual = self::normalizarRol($fila['nombre']);
                }
            }
            if ($rolActual === 'administrador' && $rol === 'usuario') {
                $total = (int) $this->db->query("SELECT COUNT(*) FROM usuarios u JOIN roles r ON r.id = u.rol_id
                    WHERE LOWER(r.nombre) IN ('administrador', 'admin')")->fetchColumn();
                if ($total <= 1) throw new DomainException('Debe quedar al menos un administrador.');
            }
            $rolId = $this->obtenerRolId($rol, $roles);
            $q = $this->db->prepare('UPDATE usuarios SET rol_id = ? WHERE id = ?');
            $q->execute([$rolId, $usuarioId]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarHash(int $id, string $anterior, string $nuevo): bool {
        $q = $this->db->prepare('UPDATE usuarios SET password = ? WHERE id = ? AND password = ?');
        $q->execute([$nuevo, $id, $anterior]);
        return $q->rowCount() === 1;
    }

    private function comprobarRol(string $rol): void {
        if (!in_array($rol, ['administrador', 'usuario'], true)) {
            throw new DomainException('Selecciona administrador o usuario.');
        }
    }

    private function bloquearRoles(): array {
        return $this->db->query('SELECT id, nombre FROM roles ORDER BY id FOR UPDATE')->fetchAll();
    }

    private function obtenerRolId(string $rol, array $roles): int {
        foreach ($roles as $fila) {
            if (self::normalizarRol($fila['nombre']) === $rol) return (int) $fila['id'];
        }
        $postgres = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';
        $q = $this->db->prepare('INSERT INTO roles(nombre) VALUES (?)'.($postgres ? ' RETURNING id' : ''));
        $q->execute([$rol]);
        return (int) ($postgres ? $q->fetchColumn() : $this->db->lastInsertId());
    }
}
