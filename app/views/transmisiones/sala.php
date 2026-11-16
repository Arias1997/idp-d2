<?php
// ==========================================================
// Sala de transmisiones (ruta pública: index.php?r=transmisiones)
// Recibe del controlador: $rows (las mismas transmisiones que usa inicio).
// ==========================================================
$rows = isset($rows) && is_array($rows) ? $rows : [];

// ---------- Enlaces de la barra inferior: EDITA AQUÍ ----------
$plataformas = [
    // Pestaña "En vivo" de la página de Facebook (el directo actual aparece primero)
    'facebook' => 'https://www.facebook.com/profile.php?id=61552980030797&sk=live_videos',
    // Abre el live de TikTok si la cuenta está transmitiendo; si no, muestra el perfil
    'tiktok' => 'https://www.tiktok.com/@idp_distrito2/live',
];
// WhatsApp para pedidos de oración (solo dígitos con código de país, ej. 59171234567).
// Si queda vacío, el botón lleva al chat del video en YouTube.
$whatsappOracion = '';

// ---------- Qué transmisión mostrar ----------
// 1) La que está en vivo  2) la próxima programada  3) la última finalizada
$destacada = null;
$programadas = [];
foreach ($rows as $t) {
    if ($t->estado === 'en_vivo' && !$destacada) $destacada = $t;
    if ($t->estado === 'programada') $programadas[] = $t;
}
if (!$destacada && $programadas) {
    usort($programadas, fn($a, $b) => strcmp($a->fecha_hora, $b->fecha_hora));
    $destacada = $programadas[0];
}
if (!$destacada) {
    foreach ($rows as $t) {
        if ($t->estado === 'finalizada') { $destacada = $t; break; } // Ya vienen de la más reciente a la más antigua
    }
}

$estado = $destacada->estado ?? null;
$etiquetas = ['en_vivo' => 'En vivo', 'programada' => 'Programada', 'finalizada' => 'Finalizada'];
$videoId = $destacada->youtube_id ?? null;
$enlaceYoutube = $videoId ? 'https://www.youtube.com/watch?v='.$videoId : null;
$enlaceFacebook = ($destacada->facebook ?? '') !== '' ? $destacada->facebook : $plataformas['facebook'];
$enlaceTiktok = ($destacada->tiktok ?? '') !== '' ? $destacada->tiktok : $plataformas['tiktok'];
$enlaceOracion = $whatsappOracion !== ''
    ? 'https://wa.me/'.$whatsappOracion.'?text='.rawurlencode('Hola, quisiera pedir oración.')
    : $enlaceYoutube;
$fecha = $destacada ? strtotime($destacada->fecha_hora) : null;
?>

