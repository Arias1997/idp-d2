<?php
// Motor activo: 'mysql' o 'pgsql'. Cambia esta línea para elegir.
$driver = getenv('DB_DRIVER') ?: 'mysql';

// Datos de conexión de cada motor.
// Escribe aquí el usuario y la contraseña de TU servidor de base de datos.
// La base idp_d2 se crea al importar database/idp_d2.sql.
$conexiones = [
    'mysql' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'dbname' => 'idp_d2',
        'username' => 'root',
        'password' => 'tu_contraseña',   // XAMPP sin contraseña: deja ''
    ],
    'pgsql' => [
        'host' => '127.0.0.1',
        'port' => '5432',
        'dbname' => 'idp_d2',
        'username' => 'postgres',
        'password' => 'tu_contraseña',
    ],
];
if (!isset($conexiones[$driver])) {
    throw new RuntimeException("El motor debe ser mysql o pgsql.");
}
$config = $conexiones[$driver];
$config['driver'] = $driver;

// Opcional: también permite configurar la conexión desde el entorno.
foreach (['host' => 'DB_HOST', 'port' => 'DB_PORT', 'dbname' => 'DB_NAME',
          'username' => 'DB_USER', 'password' => 'DB_PASSWORD'] as $campo => $variable) {
    if (getenv($variable) !== false) {
        $config[$campo] = getenv($variable);
    }
}
return $config;
