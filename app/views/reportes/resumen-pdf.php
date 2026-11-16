<?php
// PLANTILLA DEL REPORTE PDF: "Resumen del Distrito 2".
// No usa el layout del sitio: es un HTML completo que el backend convierte a PDF con Dompdf
// (ver PENDIENTES-BACKEND.md, punto 8). Dompdf entiende CSS sencillo: por eso se usan tablas
// en lugar de flex o grid, y la fuente DejaVu Sans (incluye tildes y ñ).
//
// Recibe:
//   $totales       ['transmisiones' => ['en_vivo' => n, 'programada' => n, 'finalizada' => n],
//                   'iglesias_activas' => n, 'iglesias_inactivas' => n, 'zonas' => n, 'usuarios' => n]
//   $porZona       arreglos con zona e iglesias (cantidad de iglesias por zona)
//   $iglesias      arreglos con nombre, zona, pastor, telefono y activa
//   $transmisiones arreglos con titulo, iglesia, fecha_hora y estado (las más recientes)
//   $generadoPor   nombre del administrador que descargó el reporte
$totales = $totales ?? [];
$tx = ($totales['transmisiones'] ?? []) + ['en_vivo' => 0, 'programada' => 0, 'finalizada' => 0];
$porZona = $porZona ?? [];
$iglesias = $iglesias ?? [];
$transmisiones = $transmisiones ?? [];
$generadoPor = $generadoPor ?? ($_SESSION['usuario_nombre'] ?? 'Administrador');
$etiquetas = ['en_vivo' => 'En vivo', 'programada' => 'Programada', 'finalizada' => 'Finalizada'];
$e = static fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

