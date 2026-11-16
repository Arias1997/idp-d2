<?php
require_once __DIR__.'/../models/Zona.php';
require_once __DIR__.'/../repositories/ZonaRepository.php';
require_once __DIR__.'/../validator/ZonaValidator.php';

class ZonaController extends BaseController {
    private ?ZonaRepository $repo = null;
    private ?ZonaValidator $validator = null;

    public function index(): void {
        $this->auth();
        $formulario = $_SESSION['zona_formulario'] ?? ['id' => '0', 'nombre' => ''];

        if (isset($_GET['editar'])) {
            $id = filter_var($_GET['editar'], FILTER_VALIDATE_INT);
            $zona = $id && $id > 0 ? $this->repo()->buscar($id) : null;
            if (!$zona) {
                $_SESSION['error'] = 'La zona que quieres editar no existe.';
            } else {
                $formulario = $zona;
            }
        }

        $this->render('zonas/index', [
            'zonas' => $this->repo()->todas(),
            'formulario' => $formulario,
        ]);
        unset($_SESSION['zona_formulario']);
    }

    public function guardar(): void {
        $this->auth();
        $this->csrf();

        $valorId = $_POST['id'] ?? '0';
        $valorNombre = $_POST['nombre'] ?? '';
        $id = is_string($valorId)
            ? filter_var(trim($valorId) === '' ? '0' : trim($valorId), FILTER_VALIDATE_INT)
            : false;
        $nombre = is_string($valorNombre) ? trim($valorNombre) : '';
        $existente = $id && $id > 0 ? $this->repo()->buscar($id) : null;

        if ($id === false || $id < 0 || !is_string($valorNombre)) {
            $error = 'El formulario contiene datos incorrectos.';
        } elseif ($id > 0 && !$existente) {
            $error = 'La zona que quieres editar ya no existe.';
        } else {
            $error = $this->validator()->validar($nombre, $id);
        }

        if ($error !== '') {
            $_SESSION['error'] = $error;
            if ($id !== false && $id >= 0 && ($id === 0 || $existente)) {
                $_SESSION['zona_formulario'] = ['id' => $id, 'nombre' => $nombre];
            }
        } else {
            $zona = new Zona();
            $zona->id = $id;
            $zona->nombre = $nombre;
            $this->repo()->guardar($zona);
            $_SESSION['zona_exito'] = $id > 0
                ? 'Zona actualizada.'
                : 'Zona registrada. Ya puedes seleccionarla al registrar una iglesia.';
        }
        $this->redirigir('zonas');
    }

    public function eliminar(): void {
        $this->auth();
        $this->csrf();
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

        if ($id === false || $id < 1) {
            $_SESSION['error'] = 'Selecciona una zona válida para eliminar.';
        } elseif (!$this->repo()->buscar($id)) {
            $_SESSION['error'] = 'La zona que quieres eliminar ya no existe.';
        } elseif ($this->repo()->tieneIglesias($id)) {
            $_SESSION['error'] = 'Mueve o elimina primero las iglesias de esta zona.';
        } else {
            try {
                $this->repo()->eliminar($id);
                $_SESSION['zona_exito'] = 'Zona eliminada.';
            } catch (PDOException $e) {
                if (!in_array((string) $e->getCode(), ['23000', '23503'], true)) {
                    throw $e;
                }
                $_SESSION['error'] = 'La zona tiene registros asociados. Muévelos antes de eliminarla.';
            }
        }
        $this->redirigir('zonas');
    }

    // Redirige a otra pantalla (patrón PRG) y termina la petición.
    private function redirigir(string $ruta): never {
        header('Location: index.php?r='.$ruta);
        exit;
    }

    private function repo(): ZonaRepository {
        return $this->repo ??= new ZonaRepository($this->db);
    }

    private function validator(): ZonaValidator {
        return $this->validator ??= new ZonaValidator($this->repo());
    }
}