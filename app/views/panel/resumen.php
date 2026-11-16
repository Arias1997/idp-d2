<?php
// Datos de PanelRepository: totales, enVivo, proximas e incompletas.
$e = static fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$tx = $totales['transmisiones'];
$tarjetas = [
    ['Iglesias', $totales['iglesias_activas'] + $totales['iglesias_inactivas'],
        $totales['iglesias_activas'].' activas · '.$totales['iglesias_inactivas'].' inactivas', 'azul'],
    ['Zonas', $totales['zonas'], 'Sectores registrados en el distrito', 'morado'],
    ['Transmisiones', array_sum($tx), $tx['en_vivo'].' en vivo · '.$tx['programada'].' programadas · '.$tx['finalizada'].' finalizadas', 'rojo'],
    ['Usuarios', $totales['usuarios'], 'Cuentas registradas en el sistema', 'verde'],
];
?>
<section class="admin-bienvenida">
    <div>
        <span class="admin-insignia"><span class="admin-punto" aria-hidden="true"></span>Resumen del distrito</span>
        <h1>Bienvenido, <?= $e($_SESSION['usuario_nombre'] ?? 'Administrador') ?></h1>
        <p>Consulta las iglesias, las próximas transmisiones y los datos que necesitan atención.</p>
    </div>
    <div class="admin-bienvenida-acciones">
        <a class="btn admin-btn-oro" href="index.php?r=admin">Gestionar transmisiones</a>
        <a class="btn admin-btn-linea" href="index.php?r=reporte_resumen" target="_blank" rel="noopener">Exportar PDF</a>
    </div>
</section>

<section class="row g-3" aria-label="Totales del sistema">
    <?php foreach ($tarjetas as [$titulo, $cantidad, $detalle, $color]): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="admin-stat h-100">
                <div class="admin-stat-cabecera">
                    <span><?= $e($titulo) ?></span>
                    <span class="admin-stat-icono <?= $e($color) ?>" aria-hidden="true">#</span>
                </div>
                <strong><?= (int) $cantidad ?></strong>
                <p><?= $e($detalle) ?></p>
            </article>
        </div>
    <?php endforeach; ?>
</section>

<section class="admin-panel">
    <div class="admin-tabla-cabecera">
        <div><h2>En vivo ahora</h2><p class="admin-panel-sub">Transmisiones marcadas como en vivo.</p></div>
        <a class="btn admin-btn-linea" href="index.php?r=transmisiones">Abrir sala pública</a>
    </div>
    <?php if (!$enVivo): ?>
        <p class="admin-vacio">No hay transmisiones en vivo en este momento.</p>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($enVivo as $transmision): ?>
                <article class="col-12 col-xl-6">
                    <h3 class="h5"><?= $e($transmision['titulo']) ?></h3>
                    <p class="admin-ayuda"><?= $e($transmision['iglesia']) ?> · <?= $e(date('d/m/Y H:i', strtotime($transmision['fecha_hora']))) ?></p>
                    <?php if ($transmision['youtube_id']): ?>
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.youtube-nocookie.com/embed/<?= $e($transmision['youtube_id']) ?>"
                                title="<?= $e($transmision['titulo']) ?>" loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <p class="admin-ayuda">Esta transmisión necesita un enlace válido de YouTube.</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <section class="admin-panel admin-tabla-panel h-100">
            <div class="admin-tabla-cabecera">
                <div><h2>Próximas transmisiones</h2><p class="admin-panel-sub">Las cinco programadas más cercanas, en horario de Bolivia.</p></div>
            </div>
            <?php if (!$proximas): ?>
                <p class="admin-vacio">No hay transmisiones futuras programadas.</p>
            <?php else: ?>
                <div class="admin-tabla-scroll">
                    <table class="admin-tabla">
                        <thead><tr><th>Transmisión</th><th>Iglesia</th><th>Fecha y hora</th></tr></thead>
                        <tbody>
                        <?php foreach ($proximas as $transmision): ?>
                            <tr>
                                <td><strong><?= $e($transmision['titulo']) ?></strong></td>
                                <td><?= $e($transmision['iglesia']) ?></td>
                                <td><?= $e(date('d/m/Y H:i', strtotime($transmision['fecha_hora']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <div class="col-12 col-xl-5">
        <section class="admin-panel h-100 p-3 p-sm-4">
            <h2>Datos por completar</h2>
            <p class="admin-panel-sub">Iglesias sin ubicación completa o sin horarios.</p>
            <?php if (!$incompletas): ?>
                <p class="admin-vacio">Todas las iglesias tienen ubicación y horarios registrados.</p>
            <?php else: ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($incompletas as $iglesia): ?>
                        <?php
                        $faltantes = [];
                        if ($iglesia['latitud'] === null || $iglesia['longitud'] === null) $faltantes[] = 'ubicación';
                        if (trim((string) $iglesia['horarios']) === '') $faltantes[] = 'horarios';
                        ?>
                        <li class="py-2">
                            <a class="admin-enlace" href="index.php?r=iglesias&amp;editar=<?= (int) $iglesia['id'] ?>"><?= $e($iglesia['nombre']) ?></a>
                            <small class="d-block admin-ayuda">Falta: <?= $e(implode(' y ', $faltantes)) ?>.</small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</div>

<section class="admin-panel p-3 p-sm-4">
    <h2>Accesos rápidos</h2>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn admin-btn-linea" href="index.php?r=iglesias">Iglesias</a>
        <a class="btn admin-btn-linea" href="index.php?r=zonas">Zonas</a>
        <a class="btn admin-btn-linea" href="index.php?r=usuarios">Usuarios</a>
    </div>
</section>
