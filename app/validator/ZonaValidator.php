<?php
require_once __DIR__.'/../repositories/ZonaRepository.php';

/**
 * Reglas de validación del formulario de zonas.
 * Devuelve '' si todo está bien, o el mensaje de error.
 */
class ZonaValidator {
    private const MAXIMO_NOMBRE = 100;

    public function __construct(private ZonaRepository $repo) {}

    public function validar(string $nombre, int $id): string {
        if ($nombre === '') {
            return 'Escribe el nombre de la zona.';
        }

        $longitud = preg_match_all('/./us', $nombre);
        if ($longitud === false || $longitud > self::MAXIMO_NOMBRE) {
            return 'El nombre de la zona debe tener como máximo '.self::MAXIMO_NOMBRE.' caracteres.';
        }

        if ($this->repo->nombreExiste($nombre, $id)) {
            return 'Ya existe una zona con ese nombre.';
        }
        return '';
    }
}