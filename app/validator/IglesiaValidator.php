<?php
require_once __DIR__.'/../repositories/IglesiaRepository.php';

/**
 * Reglas de validación del formulario de iglesias.
 * Devuelve '' si todo está bien, o el mensaje de error.
 * No toca sesión, redirecciones ni $_POST.
 */
class IglesiaValidator {
    private const MAXIMOS = [
        'nombre' => 150, 'direccion' => 200, 'pastor' => 120,
        'telefono' => 25, 'horarios' => 5000,
    ];

    public function __construct(private IglesiaRepository $repo) {}

    public function validar(array $datos): string {
        return $this->obligatorios($datos)
            ?: $this->longitudes($datos)
            ?: $this->telefono($datos)
            ?: $this->zona($datos)
            ?: $this->ubicacion($datos)
            ?: $this->estado($datos);
    }

    private function obligatorios(array $datos): string {
        if ($datos['nombre'] === '' || $datos['direccion'] === '') {
            return 'Escribe el nombre y la dirección de la iglesia.';
        }
        return '';
    }

    private function longitudes(array $datos): string {
        foreach (self::MAXIMOS as $campo => $maximo) {
            $longitud = preg_match_all('/./us', $datos[$campo]);
            if ($longitud === false || $longitud > $maximo) {
                return 'Revisa el campo '.$campo.' (máximo '.$maximo.' caracteres).';
            }
        }
        return '';
    }

    private function telefono(array $datos): string {
        if ($datos['telefono'] !== '' && !preg_match('/^[0-9 +\-]+$/D', $datos['telefono'])) {
            return 'El teléfono solo admite números, espacios, + y -.';
        }
        return '';
    }

    private function zona(array $datos): string {
        $zona = filter_var($datos['zona_id'], FILTER_VALIDATE_INT);
        if (!$zona || $zona < 1) {
            return 'Selecciona una zona válida.';
        }
        if (!$this->repo->zonaExiste($zona)) {
            return 'La zona seleccionada no existe.';
        }
        return '';
    }

    private function ubicacion(array $datos): string {
        if (($datos['latitud'] === '') !== ($datos['longitud'] === '')) {
            return 'Marca una ubicación completa en el mapa o usa Quitar ubicación.';
        }
        foreach (['latitud' => 90, 'longitud' => 180] as $campo => $limite) {
            $v = $datos[$campo];
            if ($v !== '' &&
                (!preg_match('/^-?\d{1,3}(?:\.\d{1,7})?$/D', $v) ||
                 (float) $v < -$limite || (float) $v > $limite)) {
                return 'La '.$campo.' debe estar entre -'.$limite.' y '.$limite.', con hasta 7 decimales.';
            }
        }
        return '';
    }

    private function estado(array $datos): string {
        if (!in_array($datos['activa'], ['', '0', '1'], true)) {
            return 'Revisa el estado de la iglesia.';
        }
        return '';
    }
}