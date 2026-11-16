<?php
require_once __DIR__.'/../support/Conexion.php';
require_once __DIR__.'/../repositories/UsuarioRepository.php';

abstract class BaseController {
    protected PDO $db;

    public function __construct() {
        $this->db = Conexion::abrir();
    }

    protected function render(string $view, array $vars = []): void {
        extract($vars);
        require __DIR__.'/../views/layouts/header.php';
        require __DIR__.'/../views/'.$view.'.php';
        require __DIR__.'/../views/layouts/footer.php';
    }

    protected function auth(): void {
        $this->authRol(['administrador']);
    }

    protected function authRol(array $roles): array {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?r=login');
            exit;
        }
        // Lee el rol actual: un cambio de permisos se aplica a sesiones abiertas.
        $usuario = (new UsuarioRepository($this->db))->buscarPorId((int) $_SESSION['usuario_id']);
        if (!$usuario || !in_array($usuario['rol'], ['administrador', 'usuario'], true)) {
            unset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], $_SESSION['usuario_rol']);
            header('Location: index.php?r=login');
            exit;
        }
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        if (!in_array($usuario['rol'], $roles, true)) {
            http_response_code(403);
            exit('No tienes permiso para realizar esta acción.');
        }
        return $usuario;
    }

    protected function csrfValido(): bool {
        $esperado = $_SESSION['csrf'] ?? null;
        $recibido = $_POST['csrf'] ?? null;
        return is_string($esperado) && $esperado !== '' && is_string($recibido)
            && hash_equals($esperado, $recibido);
    }

    protected function csrf(): void {
        if (!$this->csrfValido()) {
            http_response_code(403);
            exit('Token de seguridad inválido.');
        }
    }
}
