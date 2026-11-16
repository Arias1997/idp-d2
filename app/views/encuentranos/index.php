<?php
// ==========================================================
// Encuéntranos (ruta pública: index.php?r=encuentranos)
// Recibe del controlador:
//   $iglesias   → nombre, direccion, latitud, longitud, pastor, telefono, horarios, zona
//   $mapaConfig → config/maps.php (si no llega, se carga aquí)
// ==========================================================
$iglesias = isset($iglesias) && is_array($iglesias) ? $iglesias : [];
$mapaConfig = $mapaConfig ?? require __DIR__.'/../../../config/maps.php';

// ---------- Datos generales del distrito: EDITA AQUÍ ----------
// Son textos fijos de la página (no vienen de la base de datos).
$distrito = [
    'direccion' => 'Sacaba, Cochabamba – Bolivia',
    'referencia' => 'Todas nuestras congregaciones están en el municipio de Sacaba. Elige la más cercana en el mapa.',
    // Enlace general del botón "Abrir en Google Maps" (debajo del mapa).
    // Al elegir una iglesia en el mapa, el botón cambia a la ubicación de esa iglesia.
    'google_maps' => 'https://maps.app.goo.gl/Cjyq2H9pgatGtCvA8',
    // Cambia estos horarios por los reales del distrito.
    'horarios' => [
        ['Domingo', 'Escuela Dominical', '10:00'],
        ['Domingo', 'Reunión General', '17:30'],
        ['Martes', 'Noche de Oración', '19:30'],
        ['Jueves', 'Noche de Estudio Bíblico', '17:30'],
        ['Sábado', 'Noche de Jóvenes', '19:30'],
    ],
    'whatsapp' => '', // Número con código de país, solo dígitos. Ej.: 59171234567
    'correo' => '',   // Ej.: contacto@idpbolivia.org
];

// ---------- Fotos del carrusel del hero ----------
// Sube cualquier .jpg, .png o .webp a public/assets/img/encuentranos/ y aparecerá aquí.
$carrusel = [];
foreach (glob(__DIR__.'/../../../public/assets/img/encuentranos/*') ?: [] as $archivo) {
    if (preg_match('/\.(jpe?g|png|webp)$/i', $archivo)) {
        $carrusel[] = 'assets/img/encuentranos/'.rawurlencode(basename($archivo));
    }
}

// ---------- Fotos de las tarjetas de iglesias ----------
// Carpeta: public/assets/img/iglesias/
//   - Foto propia: nombre de la iglesia en minúsculas, sin tildes y con guiones.
//     Ej.: "Iglesia Central Sacaba" → iglesia-central-sacaba.jpg (o .jpeg, .png, .webp)
//   - Si no existe, se usa generica.jpg (o .jpeg, .png, .webp) para todas.
$nombreArchivo = static function (string $nombre): string {
    $nombre = strtr(trim($nombre), [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
    ]);
    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($nombre)), '-');
};
$buscarFoto = static function (string $base) use ($ipdImg): ?string {
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
        if ($ruta = $ipdImg('iglesias/'.$base.'.'.$extension)) return $ruta;
    }
    return null;
};
$fotoGenerica = $buscarFoto('generica');

// ---------- Ayudas para las tarjetas ----------
$iniciales = static function (string $nombre): string {
    $nombre = preg_replace('/^(pra?\.|pastora?|hno\.|hna\.)\s+/iu', '', trim($nombre));
    preg_match_all('/(?<!\p{L})\p{L}/u', $nombre, $letras);
    $texto = implode('', array_slice($letras[0], 0, 2));
    return function_exists('mb_strtoupper') ? mb_strtoupper($texto) : strtoupper($texto);
};
// Convierte el texto de horarios (una línea por culto) en filas [día, hora].
// Formatos aceptados: "Domingo - 10:00", "Domingo: 10:00", "Domingo | 10:00".
$filasHorario = static function (?string $texto): array {
    $filas = [];
    foreach (preg_split('/\R/u', (string) $texto) as $linea) {
        $linea = trim($linea);
        if ($linea === '') continue;
        // Separa "Día - 19:30", "Día: 19:30" o simplemente "Día 19:30"
        if (preg_match('/^(.*?)(?:\s*(?:-|–|\||:)\s*|\s+)(\d{1,2}[:.]\d{2}.*)$/u', $linea, $m)) {
            $filas[] = [$m[1], $m[2]];
        } else {
            $filas[] = [$linea, ''];
        }
    }
    return $filas;
};
// Enlace de WhatsApp: números bolivianos de 8 dígitos reciben el código 591.
$enlaceWhatsapp = static function (?string $telefono): ?string {
    $digitos = preg_replace('/\D/', '', (string) $telefono);
    if (strlen($digitos) === 8) $digitos = '591'.$digitos;
    return strlen($digitos) >= 11 ? 'https://wa.me/'.$digitos : null;
};

