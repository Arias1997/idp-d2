<?php
// Módulo de gestión de transmisiones; la bienvenida general está en Resumen.
// Recibe: $rows (transmisiones), $iglesias (id, nombre), $formulario (datos si hubo error).
// IMPORTANTE: no cambiar name="" ni los id del formulario (los usa el backend y la función editar()).
$conteo = ['en_vivo' => 0, 'programada' => 0, 'finalizada' => 0];
foreach ($rows as $t) {
    if (isset($conteo[$t->estado])) $conteo[$t->estado]++;
}
$etiquetas = ['en_vivo' => 'En vivo', 'programada' => 'Programada', 'finalizada' => 'Finalizada'];
$editando = !empty($formulario['id']);
?>

<!-- ===== Bienvenida ===== -->
<section class="admin-encabezado">
    <div>
        <span class="admin-insignia"><span class="admin-punto" aria-hidden="true"></span>Sistema de transmisiones · Distrito 2</span>
        <h1>Transmisiones</h1>
        <p>Publica los directos, completa sus enlaces y organiza las transmisiones de las iglesias.</p>
    </div>
    <div class="admin-bienvenida-acciones">
        <button class="btn admin-btn-oro" type="button" data-nueva-transmision>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Crear nueva transmisión
        </button>
        <a class="btn admin-btn-azul" href="index.php?r=iglesias">
            Gestionar iglesias
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5"/></svg>
        </a>
    </div>
</section>

<?php if (!empty($_SESSION['exito'])): ?>
    <div class="admin-aviso exito" role="status"><?= htmlspecialchars($_SESSION['exito']) ?></div>
    <?php unset($_SESSION['exito']); ?>
<?php endif; ?>
<?php if (!$iglesias): ?>
    <div class="admin-aviso">Necesitas registrar una iglesia antes de crear transmisiones. <a href="index.php?r=iglesias">Registrar iglesia →</a></div>
<?php endif; ?>

<!-- ===== Estadísticas ===== -->
<!-- Grid de Bootstrap: 1 columna en celular, 2 en tablet y 4 en escritorio -->
<section class="row g-3" aria-label="Resumen">
    <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat h-100">
        <div class="admin-stat-cabecera"><span>Iglesias registradas</span><span class="admin-stat-icono azul"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4M10 4h4M5 21V11l7-5 7 5v10M9 21v-5h6v5M3 21h18"/></svg></span></div>
        <strong><?= count($iglesias) ?></strong>
        <p>Disponibles para asignar transmisiones</p>
    </article></div>
    <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat h-100">
        <div class="admin-stat-cabecera"><span>En vivo ahora</span><span class="admin-stat-icono rojo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="2"/><path d="M7.8 7.8a6 6 0 0 0 0 8.4m8.4 0a6 6 0 0 0 0-8.4"/></svg></span></div>
        <strong><?= $conteo['en_vivo'] ?></strong>
        <p><?= $conteo['en_vivo'] ? 'Se muestra en la portada' : 'Ninguna transmisión activa' ?></p>
    </article></div>
    <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat h-100">
        <div class="admin-stat-cabecera"><span>Programadas</span><span class="admin-stat-icono oro"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg></span></div>
        <strong><?= $conteo['programada'] ?></strong>
        <p>Próximos cultos publicados</p>
    </article></div>
    <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat h-100">
        <div class="admin-stat-cabecera"><span>Publicaciones</span><span class="admin-stat-icono morado"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M22 8.2a3 3 0 0 0-2.1-2.1C18 5.6 12 5.6 12 5.6s-6 0-7.9.5A3 3 0 0 0 2 8.2 31 31 0 0 0 1.6 12a31 31 0 0 0 .4 3.8 3 3 0 0 0 2.1 2.1c1.9.5 7.9.5 7.9.5s6 0 7.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .4-3.8 31 31 0 0 0-.4-3.8z"/><path d="M10 15V9l5 3z"/></svg></span></div>
        <strong><?= count($rows) ?></strong>
        <p><?= $conteo['finalizada'] ?> finalizada<?= $conteo['finalizada'] === 1 ? '' : 's' ?></p>
    </article></div>
</section>

<!-- ===== Formulario en ventana de diálogo =====
     Se abre con "Crear nueva transmisión" o con "Editar" de la tabla.
     data-abrir-al-cargar: si el backend devolvió un error, se abre solo con los datos enviados. -->
