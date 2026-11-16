<?php
/**
 * Reglas de validación para crear usuarios.
 * Devuelve '' si todo está bien, o el mensaje de error.
 * No toca sesión, redirecciones ni $_POST.
 */
class UsuarioValidator {
    private const ROLES = ['administrador', 'usuario'];

    public function validarNuevo(string $nombre, string $email, string $clave, string $rol): string {
        return $this->nombre($nombre)
            ?: $this->email($email)
            ?: $this->clave($clave)
            ?: $this->rol($rol);
    }

    public function validarRegistro(string $nombre, string $email, string $clave, string $confirmacion): string {
        return $this->validarNuevo($nombre, $email, $clave, 'usuario')
            ?: ($clave === $confirmacion ? '' : 'Las contraseñas no coinciden.');
    }

    private function nombre(string $nombre): string {
        $largo = preg_match_all('/./us', $nombre);
        if ($largo === false || $largo < 1 || $largo > 120) {
            return 'Escribe un nombre de hasta 120 caracteres.';
        }
        return '';
    }

    private function email(string $email): string {
        if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Escribe un correo válido de hasta 150 caracteres.';
        }
        return '';
    }

    private function clave(string $clave): string {
        $largo = preg_match_all('/./us', $clave);
        // 72 bytes es el límite real de bcrypt (password_hash por defecto).
        if ($largo === false || $largo < 8 || strlen($clave) > 72 || str_contains($clave, "\0")) {
            return 'La contraseña debe tener al menos 8 caracteres y no superar el tamaño permitido.';
        }
        return '';
    }

    private function rol(string $rol): string {
        if (!in_array($rol, self::ROLES, true)) {
            return 'Selecciona administrador o usuario.';
        }
        return '';
    }
}