// Logo incrustado en base64 (Dompdf no necesita leer archivos externos). Requiere la extensión gd de PHP.
$rutaLogo = __DIR__.'/../../../public/assets/img/logo-IDP-B.png';
$logo = is_file($rutaLogo) && extension_loaded('gd') ? 'data:image/png;base64,'.base64_encode(file_get_contents($rutaLogo)) : null;
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fecha = date('j').' de '.$meses[(int) date('n') - 1].' de '.date('Y').' · '.date('H:i');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Resumen del Distrito 2 · IDP Sacaba</title>
<style>
    @page { margin: 26mm 16mm 20mm; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9.5pt; color: #1c2433; }

    /* Encabezado y pie fijos en todas las páginas */
    header { position: fixed; top: -19mm; left: 0; right: 0; height: 14mm; border-bottom: 2px solid #f5a524; }
    header table { width: 100%; }
    header img { height: 11mm; }
    header .titulo { font-size: 12pt; font-weight: bold; color: #0b1426; }
    header .sub { font-size: 8pt; color: #5f6b7e; }
    footer { position: fixed; bottom: -13mm; left: 0; right: 0; height: 8mm; font-size: 7.5pt; color: #8a95a6; border-top: 1px solid #e2e7ef; padding-top: 2mm; }
    footer .pagina:after { content: "Página " counter(page); } /* Dompdf no calcula el total de páginas en CSS */

    h1 { font-size: 17pt; margin: 0 0 1mm; color: #0b1426; }
    h2 { font-size: 11pt; margin: 7mm 0 2.5mm; color: #0b1426; padding-left: 2.5mm; border-left: 3px solid #3b6fe8; }
    .intro { color: #5f6b7e; margin: 0 0 5mm; }

    /* Tarjetas de totales (tabla de 4 columnas) */
    .totales { width: 100%; border-collapse: separate; border-spacing: 2.5mm 0; margin: 0 -2.5mm; }
    .totales td { width: 25%; padding: 3.5mm; border: 1px solid #e2e7ef; border-radius: 2mm; background: #f6f8fb; vertical-align: top; }
    .totales .numero { font-size: 19pt; font-weight: bold; color: #0b1426; }
    .totales .etiqueta { font-size: 7pt; text-transform: uppercase; letter-spacing: .5pt; color: #5f6b7e; }
    .totales .detalle { font-size: 7.5pt; color: #5f6b7e; }

    /* Tablas de datos */
    table.datos { width: 100%; border-collapse: collapse; }
    table.datos th { background: #0b1426; color: #fff; font-size: 7.5pt; text-transform: uppercase; letter-spacing: .4pt; text-align: left; padding: 2mm 2.5mm; }
    table.datos td { padding: 2mm 2.5mm; border-bottom: 1px solid #e2e7ef; }
    table.datos tr:nth-child(even) td { background: #f6f8fb; }
    .vacio { color: #8a95a6; font-style: italic; }

    .estado { padding: .6mm 2mm; border-radius: 1.5mm; font-size: 7.5pt; font-weight: bold; }
    .en_vivo { background: #fde2e1; color: #c0392b; }
    .programada { background: #fdf0d5; color: #9a5f00; }
    .finalizada, .inactiva { background: #e9edf2; color: #5f6b7e; }
    .activa { background: #dcf5e6; color: #1e8449; }
    .derecha, table.datos th.derecha { text-align: right; }
</style>
</head>
<body>
<header>
    <table>
        <tr>
            <td style="width: 14mm;"><?php if ($logo): ?><img src="<?= $logo ?>" alt=""><?php endif; ?></td>
            <td>
                <div class="titulo">Iglesia de Dios de la Profecía · Distrito 2</div>
                <div class="sub">Sacaba · Cochabamba, Bolivia</div>
            </td>
            <td class="derecha sub">Reporte del sistema web<br>IDP D-2</td>
        </tr>
    </table>
</header>

<footer>
    <table style="width: 100%;">
        <tr>
            <td>Generado el <?= $e($fecha) ?> por <?= $e($generadoPor) ?></td>
            <td class="derecha pagina"></td>
        </tr>
    </table>
</footer>

<main>
    <h1>Resumen del distrito</h1>
    <p class="intro">Estado actual de las iglesias, zonas y transmisiones registradas en el sistema.</p>

    <table class="totales">
        <tr>
            <td>
                <div class="etiqueta">Iglesias</div>
                <div class="numero"><?= (int) ($totales['iglesias_activas'] ?? 0) + (int) ($totales['iglesias_inactivas'] ?? 0) ?></div>
                <?php $act = (int) ($totales['iglesias_activas'] ?? 0); $inact = (int) ($totales['iglesias_inactivas'] ?? 0); ?>
                <div class="detalle"><?= $act ?> activa<?= $act === 1 ? '' : 's' ?> · <?= $inact ?> inactiva<?= $inact === 1 ? '' : 's' ?></div>
            </td>
            <td>
                <div class="etiqueta">Zonas</div>
                <div class="numero"><?= (int) ($totales['zonas'] ?? 0) ?></div>
                <div class="detalle">Sectores del distrito</div>
            </td>
            <td>
                <div class="etiqueta">Transmisiones</div>
                <div class="numero"><?= (int) $tx['en_vivo'] + (int) $tx['programada'] + (int) $tx['finalizada'] ?></div>
                <div class="detalle"><?= (int) $tx['programada'] ?> programadas · <?= (int) $tx['finalizada'] ?> finalizadas<?= $tx['en_vivo'] ? ' · '.(int) $tx['en_vivo'].' en vivo' : '' ?></div>
            </td>
            <td>
                <div class="etiqueta">Usuarios</div>
                <div class="numero"><?= (int) ($totales['usuarios'] ?? 0) ?></div>
                <div class="detalle">Cuentas del sistema</div>
            </td>
        </tr>
    </table>

    <h2>Iglesias por zona</h2>
    <table class="datos">
        <thead><tr><th>Zona</th><th class="derecha">Iglesias</th></tr></thead>
        <tbody>
        <?php if (!$porZona): ?>
            <tr><td colspan="2" class="vacio">No hay zonas registradas.</td></tr>
        <?php endif; ?>
        <?php foreach ($porZona as $z): ?>
            <tr><td><?= $e($z['zona']) ?></td><td class="derecha"><?= (int) $z['iglesias'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Directorio de iglesias</h2>
    <table class="datos">
        <thead><tr><th>Iglesia</th><th>Zona</th><th>Pastor</th><th>Teléfono</th><th>Estado</th></tr></thead>
        <tbody>
        <?php if (!$iglesias): ?>
            <tr><td colspan="5" class="vacio">No hay iglesias registradas.</td></tr>
        <?php endif; ?>
        <?php foreach ($iglesias as $i): ?>
            <tr>
                <td><strong><?= $e($i['nombre']) ?></strong></td>
                <td><?= $e($i['zona'] ?? '') ?></td>
                <td><?= $e($i['pastor'] ?: '—') ?></td>
                <td><?= $e($i['telefono'] ?: '—') ?></td>
                <td><span class="estado <?= !empty($i['activa']) ? 'activa' : 'inactiva' ?>"><?= !empty($i['activa']) ? 'Activa' : 'Inactiva' ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Transmisiones recientes</h2>
    <table class="datos">
        <thead><tr><th>Transmisión</th><th>Iglesia</th><th>Fecha y hora</th><th>Estado</th></tr></thead>
        <tbody>
        <?php if (!$transmisiones): ?>
            <tr><td colspan="4" class="vacio">No hay transmisiones registradas.</td></tr>
        <?php endif; ?>
        <?php foreach ($transmisiones as $t): ?>
            <tr>
                <td><strong><?= $e($t['titulo']) ?></strong></td>
                <td><?= $e($t['iglesia']) ?></td>
                <td><?= $e(date('d/m/Y H:i', strtotime($t['fecha_hora']))) ?></td>
                <td><span class="estado <?= $e($t['estado']) ?>"><?= $e($etiquetas[$t['estado']] ?? $t['estado']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
