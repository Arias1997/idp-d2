<?php
require_once __DIR__.'/../repositories/UsuarioRepository.php';
require_once __DIR__.'/../validator/UsuarioValidator.php';

class AuthController extends BaseController {
    public function login(): void {
        if (!empty($_SESSION['usuario_id'])) {
            $usuario = $this->authRol(['administrador', 'usuario']);
            header('Location: index.php?r='.$this->inicioSegunRol($usuario['rol']));
            exit;
        }
        $this->render('auth/login');
    }

    public function entrar(): void {
        $this->csrf();
        $correo = is_string($_POST['correo'] ?? null) ? trim($_POST['correo']) : '';
        $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';
        $usuario = filter_var($correo, FILTER_VALIDATE_EMAIL) && strlen($correo) <= 150
            ? $this->repositorio()->buscarPorCorreo($correo) : null;
        if (!$usuario || !in_array($usuario['rol'], ['administrador', 'usuario'], true) ||
            !$this->verificarClave($usuario, $clave)) {
            $_SESSION['error'] = 'Correo o contraseña incorrectos.';
            $_SESSION['login_correo'] = $correo;
            header('Location: index.php?r=login');
            exit;
        }
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        unset($_SESSION['login_correo']);
        header('Location: index.php?r='.$this->inicioSegunRol($usuario['rol']));
        exit;
    }

    // Backend de registro: entrega JSON para que el frontend pueda consumirlo.
    public function registro(): void {
        if (!empty($_SESSION['usuario_id'])) {
            $this->responderRegistro(['ok' => false, 'mensaje' => 'Ya tienes una sesión iniciada.'], 409);
            return;
        }
        $this->responderRegistro(['ok' => true, 'csrf' => $_SESSION['csrf']]);
    }

    public function registrar(): void {
        if (!$this->csrfValido()) {
            $this->responderRegistro(['ok' => false, 'mensaje' => 'Token de seguridad inválido.'], 403);
            return;
        }
        if (!empty($_SESSION['usuario_id'])) {
            $this->responderRegistro(['ok' => false, 'mensaje' => 'Ya tienes una sesión iniciada.'], 409);
            return;
        }

        $nombre = is_string($_POST['nombre'] ?? null) ? trim($_POST['nombre']) : '';
        $correo = is_string($_POST['correo'] ?? null) ? strtolower(trim($_POST['correo'])) : '';
        $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';
        $confirmacion = is_string($_POST['confirmar_clave'] ?? null) ? $_POST['confirmar_clave'] : '';
        $error = (new UsuarioValidator())->validarRegistro($nombre, $correo, $clave, $confirmacion);
        if ($error !== '') {
            $this->responderRegistro(['ok' => false, 'mensaje' => $error], 422);
            return;
        }

        try {
            // El cliente nunca decide el rol ni la iglesia de una cuenta pública.
            $this->repositorio()->crear($nombre, $correo, password_hash($clave, PASSWORD_DEFAULT), 'usuario');
        } catch (DomainException $e) {
            $this->responderRegistro(['ok' => false, 'mensaje' => $e->getMessage()], 409);
            return;
        } catch (Throwable $e) {
            error_log((string) $e);
            $this->responderRegistro(['ok' => false, 'mensaje' => 'No se pudo crear la cuenta. Inténtalo nuevamente.'], 500);
            return;
        }

        // El alta no inicia sesión ni devuelve la clave o su hash.
        $this->responderRegistro([
            'ok' => true,
            'mensaje' => 'Cuenta creada. Ya puedes iniciar sesión.',
            'redirect' => 'index.php?r=login',
        ], 201);
    }

    private function responderRegistro(array $datos, int $estado = 200): void {
        http_response_code($estado);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public function salir(): void {
        $this->csrf();
        $_SESSION = [];
        session_destroy();
        header('Location: index.php');
        exit;
    }

    private function verificarClave(array $usuario, string $clave): bool {
        if ($clave === '' || str_contains($clave, "\0")) return false;
        if (password_verify($clave, $usuario['password'])) return true;
        // Una cuenta antigua conserva su clave; solo cambia el formato del hash.
        if (!preg_match('/^[a-f0-9]{64}$/iD', $usuario['password']) ||
            !hash_equals(strtolower($usuario['password']), hash('sha256', $clave))) {
            return false;
        }
        return $this->repositorio()->actualizarHash((int) $usuario['id'], $usuario['password'],
            password_hash($clave, PASSWORD_DEFAULT));
    }

    private function inicioSegunRol(string $rol): string {
        return $rol === 'administrador' ? 'resumen' : 'mi_perfil';
    }

    private function repositorio(): UsuarioRepository {
        return new UsuarioRepository($this->db);
    }
}