<section class="sala" aria-labelledby="titulo-sala">
    <!-- ===== Cabecera: título y estado ===== -->
    <div class="sala-cabecera">
        <div>
            <div class="sala-eyebrow">Señal oficial en directo · Distrito 2 Sacaba</div>
            <h1 id="titulo-sala"><?= $destacada ? htmlspecialchars($destacada->titulo) : 'Sala de transmisiones' ?></h1>
        </div>
        <div class="sala-estados">
            <?php if ($estado): ?>
                <span class="sala-pill <?= htmlspecialchars($estado) ?>">
                    <?php if ($estado === 'en_vivo'): ?><span class="punto-vivo" aria-hidden="true"></span><?php endif; ?>
                    <?= htmlspecialchars($etiquetas[$estado] ?? $estado) ?>
                </span>
            <?php endif; ?>
            <?php if ($destacada): ?>
                <span class="sala-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                    <?= htmlspecialchars($destacada->iglesia) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== Pantalla ===== -->
    <div class="sala-pantalla<?= $estado === 'en_vivo' ? ' es-vivo' : '' ?>">
        <?php if ($videoId): ?>
            <iframe src="https://www.youtube-nocookie.com/embed/<?= htmlspecialchars($videoId) ?>" title="<?= htmlspecialchars($destacada->titulo) ?>" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
        <?php else: ?>
            <!-- Escenario con luces cuando no hay video -->
            <div class="sala-escenario">
                <span class="insignia"><?= $destacada ? htmlspecialchars($destacada->iglesia) : 'Iglesia de Dios de la Profecía · D2' ?></span>
                <p class="sala-escenario-titulo"><?= $destacada ? '«'.htmlspecialchars($destacada->titulo).'»' : 'Pronto estaremos en vivo' ?></p>
                <p class="sala-escenario-sub">
                    <?php if ($destacada): ?>
                        <?= htmlspecialchars(date('d/m/Y · H:i', $fecha)) ?> (Bolivia)
                    <?php else: ?>
                        Vuelve a esta sala en el horario de nuestros cultos.
                    <?php endif; ?>
                </p>
                <span class="sala-escenario-icono" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="2"/><path d="M7.8 7.8a6 6 0 0 0 0 8.4m8.4 0a6 6 0 0 0 0-8.4M4.9 4.9a10 10 0 0 0 0 14.2m14.2 0a10 10 0 0 0 0-14.2"/></svg>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($estado === 'programada'): ?>
        <p class="sala-aviso">Esta transmisión comienza el <strong><?= htmlspecialchars(date('d/m/Y', $fecha)) ?></strong> a las <strong><?= htmlspecialchars(date('H:i', $fecha)) ?></strong> (hora de Bolivia).</p>
    <?php elseif ($estado === 'finalizada'): ?>
        <p class="sala-aviso">Ahora no estamos en vivo. Puedes ver la última transmisión del <?= htmlspecialchars(date('d/m/Y', $fecha)) ?>.</p>
    <?php endif; ?>

    <!-- ===== Barra inferior ===== -->
    <div class="sala-barra">
        <div class="sala-plataformas">
            <span>Disfruta también en:</span>
            <a class="plataforma facebook" href="<?= htmlspecialchars($enlaceFacebook) ?>" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-1.6 19.9V14.9H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.5 2.9h-2.3v7A10 10 0 0 0 12 2z"/></svg>
                Facebook Live
            </a>
            <a class="plataforma tiktok" href="<?= htmlspecialchars($enlaceTiktok) ?>" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.5 3a4.6 4.6 0 0 0 4 4v3.2a7.7 7.7 0 0 1-4-1.2v6.3A6.2 6.2 0 1 1 10.3 9v3.3a3 3 0 1 0 2.9 3V3z"/></svg>
                TikTok Live
            </a>
            <?php if ($enlaceYoutube): ?>
                <a class="plataforma youtube" href="<?= htmlspecialchars($enlaceYoutube) ?>" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 8.2a3 3 0 0 0-2.1-2.1C18 5.6 12 5.6 12 5.6s-6 0-7.9.5A3 3 0 0 0 2 8.2 31 31 0 0 0 1.6 12a31 31 0 0 0 .4 3.8 3 3 0 0 0 2.1 2.1c1.9.5 7.9.5 7.9.5s6 0 7.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .4-3.8 31 31 0 0 0-.4-3.8zM10 15V9l5.2 3z"/></svg>
                    YouTube
                </a>
            <?php endif; ?>
        </div>
        <div class="sala-acciones">
            <?php if ($enlaceOracion): ?>
                <a class="btn btn-oro" href="<?= htmlspecialchars($enlaceOracion) ?>" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8z"/></svg>
                    Pedir oración en vivo
                </a>
            <?php endif; ?>
            <button class="btn btn-claro" type="button" data-compartir data-titulo="<?= htmlspecialchars($destacada->titulo ?? 'Sala de transmisiones IDP D-2') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/></svg>
                <span>Compartir</span>
            </button>
        </div>
    </div>
</section>
<script src="assets/js/sala.js" defer></script>