// Zonas para los filtros y puntos para el mapa.
$zonas = array_values(array_unique(array_map(fn($i) => $i['zona'], $iglesias)));
sort($zonas);
$puntosMapa = [];
foreach ($iglesias as $indice => $i) {
    if ($i['latitud'] !== null && $i['longitud'] !== null) {
        $puntosMapa[] = [
            'indice' => $indice, 'nombre' => $i['nombre'], 'direccion' => $i['direccion'], 'zona' => $i['zona'],
            'lat' => (float) $i['latitud'], 'lng' => (float) $i['longitud'],
        ];
    }
}
$iconoPin = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>';
?>

<!-- ========== HERO CON CARRUSEL Y TARJETAS ========== -->
<section class="enc-hero">
    <div class="enc-carrusel" aria-hidden="true">
        <?php foreach ($carrusel as $k => $foto): ?>
            <div class="enc-slide<?= $k === 0 ? ' activa' : '' ?>" style="background-image: url('<?= htmlspecialchars($foto) ?>')"></div>
        <?php endforeach; ?>
    </div>

    <div class="enc-hero-contenido">
        <?php if ($ipdLogo): ?>
            <img class="enc-logo" src="<?= htmlspecialchars($ipdLogo) ?>" alt="">
        <?php endif; ?>
        <div class="insignia">Casa de oración · Sacaba D-2</div>
        <h1>Te esperamos con los <em>brazos abiertos</em></h1>
        <p class="versiculo"><q>Venid a mí todos los que estáis trabajados y cargados, y yo os haré descansar</q>. <cite>Mateo 11:28</cite></p>
        <p class="enc-hero-texto">Ya sea que busques una congregación cerca de tu casa o quieras visitarnos por primera vez, aquí encontrarás dónde y cuándo reunirnos.</p>
        <div class="hero-acciones">
            <a class="btn btn-oro" href="#seccion-mapa">
                <?= $iconoPin ?>
                Ver en el mapa
            </a>
            <a class="btn btn-claro" href="#iglesias">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                Horarios de reunión
            </a>
        </div>
    </div>

    <div class="enc-tarjetas">
        <!-- Dirección -->
        <article class="vidrio">
            <div class="vidrio-cabecera">
                <span class="vidrio-icono"><?= $iconoPin ?></span>
                <span class="vidrio-etiqueta">Distrito 2</span>
            </div>
            <h2>Dirección y accesos</h2>
            <p><?= htmlspecialchars($distrito['direccion']) ?></p>
            <p>Ven con tu Familia.</p>
            <div class="vidrio-nota">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
                <span><?= htmlspecialchars($distrito['referencia']) ?></span>
            </div>
            <a class="vidrio-pie" href="#seccion-mapa">Explorar mapa detallado →</a>
        </article>

        <!-- Horarios -->
        <article class="vidrio">
            <div class="vidrio-cabecera">
                <span class="vidrio-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                <span class="vidrio-etiqueta verde">Cultos activos</span>
            </div>
            <h2>Horarios de reunión D-2</h2>
            <ul class="vidrio-horarios">
                <?php foreach ($distrito['horarios'] as [$dia, $actividad, $hora]): ?>
                    <li><span><?= htmlspecialchars($dia) ?> · <?= htmlspecialchars($actividad) ?></span><strong><?= htmlspecialchars($hora) ?></strong></li>
                <?php endforeach; ?>
            </ul>
            <a class="vidrio-pie" href="index.php?r=transmisiones">Ver transmisiones en vivo →</a>
        </article>

        <!-- Contacto -->
        <article class="vidrio">
            <div class="vidrio-cabecera">
                <span class="vidrio-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2z"/></svg></span>
                <span class="vidrio-etiqueta">Pastoral y consejería</span>
            </div>
            <h2>Atención y contacto</h2>
            <p>¿Necesitas apoyo espiritual o tienes dudas para tu primera visita? Estamos para acompañarte.</p>
            <?php if ($distrito['whatsapp'] !== ''): ?>
                <a class="vidrio-contacto" href="https://wa.me/<?= htmlspecialchars($distrito['whatsapp']) ?>" target="_blank" rel="noopener noreferrer">
                    WhatsApp pastoral directo <span aria-hidden="true">→</span>
                </a>
            <?php endif; ?>
            <?php if ($distrito['correo'] !== ''): ?>
                <p>Correo: <a href="mailto:<?= htmlspecialchars($distrito['correo']) ?>"><?= htmlspecialchars($distrito['correo']) ?></a></p>
            <?php endif; ?>
            <?php if ($distrito['whatsapp'] === '' && $distrito['correo'] === ''): ?>
                <p>Acércate a cualquiera de nuestras iglesias: los pastores locales te atenderán con gusto.</p>
            <?php endif; ?>
            <a class="vidrio-pie" href="#iglesias">Contactar a un pastor →</a>
        </article>
    </div>
