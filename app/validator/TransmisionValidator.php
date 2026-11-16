<?php
// Reglas de entrada, sin conexión a base de datos ni dependencias externas.
class TransmisionValidator {
    public static function validar(array $entrada): string {
        foreach (['id', 'iglesia_id', 'titulo', 'youtube', 'facebook', 'tiktok', 'estado', 'fecha_hora'] as $campo) {
            if (array_key_exists($campo, $entrada) && !is_string($entrada[$campo])) {
                return 'El formulario contiene datos incorrectos.';
            }
        }
        $id = filter_var($entrada['id'] ?? '0', FILTER_VALIDATE_INT);
        $iglesia = filter_var($entrada['iglesia_id'] ?? '', FILTER_VALIDATE_INT);
        $titulo = trim($entrada['titulo'] ?? '');
        $longitud = preg_match_all('/./us', $titulo);
        $fechaTexto = $entrada['fecha_hora'] ?? '';
        $fecha = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/D', $fechaTexto)
            ? DateTime::createFromFormat('!Y-m-d\\TH:i', $fechaTexto) : false;
        if ($id === false || $id < 0 || !$iglesia || $iglesia < 1 ||
            $titulo === '' || $longitud === false || $longitud > 150 ||
            !$fecha || $fecha->format('Y-m-d\\TH:i') !== $fechaTexto ||
            !in_array($entrada['estado'] ?? '', ['programada', 'en_vivo', 'finalizada'], true) ||
            !self::videoId(trim($entrada['youtube'] ?? ''))) {
            return 'Revisa el título (máximo 150 caracteres), la iglesia, fecha, estado y enlace de YouTube.';
        }
        $dominios = [
            'facebook' => ['facebook.com', 'www.facebook.com', 'm.facebook.com', 'fb.watch'],
            'tiktok' => ['tiktok.com', 'www.tiktok.com', 'vm.tiktok.com'],
        ];
        foreach ($dominios as $campo => $hosts) {
            if (array_key_exists($campo, $entrada) && !self::urlRedValida(trim($entrada[$campo]), $hosts)) {
                return 'Escribe un enlace HTTP o HTTPS válido de '.($campo === 'facebook' ? 'Facebook' : 'TikTok').' (máximo 500 caracteres), o deja el campo vacío.';
            }
        }
        return '';
    }

    // Se llama después de validar: conserva la diferencia entre omitido y vacío.
    public static function datos(array $entrada): array {
        $fecha = DateTime::createFromFormat('!Y-m-d\\TH:i', $entrada['fecha_hora']);
        return [
            'id' => (int) ($entrada['id'] ?? 0),
            'iglesia' => (int) $entrada['iglesia_id'],
            'titulo' => trim($entrada['titulo']),
            'fecha' => $fecha->format('Y-m-d H:i:s'),
            'estado' => $entrada['estado'],
            'youtube' => 'https://www.youtube.com/watch?v='.self::videoId(trim($entrada['youtube'])),
            'facebook' => array_key_exists('facebook', $entrada) ? trim($entrada['facebook']) : null,
            'tiktok' => array_key_exists('tiktok', $entrada) ? trim($entrada['tiktok']) : null,
        ];
    }

    public static function urlRedValida(string $url, array $dominios): bool {
        if ($url === '') {
            return true;
        }
        // La columna url existente admite hasta 500 caracteres.
        if (strlen($url) > 500 || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $partes = parse_url($url);
        return $partes !== false &&
            in_array(strtolower($partes['scheme'] ?? ''), ['http', 'https'], true) &&
            in_array(strtolower($partes['host'] ?? ''), $dominios, true) &&
            !isset($partes['user']) && !isset($partes['pass']);
    }

    public static function videoId(string $url): ?string {
        if (preg_match('/^[A-Za-z0-9_-]{11}$/D', $url)) {
            return $url;
        }
        $partes = parse_url($url);
        if (!$partes || !isset($partes['host']) ||
            !in_array(strtolower($partes['scheme'] ?? ''), ['http', 'https'], true)) {
            return null;
        }
        $host = strtolower($partes['host']);
        $ruta = $partes['path'] ?? '';
        $id = null;
        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $id = trim($ruta, '/');
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            parse_str($partes['query'] ?? '', $consulta);
            if ($ruta === '/watch') {
                $id = $consulta['v'] ?? null;
            } elseif (preg_match('~^/(?:live|embed|shorts)/([A-Za-z0-9_-]{11})/?$~D', $ruta, $coincidencia)) {
                $id = $coincidencia[1];
            }
        }
        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/D', $id) ? $id : null;
    }
}