<dialog class="admin-dialogo admin-dialogo-ancho" id="dialogo-transmision" aria-labelledby="form-title"<?= $formulario ? ' data-abrir-al-cargar' : '' ?>>
    <section class="admin-panel admin-formulario">
        <div class="admin-dialogo-cabecera">
            <div>
                <h2 id="form-title"><?= $editando ? 'Editar transmisión' : 'Nueva transmisión' ?></h2>
                <p class="admin-panel-sub">Guarda YouTube y, si los tienes, los enlaces del directo en Facebook y TikTok.</p>
            </div>
            <button class="admin-icono-boton" type="button" data-cerrar-dialogo aria-label="Cerrar" title="Cerrar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <form method="post" action="index.php?r=guardar" id="editor" data-validar="transmision" data-fecha-defecto="<?= htmlspecialchars(date('Y-m-d\\TH:i')) ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="id" id="id" value="<?= (int) ($formulario['id'] ?? 0) ?>">

            <!-- Dos columnas; .completo ocupa todo el ancho -->
            <div class="admin-form-grid">
                <div>
                    <label class="form-label" for="titulo">Título</label>
                    <input class="form-control" name="titulo" id="titulo" maxlength="150" required placeholder="Ej.: Culto dominical" value="<?= htmlspecialchars($formulario['titulo'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label" for="iglesia_id">Iglesia</label>
                    <select class="form-select" name="iglesia_id" id="iglesia_id" required>
                        <option value="">Selecciona una iglesia</option>
                        <?php foreach ($iglesias as $iglesia): ?>
                            <option value="<?= (int) $iglesia['id'] ?>" <?= (string) ($formulario['iglesia_id'] ?? '') === (string) $iglesia['id'] ? 'selected' : '' ?>><?= htmlspecialchars($iglesia['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="fecha_hora">Fecha y hora (Bolivia)</label>
                    <input class="form-control" type="datetime-local" name="fecha_hora" id="fecha_hora" required value="<?= htmlspecialchars($formulario['fecha_hora'] ?? date('Y-m-d\\TH:i')) ?>">
                </div>
                <div>
                    <label class="form-label" for="estado">Estado</label>
                    <select class="form-select" name="estado" id="estado">
                        <?php foreach ($etiquetas as $valor => $texto): ?>
                            <option value="<?= $valor ?>" <?= ($formulario['estado'] ?? 'programada') === $valor ? 'selected' : '' ?>><?= $texto ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="completo">
                    <label class="form-label" for="youtube">Enlace de YouTube</label>
                    <input class="form-control" name="youtube" id="youtube" required placeholder="https://www.youtube.com/live/..." value="<?= htmlspecialchars($formulario['youtube'] ?? '') ?>">
                    <p class="admin-ayuda">El video debe permitir reproducirse en otras páginas.</p>
                </div>
                <div>
                    <label class="form-label" for="facebook">Facebook (opcional)</label>
                    <input class="form-control" type="url" name="facebook" id="facebook" maxlength="500" placeholder="https://www.facebook.com/.../videos/..." value="<?= htmlspecialchars($formulario['facebook'] ?? '') ?>">
                    <p class="admin-ayuda">Deja vacío para quitar el enlace de Facebook de esta transmisión.</p>
                </div>
                <div>
                    <label class="form-label" for="tiktok">TikTok (opcional)</label>
                    <input class="form-control" type="url" name="tiktok" id="tiktok" maxlength="500" placeholder="https://www.tiktok.com/@cuenta/live" value="<?= htmlspecialchars($formulario['tiktok'] ?? '') ?>">
                    <p class="admin-ayuda">Deja vacío para quitar el enlace de TikTok de esta transmisión.</p>
                </div>
            </div>

            <div class="admin-form-acciones">
                <button class="btn admin-btn-oro" <?= !$iglesias ? 'disabled' : '' ?>>Guardar transmisión</button>
                <button class="btn admin-btn-linea" type="button" data-cerrar-dialogo>Cancelar</button>
            </div>
        </form>
    </section>
</dialog>

<!-- ===== Tabla (ocupa todo el ancho) ===== -->
<section class="admin-panel admin-tabla-panel">
        <div class="admin-tabla-cabecera">
            <div>
                <h2>Publicaciones (<?= count($rows) ?>)</h2>
                <p class="admin-panel-sub">Cambia el estado manualmente: programada → en vivo → finalizada.</p>
            </div>
            <div class="admin-filtros">
                <select class="form-select form-select-sm" data-filtro="estado" data-tabla="tabla-transmisiones" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <?php foreach ($etiquetas as $valor => $texto): ?>
                        <option value="<?= $valor ?>"><?= $texto ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-select form-select-sm" data-filtro="iglesia" data-tabla="tabla-transmisiones" aria-label="Filtrar por iglesia">
                    <option value="">Todas las iglesias</option>
                    <?php foreach ($iglesias as $iglesia): ?>
                        <option value="<?= (int) $iglesia['id'] ?>"><?= htmlspecialchars($iglesia['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (!$rows): ?>
            <div class="admin-vacio">Aún no hay transmisiones. Crea la primera con el botón «Crear nueva transmisión».</div>
        <?php else: ?>
            <div class="admin-tabla-scroll">
                <table class="admin-tabla" id="tabla-transmisiones">
                    <thead>
                        <tr><th>Transmisión</th><th>Iglesia</th><th>Estado</th><th>Enlaces</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $t): ?>
                            <?php $datos = [
                                'id' => $t->id, 'titulo' => $t->titulo, 'iglesia_id' => $t->iglesia_id,
                                'fecha_hora' => date('Y-m-d\\TH:i', strtotime($t->fecha_hora)),
                                'youtube' => $t->youtube ?? '', 'estado' => $t->estado,
                                'facebook' => $t->facebook ?? '', 'tiktok' => $t->tiktok ?? '',
                            ]; ?>
                            <tr data-estado="<?= htmlspecialchars($t->estado) ?>" data-iglesia="<?= (int) $t->iglesia_id ?>">
                                <td>
                                    <strong><?= htmlspecialchars($t->titulo) ?></strong>
                                    <small><?= htmlspecialchars(date('d/m/Y · H:i', strtotime($t->fecha_hora))) ?></small>
                                </td>
                                <td><span class="admin-chip"><?= htmlspecialchars($t->iglesia) ?></span></td>
                                <td><span class="admin-estado <?= htmlspecialchars($t->estado) ?>"><?= htmlspecialchars($etiquetas[$t->estado] ?? $t->estado) ?></span></td>
                                <td>
                                    <?php if ($t->youtube_id): ?>
                                        <a class="admin-enlace" href="https://www.youtube.com/watch?v=<?= htmlspecialchars($t->youtube_id) ?>" target="_blank" rel="noopener noreferrer">Ver en YouTube ↗</a>
                                    <?php else: ?>
                                        <span class="admin-tenue">Sin enlace de YouTube</span>
                                    <?php endif; ?>
                                    <?php foreach (['facebook' => 'Facebook', 'tiktok' => 'TikTok'] as $red => $nombreRed): ?>
                                        <?php if (!empty($t->$red)): ?>
                                            <br><a class="admin-enlace" href="<?= htmlspecialchars($t->$red) ?>" target="_blank" rel="noopener noreferrer">Ver en <?= $nombreRed ?> ↗</a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <div class="admin-acciones">
                                        <?php
                                        // Botón rápido de estado: reenvía los mismos datos a "guardar" cambiando solo el estado.
                                        $siguiente = ['programada' => ['en_vivo', '▶ Poner en vivo', 'vivo'],
                                                    'en_vivo' => ['finalizada', '■ Finalizar', 'finalizar']][$t->estado] ?? null;
                                        ?>
                                        <?php if ($siguiente && $datos['youtube'] !== ''): ?>
                                            <form method="post" action="index.php?r=guardar"<?= $siguiente[0] === 'finalizada' ? ' onsubmit="return confirm(\'¿Finalizar esta transmisión?\')"' : '' ?>>
                                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                                <?php foreach (['id', 'titulo', 'iglesia_id', 'fecha_hora', 'youtube', 'facebook', 'tiktok'] as $campo): ?>
                                                    <input type="hidden" name="<?= $campo ?>" value="<?= htmlspecialchars((string) $datos[$campo]) ?>">
                                                <?php endforeach; ?>
                                                <input type="hidden" name="estado" value="<?= $siguiente[0] ?>">
                                                <button class="admin-accion <?= $siguiente[2] ?>"><?= $siguiente[1] ?></button>
                                            </form>
                                        <?php endif; ?>
                                        <button class="admin-accion" type="button" onclick="editar(<?= htmlspecialchars(json_encode($datos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>)">Editar</button>
                                        <form method="post" action="index.php?r=eliminar" onsubmit="return confirm('¿Eliminar esta transmisión?')">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $t->id ?>">
                                            <button class="admin-accion peligro">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="admin-vacio admin-sin-resultados" hidden>No hay transmisiones con ese filtro.</p>
            </div>
        <?php endif; ?>
</section>

<script>
// Abrir, cerrar y abrir-al-cargar los maneja admin.js (window.abrirDialogo).
// Aquí solo se preparan los datos del formulario antes de abrirlo.
const dialogoTransmision = document.getElementById('dialogo-transmision');
const formularioTransmision = document.getElementById('editor');

function quitarAvisosDialogo() {
    dialogoTransmision.querySelectorAll('.admin-aviso').forEach(function (a) { a.remove(); });
}

// "Crear nueva transmisión": vacía el formulario y lo abre.
document.querySelectorAll('[data-nueva-transmision]').forEach(function (boton) {
    boton.addEventListener('click', function () {
        document.getElementById('id').value = '0';
        document.getElementById('titulo').value = '';
        document.getElementById('iglesia_id').value = '';
        document.getElementById('youtube').value = '';
        document.getElementById('facebook').value = '';
        document.getElementById('tiktok').value = '';
        document.getElementById('fecha_hora').value = formularioTransmision.dataset.fechaDefecto;
        document.getElementById('estado').value = 'programada';
        document.getElementById('form-title').textContent = 'Nueva transmisión';
        quitarAvisosDialogo();
        window.abrirDialogo(dialogoTransmision);
    });
});

// Botón "Editar" de la tabla: carga los datos de la fila y abre el diálogo.
function editar(datos) {
    for (const campo of ['id', 'titulo', 'iglesia_id', 'fecha_hora', 'youtube', 'facebook', 'tiktok', 'estado']) {
        document.getElementById(campo).value = datos[campo];
    }
    document.getElementById('form-title').textContent = 'Editar transmisión';
    quitarAvisosDialogo();
    window.abrirDialogo(dialogoTransmision);
}
</script>
