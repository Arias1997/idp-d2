<?php
session_start();
date_default_timezone_set('America/La_Paz');
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

require __DIR__.'/../app/controllers/BaseController.php';
require __DIR__.'/../app/controllers/AuthController.php';
require __DIR__.'/../app/controllers/TransmisionController.php';
require __DIR__.'/../app/controllers/IglesiaController.php';
require __DIR__.'/../app/controllers/ZonaController.php';
require __DIR__.'/../app/controllers/UsuarioController.php';
require __DIR__.'/../app/controllers/PanelController.php';
require __DIR__.'/../app/controllers/ReporteController.php';

$ruta = $_GET['r'] ?? 'inicio';
$metodo = $_SERVER['REQUEST_METHOD'];
$rutas = [
    'inicio' => ['GET', TransmisionController::class, 'inicio'],
    'transmisiones' => ['GET', TransmisionController::class, 'sala'],
    'encuentranos' => ['GET', IglesiaController::class, 'encuentranos'],
    'login' => ['GET', AuthController::class, 'login'],
    'registro' => ['GET', AuthController::class, 'registro'],
    'registrar' => ['POST', AuthController::class, 'registrar'],
    'entrar' => ['POST', AuthController::class, 'entrar'],
    'salir' => ['POST', AuthController::class, 'salir'],
    'resumen' => ['GET', PanelController::class, 'resumen'],
    'reporte_resumen' => ['GET', ReporteController::class, 'resumen'],
    'usuarios' => ['GET', UsuarioController::class, 'index'],
    'guardar_usuario' => ['POST', UsuarioController::class, 'guardar'],
    'asignar_rol' => ['POST', UsuarioController::class, 'asignarRol'],
    'mi_perfil' => ['GET', UsuarioController::class, 'miPerfil'],
    'admin' => ['GET', TransmisionController::class, 'admin'],
    'zonas' => ['GET', ZonaController::class, 'index'],
    'guardar_zona' => ['POST', ZonaController::class, 'guardar'],
    'eliminar_zona' => ['POST', ZonaController::class, 'eliminar'],
    'iglesias' => ['GET', IglesiaController::class, 'index'],
    'guardar_iglesia' => ['POST', IglesiaController::class, 'guardar'],
    'eliminar_iglesia' => ['POST', IglesiaController::class, 'eliminar'],
    'guardar' => ['POST', TransmisionController::class, 'guardar'],
    'eliminar' => ['POST', TransmisionController::class, 'eliminar'],
];
if (!is_string($ruta) || !isset($rutas[$ruta]) || $rutas[$ruta][0] !== $metodo) {
    http_response_code(404);
    exit('Página no encontrada.');
}
try {
    $controlador = new $rutas[$ruta][1]();
    $accion = $rutas[$ruta][2];
    $controlador->$accion();
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    echo 'No se pudo completar la operación. Revisa la configuración y el registro de PHP.';
}
