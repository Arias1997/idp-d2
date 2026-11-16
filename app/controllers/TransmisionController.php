<?php
require_once __DIR__.'/../repositories/TransmisionRepository.php';
require_once __DIR__.'/../validator/TransmisionValidator.php';

class TransmisionController extends BaseController {
    public function inicio(): void {
        $this->render('transmisiones/inicio', ['rows' => $this->repositorio()->listar()]);
    }

    public function sala(): void {
        $this->render('transmisiones/sala', ['rows' => $this->repositorio()->listar()]);
    }

    public function admin(): void {
        $this->auth();
        $repo = $this->repositorio();
        $this->render('transmisiones/admin', [
            'rows' => $repo->listar(),
            'iglesias' => $repo->iglesias(),
            'formulario' => $_SESSION['formulario'] ?? [],
        ]);
        unset($_SESSION['formulario']);
    }

    public function guardar(): void {
        $this->auth();
        $this->csrf();
        $repo = $this->repositorio();
        $error = TransmisionValidator::validar($_POST);
        if ($error === '') {
            $datos = TransmisionValidator::datos($_POST);
            if (!$repo->iglesiaExiste($datos['iglesia'])) {
                $error = 'La iglesia seleccionada no existe.';
            } elseif ($datos['id'] > 0 && !$repo->buscar($datos['id'])) {
                $error = 'La transmisión ya no existe.';
            } else {
                $repo->guardar($datos['id'], $datos['iglesia'], $datos['titulo'], $datos['fecha'],
                    $datos['estado'], $datos['youtube'], $datos['facebook'], $datos['tiktok']);
                $_SESSION['exito'] = 'Transmisión guardada.';
            }
        }
        if ($error !== '') {
            $_SESSION['error'] = $error;
            // No devolver listas al HTML si se recibió un POST mal formado.
            $_SESSION['formulario'] = array_filter($_POST, 'is_string');
        }
        header('Location: index.php?r=admin');
        exit;
    }

    public function eliminar(): void {
        $this->auth();
        $this->csrf();
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        if ($id && $id > 0) {
            $this->repositorio()->eliminar($id);
            $_SESSION['exito'] = 'Transmisión eliminada.';
        }
        header('Location: index.php?r=admin');
        exit;
    }

    private function repositorio(): TransmisionRepository {
        return new TransmisionRepository($this->db);
    }
}