</section>

<!-- ========== NUESTRAS IGLESIAS ========== -->
<section class="seccion" id="iglesias">
    <header class="titulo-seccion">
        <div class="eyebrow">Distrito 2 · Sacaba</div>
        <h2>Nuestras iglesias y congregaciones</h2>
        <p>Conoce los templos y puntos de encuentro donde se reúne nuestra comunidad. Siempre hay un lugar para ti.</p>
    </header>

    <?php if (!$iglesias): ?>
        <div class="card empty">Muy pronto publicaremos nuestras iglesias.</div>
    <?php else: ?>
        <?php if (count($zonas) > 1): ?>
            <div class="enc-filtros" role="group" aria-label="Filtrar por zona">
                <button class="enc-filtro" type="button" data-zona="" aria-pressed="true">Todas las congregaciones</button>
                <?php foreach ($zonas as $zona): ?>
                    <button class="enc-filtro" type="button" data-zona="<?= htmlspecialchars($zona) ?>" aria-pressed="false"><?= htmlspecialchars($zona) ?></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="enc-grid">
            <?php foreach ($iglesias as $indice => $i): ?>
                <?php
                $foto = $buscarFoto($nombreArchivo($i['nombre'])) ?? $fotoGenerica;
                $horarios = $filasHorario($i['horarios'] ?? null);
                $whatsapp = $enlaceWhatsapp($i['telefono'] ?? null);
                $tieneMapa = $i['latitud'] !== null && $i['longitud'] !== null;
                ?>
                <article class="enc-iglesia" data-zona="<?= htmlspecialchars($i['zona']) ?>">
                    <div class="enc-foto"<?= $foto ? ' style="background-image: url(\''.htmlspecialchars($foto).'\')"' : '' ?>>
                        <span class="enc-zona"><?= htmlspecialchars($i['zona']) ?></span>
                        <h3><?= htmlspecialchars($i['nombre']) ?></h3>
                        <p>Iglesia de Dios de la Profecía</p>
                    </div>

                    <div class="enc-cuerpo">
                        <?php if (!empty($i['pastor'])): ?>
                            <div class="enc-pastor">
                                <span class="enc-avatar" aria-hidden="true"><?= htmlspecialchars($iniciales($i['pastor'])) ?></span>
                                <div>
                                    <small>Pastor local</small>
                                    <strong><?= htmlspecialchars($i['pastor']) ?></strong>
                                    <?php if (!empty($i['telefono'])): ?>
                                        <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $i['telefono'])) ?>"><?= htmlspecialchars($i['telefono']) ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($horarios): ?>
                            <div>
                                <span class="enc-subtitulo">Horarios de culto</span>
                                <ul class="enc-horarios">
                                    <?php foreach ($horarios as [$dia, $hora]): ?>
                                        <li><span><?= htmlspecialchars($dia) ?></span><strong><?= htmlspecialchars($hora) ?></strong></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="enc-direccion">
                            <?= $iconoPin ?>
                            <span><?= htmlspecialchars($i['direccion']) ?></span>
                        </div>

                        <div class="enc-acciones">
                            <?php if ($whatsapp): ?>
                                <a class="btn btn-whatsapp" href="<?= htmlspecialchars($whatsapp) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                            <?php endif; ?>
                            <?php if ($tieneMapa): ?>
                                <button class="btn btn-oscuro" type="button" data-ver-mapa="<?= (int) $indice ?>">Ver en mapa →</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ========== CÓMO LLEGAR ========== -->
