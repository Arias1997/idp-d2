<?php
// Valores de la iglesia. Para usar una imagen propia en lugar del icono,
// sube un PNG con el nombre indicado en public/assets/img/valores/ (se muestra sin borde).
$valores = [
    ['titulo' => 'Oración', 'imagen' => 'oracion.png', 'color' => '#5bb543',
     'texto' => 'La oración es el primero de los cinco valores centrales de 
        la Iglesia de Dios de la Profecía. La oración debe estar presente en todo, 
        formar toda actividad, empoderar todo acto de servicio y ministerio y permear 
        la obra de principio a fin.',
     'icono' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="13.5" cy="4.6" r="2.3"/><path d="M9.2 1.8c.7 0 1.2.5 1.2 1.2v5l1.6.6c.4-.2.9-.3 1.4-.3h.5c1.4 0 2.5 1.1 2.5 2.5v4.6h-1.7V22h-2.4v-6h-.8v6H9.1V12.4L8 11.3V3c0-.7.5-1.2 1.2-1.2z"/></svg>'],
    ['titulo' => 'Cosecha', 'imagen' => 'cosecha.png', 'color' => '#2563b5',
     'texto' => 'Sostenemos el llamado de Dios a la cosecha como un mandato y valor central, que no ha sido anulado ni tampoco se ha cumplido cabalmente. Ni lo será hasta que todos hayan escuchado.',
     'icono' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M7 4.5c1 2 .3 3.5 2 4.5s1 2.5 2.5 3-1 3 .5 4.5M21.5 11c-2 .5-3-.5-4 1s.5 3-.5 4.5-1.5 2-1.5 4"/></svg>'],
    ['titulo' => 'Liderazgo', 'imagen' => 'liderazgo.png', 'color' => '#e0393e',
     'texto' => 'Hemos distribuido recursos significativos para crear sistemas que cultiven una cultura de excelencia y produzca líderes competentes y adiestrados.',
     'icono' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="8.5" cy="7.5" r="3.5"/><circle cx="16.5" cy="8.5" r="3"/><path d="M1.5 20c0-4 3-6.5 7-6.5s7 2.5 7 6.5zM17 20c0-2.4-.8-4.4-2.2-5.7.6-.2 1.1-.3 1.7-.3 3.4 0 6 2.2 6 6z"/></svg>'],
    ['titulo' => 'Misión social', 'imagen' => 'mision-social.png', 'color' => '#7b3fa0',
     'texto' => 'Jesús sirvió. Él llama a que Sus seguidores le sigan. Por todo el mundo hay líderes, iglesias y miembros de la Iglesia de Dios de la Profecía sirviendo a sus comunidades de muchas maneras.',
     'icono' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5 1.5 11h3v10.5h15V11h3zm0 16.2s-4.2-2.5-4.2-5.2a2.2 2.2 0 0 1 4.2-1 2.2 2.2 0 0 1 4.2 1c0 2.7-4.2 5.2-4.2 5.2z"/></svg>'],
    ['titulo' => 'Mayordomía', 'imagen' => 'mayordomia.png', 'color' => '#ee7d1b',
     'texto' => 'La Iglesia afirma que todo pertenece a Dios, y que nosotros somos responsables de manejarlo bien, conforme a Sus deseos y propósitos.',
     'icono' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 1.5 6.5 8.5h2.8L5 14h3.2l-4 5.5h6.3V23h3v-3.5h6.3l-4-5.5H19l-4.3-5.5h2.8z"/></svg>'],
];

// Transmisiones en vivo: solo se muestran debajo del hero si alguna tiene estado en_vivo.
$enVivo = array_values(array_filter($rows, function ($t) {
    return $t->estado === 'en_vivo' && $t->youtube_id;
}));

// Próximas transmisiones: las programadas, de la más cercana a la más lejana (máximo 3).
$proximas = array_values(array_filter($rows, fn($t) => $t->estado === 'programada'));
usort($proximas, fn($a, $b) => strcmp($a->fecha_hora, $b->fecha_hora));
$proximas = array_slice($proximas, 0, 3);
$meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
$dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
?>

<!-- ========== HERO ========== -->
<section class="hero-inicio" id="inicio">
    <div class="hero-contenido">
        <div class="insignia">
            <?php if ($ipdLogo): ?><img src="<?= htmlspecialchars($ipdLogo) ?>" alt=""><?php else: ?><span class="insignia-punto" aria-hidden="true">✦</span><?php endif; ?>
            Iglesia de Dios de la Profecía · D2
        </div>
        <h1>Un lugar para <em>crecer en fe</em> y compartir esperanza.</h1>
        <p class="hero-texto">Somos una comunidad que ama a Dios y a las personas. Encuentra la iglesia más cercana en Sacaba o acompaña los cultos en vivo desde tu casa.</p>
        <p class="versiculo"><q>De modo que si alguno está en Cristo, nueva criatura es</q>. <cite>2 Corintios 5:17</cite></p>
        <div class="hero-acciones">
            <a class="btn btn-oro" href="index.php?r=encuentranos">
                Encuentra tu iglesia
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
            </a>
            <a class="btn btn-claro" href="<?= $enVivo ? '#en-vivo' : 'index.php?r=transmisiones' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="2"/><path d="M7.8 7.8a6 6 0 0 0 0 8.4m8.4 0a6 6 0 0 0 0-8.4M4.9 4.9a10 10 0 0 0 0 14.2m14.2 0a10 10 0 0 0 0-14.2"/></svg>
                Ver en vivo
            </a>
        </div>
    </div>
</section>

<!-- ========== EN VIVO AHORA (solo aparece si hay una transmisión en_vivo) ========== -->
<?php if ($enVivo): ?>
    <?php $vivo = $enVivo[0]; ?>
    <section class="vivo-ahora" id="en-vivo" aria-labelledby="titulo-vivo">
        <div class="vivo-ahora-player player">
            <iframe src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($vivo->youtube_id) ?>" title="<?= htmlspecialchars($vivo->titulo) ?>" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
        </div>
        <div class="vivo-ahora-info">
            <div class="etiqueta-vivo"><span class="punto-vivo" aria-hidden="true"></span> En vivo ahora</div>
            <h2 id="titulo-vivo"><?= htmlspecialchars($vivo->titulo) ?></h2>
            <p><?= htmlspecialchars($vivo->iglesia) ?></p>
            <?php if (count($enVivo) > 1): ?>
                <p>Hay <?= count($enVivo) ?> transmisiones en vivo en este momento.</p>
            <?php endif; ?>
            <div class="hero-acciones">
                <a class="btn btn-oro" href="https://www.youtube.com/watch?v=<?= htmlspecialchars($vivo->youtube_id) ?>" target="_blank" rel="noopener noreferrer">Ver en YouTube</a>
                <a class="btn btn-claro" href="index.php?r=transmisiones">Sala de transmisiones</a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ========== PRÓXIMAS TRANSMISIONES (solo si hay programadas) ========== -->
<?php if ($proximas): ?>
    <section class="proximas" aria-labelledby="titulo-proximas">
        <div class="proximas-cabecera">
            <div>
                <div class="eyebrow">En Vivo en corto</div>
                <h2 id="titulo-proximas">Próximas transmisiones</h2>
            </div>
            <a class="enlace-ruta" href="index.php?r=transmisiones">Ir a la sala de transmisiones →</a>
        </div>
        <div class="proximas-lista">
            <?php foreach ($proximas as $p): ?>
                <?php
                $momento = strtotime($p->fecha_hora);
                $yaPaso = $momento < time();
                ?>
                <article class="proxima">
                    <div class="proxima-fecha" aria-hidden="true">
                        <strong><?= date('d', $momento) ?></strong>
                        <span><?= $meses[(int) date('n', $momento) - 1] ?></span>
                    </div>
                    <div class="proxima-info">
                        <span class="proxima-estado<?= $yaPaso ? ' por-comenzar' : '' ?>"><?= $yaPaso ? 'Por comenzar' : 'Programada' ?></span>
                        <h3><?= htmlspecialchars($p->titulo) ?></h3>
                        <p>
                            <?= $dias[(int) date('w', $momento)] ?> <?= date('d/m', $momento) ?> · <?= date('H:i', $momento) ?> (Bolivia)<br>
                            <?= htmlspecialchars($p->iglesia) ?>
                        </p>
                        <?php if ($p->youtube_id): ?>
                            <a class="enlace-ruta" href="https://www.youtube.com/watch?v=<?= htmlspecialchars($p->youtube_id) ?>" target="_blank" rel="noopener noreferrer">
                                Activar recordatorio en YouTube
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- ========== SOBRE NOSOTROS: VALORES ========== -->
<section class="seccion valores" id="nosotros">
    <header class="titulo-seccion">
        <div class="eyebrow">Sobre nosotros</div>
        <h2>Nuestros valores</h2>
    </header>
    <div class="valores-grid">
        <?php foreach ($valores as $v): ?>
            <?php $imagenValor = $ipdImg('valores/'.$v['imagen']); ?>
            <article class="valor" style="--color: <?= htmlspecialchars($v['color']) ?>">
                <?php if ($imagenValor): ?>
                    <img class="valor-img" src="<?= htmlspecialchars($imagenValor) ?>" alt="">
                <?php else: ?>
                    <div class="valor-icono"><?= $v['icono'] ?></div>
                <?php endif; ?>
                <h3><?= htmlspecialchars($v['titulo']) ?></h3>
                <p><?= htmlspecialchars($v['texto']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- ========== PARALLAX: MARCHA EVANGÉLICA ========== -->
<section class="parallax-marcha" aria-labelledby="titulo-marcha">
    <svg class="ola" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true"><path d="M0 0h1440v22c-160 26-330 34-520 18S560 6 380 16 120 46 0 30z"/></svg>
    <div class="parallax-contenido">
        <p class="acento">Un lugar para ti</p>
        <h2 id="titulo-marcha">Somos una iglesia que cree en Jesús como único Salvador.</h2>
        <p>Amamos a Dios y amamos a las personas.</p>
        <a class="btn btn-capsula-clara" href="index.php?r=encuentranos">Encuentra tu iglesia</a>
    </div>
</section>

<!-- ========== MENSAJE ========== -->
<section class="seccion mensaje" aria-labelledby="titulo-mensaje">
    <div class="mensaje-logo">
        <?php if ($ipdLogo): ?>
            <img src="<?= htmlspecialchars($ipdLogo) ?>" alt="Logo IDP D-2">
        <?php else: ?>
            <span aria-hidden="true">IDP . D-2</span>
        <?php endif; ?>
    </div>
    <div class="mensaje-texto">
        <h2 id="titulo-mensaje">Un mensaje para nuestra comunidad</h2>
        <div class="mensaje-autor">Supervisor IDP · D-2</div>
        <blockquote>«Amada iglesia, bienvenidos a este espacio de comunicación y encuentro. Somos una comunidad comprometida con la fe cristiana y con compartir el amor y la esperanza que nos trae el Evangelio de Jesucristo».</blockquote>
    </div>
</section>

