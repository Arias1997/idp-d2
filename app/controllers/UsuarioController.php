<?php
require_once __DIR__.'/../repositories/UsuarioRepository.php';
require_once __DIR__.'/../validator/UsuarioValidator.php';

class UsuarioController extends BaseController {
    private ?UsuarioRepository $repo = null;
    private ?UsuarioValidator $validator = null;

    public function index(): void {
        $this->auth();
        $formulario = $_SESSION['usuario_formulario'] ?? [];
        unset($_SESSION['usuario_formulario']);
        $this->render('usuarios/index', [
            'usuarios' => $this->repositorio()->listar(),
            'formulario' => $formulario,
        ]);
    }

    public function guardar(): void {
        $this->auth();
        $this->csrf();
        $nombre = $this->texto('nombre');
        $email = strtolower($this->texto('email'));
        $rol = $this->texto('rol');
        $clave = is_string($_POST['clave'] ?? null) ? $_POST['clave'] : '';
        // La contraseña nunca se conserva en la sesión ni se devuelve al formulario.
        $formulario = ['accion' => 'crear', 'nombre' => $nombre, 'email' => $email, 'rol' => $rol];

        $error = $this->validator()->validarNuevo($nombre, $email, $clave, $rol);

        if ($error !== '') {
            $_SESSION['error'] = $error;
            $_SESSION['usuario_formulario'] = $formulario;
        } else {
            try {
                $this->repositorio()->crear($nombre, $email, password_hash($clave, PASSWORD_DEFAULT), $rol);
                $_SESSION['usuario_exito'] = 'Cuenta creada. Ya puede iniciar sesión con su correo y contraseña.';
                unset($_SESSION['usuario_formulario']);
            } catch (DomainException $e) {
                $_SESSION['error'] = $e->getMessage();
                $_SESSION['usuario_formulario'] = $formulario;
            }
        }
        $this->volver();
    }

    public function asignarRol(): void {
        $administrador = $this->authRol(['administrador']);
        $this->csrf();
        $id = filter_var($_POST['usuario_id'] ?? null, FILTER_VALIDATE_INT);
        $rol = $this->texto('rol');
        try {
            if (!$id || $id < 1) throw new DomainException('Selecciona una cuenta válida.');
            $this->repositorio()->cambiarRol($id, $rol, (int) $administrador['id']);
            $_SESSION['usuario_exito'] = 'Permisos de la cuenta actualizados.';
            unset($_SESSION['usuario_formulario']);
        } catch (DomainException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['usuario_formulario'] = ['accion' => 'rol', 'usuario_id' => $id ?: 0, 'rol' => $rol];
        }
        $this->volver();
    }

    public function miPerfil(): void {
        $usuario = $this->authRol(['administrador', 'usuario']);
        $this->render('panel/perfil', ['usuario' => $usuario]);
    }

    private function repositorio(): UsuarioRepository {
        return $this->repo ??= new UsuarioRepository($this->db);
    }

    private function validator(): UsuarioValidator {
        return $this->validator ??= new UsuarioValidator();
    }

    private function texto(string $campo): string {
        return is_string($_POST[$campo] ?? null) ? trim($_POST[$campo]) : '';
    }

    private function volver(): void {
        header('Location: index.php?r=usuarios');
        exit;
    }
}