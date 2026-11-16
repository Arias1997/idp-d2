<?php
// Una sola conexión por petición; usa la configuración existente.
final class Conexion {
    public static function abrir(): PDO {
        $c = require __DIR__.'/../../config/database.php';
        $driver = $c['driver'];
        if (!in_array($driver, ['mysql', 'pgsql'], true)) {
            throw new RuntimeException('El motor debe ser mysql o pgsql.');
        }
        if (!in_array($driver, PDO::getAvailableDrivers(), true)) {
            throw new RuntimeException('Activa la extensión pdo_'.$driver.' en PHP.');
        }
        $dsn = $driver.":host={$c['host']};port={$c['port']};dbname={$c['dbname']}";
        if ($driver === 'mysql') {
            $dsn .= ';charset=utf8mb4';
        }
        $db = new PDO($dsn, $c['username'], $c['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        if ($driver === 'pgsql') {
            $db->exec('SET search_path TO public');
        }
        return $db;
    }
}
