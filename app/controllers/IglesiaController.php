<?php
require_once __DIR__.'/../models/Iglesia.php';
require_once __DIR__.'/../repositories/IglesiaRepository.php';
require_once __DIR__.'/../validator/IglesiaValidator.php';

class IglesiaController extends BaseController {
    private ?IglesiaRepository $repo = null;
    private ?IglesiaValidator $validator = null;

    private const CAMPOS = ['id', 'zona_id', 'nombre', 'direccion', 'latitud',
                            'longitud', 'pastor', 'telefono', 'horarios', 'activa'];

    public function index(): void {
        $this->auth();
        $formulario = $_SESSION['iglesia_formulario'] ?? ['id' => '0', 'activa' => '1'];

        // Editar usa el mismo formulario que registrar.
        if (isset($_GET['editar'])) {
            $id = filter_var($_GET['editar'], FILTER_VALIDATE_INT);
            $iglesia = $id && $id > 0 ? $this->repo()->buscar($id) : null;
            if (!$iglesia) {
                $_SESSION['error'] = 'La iglesia que quieres editar no existe.';
            } else {
                $formulario = $iglesia;
                $formulario['activa'] = $iglesia['activa'] ? '1' : '';
            }
        }

        $this->render('iglesias/index', [
            'iglesias' => $this->repo()->todas(),
            'zonas' => $this->repo()->zonas(),
            'mapaConfig' => require __DIR__.'/../../config/maps.php',
            'formulario' => $formulario,
        ]);
        unset($_SESSION['iglesia_formulario']);
    }

    public function encuentranos(): void {
        $this->render('encuentranos/index', [
            'iglesias' => $this->repo()->activasConZona(),
            'mapaConfig' => require __DIR__.'/../../config/maps.php',
        ]);
    }

    public function guardar(): void {
        $this->auth();
        $this->csrf();

        [$datos, $datosIncorrectos] = $this->leerFormulario();
        $id = filter_var($datos['id'], FILTER_VALIDATE_INT);
        $existente = $id && $id > 0 ? $this->repo()->buscar($id) : null;

        if ($datosIncorrectos) {
            $error = 'El formulario contiene datos incorrectos.';
        } elseif ($id === false || $id < 0) {
            $error = 'El identificador de la iglesia no es válido.';
        } elseif ($id > 0 && !$existente) {
            $error = 'La iglesia que quieres editar ya no existe.';
        } else {
            $error = $this->validator()->validar($datos);
        }

        if ($error !== '') {
            $_SESSION['error'] = $error;
            // Conserva los campos y la ubicación si hay que corregir el formulario.
            if ($id !== false && $id >= 0 && ($id === 0 || $existente)) {
                $_SESSION['iglesia_formulario'] = $datos;
            }
        } else {
            $iglesia = Iglesia::desdeArray($datos);
            $iglesia->id = $id;
            $this->repo()->guardar($iglesia);
            $_SESSION['iglesia_exito'] = $id > 0
                ? 'Iglesia actualizada.'
                : 'Iglesia registrada. Ya puedes seleccionarla al crear una transmisión.';
        }
        $this->redirigir('iglesias');
    }

    public function eliminar(): void {
        $this->auth();
        $this->csrf();
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

        if ($id === false || $id < 1) {
            $_SESSION['error'] = 'Selecciona una iglesia válida para eliminar.';
        } elseif (!$this->repo()->buscar($id)) {
            $_SESSION['error'] = 'La iglesia que quieres eliminar ya no existe.';
        } elseif ($this->repo()->tieneTransmisiones($id)) {
            $_SESSION['error'] = 'Esta iglesia tiene transmisiones; márcala como inactiva en lugar de eliminarla.';
        } else {
            try {
                $this->repo()->eliminar($id);
                $_SESSION['iglesia_exito'] = 'Iglesia eliminada.';
            } catch (PDOException $e) {
                // Otras tablas también pueden tener datos de esta iglesia.
                if (!in_array((string) $e->getCode(), ['23000', '23503'], true)) {
                    throw $e;
                }
                $_SESSION['error'] = 'Esta iglesia tiene registros asociados; márcala como inactiva en lugar de eliminarla.';
            }
        }
        $this->redirigir('iglesias');
    }

    /** Lee y limpia $_POST. Devuelve [datos, hayDatosIncorrectos]. */
    private function leerFormulario(): array {
        $datos = [];
        $incorrectos = false;
        foreach (self::CAMPOS as $campo) {
            $valor = $_POST[$campo] ?? '';
            if (!is_string($valor)) {
                $incorrectos = true;
                $valor = '';
            }
            $datos[$campo] = trim($valor);
        }
        $datos['id'] = $datos['id'] === '' ? '0' : $datos['id'];
        return [$datos, $incorrectos];
    }

    // Redirige a otra pantalla (patrón PRG) y termina la petición.
    private function redirigir(string $ruta): never {
        header('Location: index.php?r='.$ruta);
        exit;
    }

    private function repo(): IglesiaRepository {
        return $this->repo ??= new IglesiaRepository($this->db);
    }

    private function validator(): IglesiaValidator {
        return $this->validator ??= new IglesiaValidator($this->repo());
    }
}