<section class="seccion enc-llegar" id="como-llegar">
    <header class="titulo-seccion">
        <div class="eyebrow">Visítanos</div>
        <h2>¿Cómo llegar a nuestra casa de oración?</h2>
        <p>Ubica la congregación más cercana a tu hogar y traza tu ruta desde cualquier punto de Sacaba.</p>
    </header>

    <div class="enc-llegar-grid" id="seccion-mapa">
        <div class="enc-mapa-tarjeta">
            <div class="enc-mapa-barra">
                <span>Mapa de congregaciones</span>
                <span><?= count($puntosMapa) ?> ubicacion<?= count($puntosMapa) === 1 ? '' : 'es' ?></span>
            </div>
            <?php if ($puntosMapa): ?>
                <div id="mapa-encuentranos" class="enc-mapa" role="region" aria-label="Mapa de iglesias"
                     data-iglesias="<?= htmlspecialchars(json_encode($puntosMapa, JSON_UNESCAPED_UNICODE)) ?>"
                     data-latitud="<?= htmlspecialchars((string) $mapaConfig['latitud_inicial']) ?>"
                     data-longitud="<?= htmlspecialchars((string) $mapaConfig['longitud_inicial']) ?>"
                     data-zoom="<?= (int) $mapaConfig['zoom_inicial'] ?>"
                     data-teselas="<?= htmlspecialchars($mapaConfig['url_mapa']) ?>"></div>
            <?php else: ?>
                <div class="enc-mapa enc-mapa-vacio">Pronto marcaremos nuestras iglesias en el mapa.</div>
            <?php endif; ?>
            <div class="enc-mapa-pie">
                <a class="btn btn-oscuro" id="abrir-google-maps" href="<?= htmlspecialchars($distrito['google_maps']) ?>" target="_blank" rel="noopener noreferrer">Abrir en Google Maps</a>
            </div>
        </div>

        <aside class="enc-visita">
            <div class="eyebrow">Primera visita</div>
            <h3>¿Qué esperar en tu primera visita?</h3>
            <p>Queremos que te sientas en casa desde el primer momento.</p>
            <ul class="enc-lista">
                <li><span class="enc-check" aria-hidden="true">✓</span><div><strong>Bienvenida cálida</strong>Hermanos de la congregación te recibirán y te ayudarán a ubicarte.</div></li>
                <li><span class="enc-check" aria-hidden="true">✓</span><div><strong>Alabanza y Palabra</strong>Un tiempo de adoración, oración y enseñanza bíblica.</div></li>
                <li><span class="enc-check" aria-hidden="true">✓</span><div><strong>Ven como eres</strong>No necesitas vestimenta especial, lo importante es tu presencia.</div></li>
                <li><span class="enc-check" aria-hidden="true">✓</span><div><strong>Para toda la familia</strong>Hay espacios para niños, jóvenes y adultos.</div></li>
            </ul>
            <div class="enc-cta">
                <p>¿Tienes preguntas antes de venir?</p>
                <a class="btn" href="#iglesias">Contáctanos</a>
            </div>
        </aside>
    </div>
</section>

<?php if ($puntosMapa): ?>
    <link rel="stylesheet" href="assets/vendor/leaflet/leaflet.css">
    <script src="assets/vendor/leaflet/leaflet.js" defer></script>
<?php endif; ?>
<script src="assets/js/encuentranos.js" defer></script>
