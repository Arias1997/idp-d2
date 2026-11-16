<?php
// Panel: registro, edición y eliminación de iglesias.
// Recibe: $iglesias (objetos Iglesia), $zonas (id, nombre), $mapaConfig, $formulario.
// IMPORTANTE: no cambiar name="" ni los id del mapa (los usan el backend e iglesia-mapa.js).
$editando = (int) ($formulario['id'] ?? 0) > 0;
// La ventana del formulario se abre sola si se está editando o si el backend devolvió un error.
$abrirAlCargar = $editando || isset($_SESSION['iglesia_formulario']);
$activas = count(array_filter($iglesias, fn($i) => $i->activa));
$conUbicacion = count(array_filter($iglesias, fn($i) => $i->latitud !== null && $i->longitud !== null));
?>
<link rel="stylesheet" href="assets/vendor/leaflet/leaflet.css">

<!-- ===== Encabezado ===== -->
<section class="admin-encabezado">
    <div>
        <span class="admin-insignia"><span class="admin-punto" aria-hidden="true"></span>Gestión de congregaciones</span>
        <h1>Iglesias del distrito</h1>
        <p>Registra cada iglesia con su zona y ubicación para seleccionarla al crear transmisiones.</p>
    </div>
    <div class="admin-bienvenida-acciones">
        <?php if ($editando): ?>
            <!-- Si se está editando, recarga la página limpia y abre el formulario vacío -->
            <a class="btn admin-btn-oro" href="index.php?r=iglesias#nueva">+ Registrar nueva iglesia</a>
        <?php else: ?>
            <button class="btn admin-btn-oro" type="button" data-abrir-dialogo="dialogo-iglesia" <?= !$zonas ? 'disabled' : '' ?>>+ Registrar una nueva iglesia</button>
        <?php endif; ?>
        <a class="btn admin-btn-linea" href="index.php?r=zonas">Gestionar zonas</a>
    </div>
</section>

<?php if (!empty($_SESSION['iglesia_exito'])): ?>
    <div class="admin-aviso exito" role="status"><?= htmlspecialchars($_SESSION['iglesia_exito']) ?></div>
    <?php unset($_SESSION['iglesia_exito']); ?>
<?php endif; ?>
<?php if (!$zonas): ?>
    <div class="admin-aviso">Primero registra una zona para continuar. <a href="index.php?r=zonas">Registrar zona →</a></div>
<?php endif; ?>

<!-- ===== Estadísticas ===== -->
<!-- Grid de Bootstrap: 1 columna en celular, 2 en tablet y 4 en escritorio -->
<section class="row g-3" aria-label="Resumen">
    <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat h-100">
        <div class="admin-stat-cabecera"><span>Iglesias registradas</span><span class="admin-stat-icono azul"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4M10 4h4M5 21V11l7-5 7 5v10M9 21v-5h6v5M3 21h18"/></svg></span></div>
        <strong><?= count($iglesias) ?></strong>
        <p>Directorio del Distrito 2</p>
    </article></div>
    <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat h-100">
        <div class="admin-stat-cabecera"><span>Activas</span><span class="admin-stat-icono verde"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="m5 12 5 5L20 7"/></svg></span></div>
        <strong><?= $activas ?></strong>
        <p><?= count($iglesias) - $activas ?> inactiva<?= count($iglesias) - $activas === 1 ? '' : 's' ?></p>
    </article></div>
    <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat h-100">
        <div class="admin-stat-cabecera"><span>Con ubicación</span><span class="admin-stat-icono oro"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg></span></div>
        <strong><?= $conUbicacion ?></strong>
        <p>Aparecen en el mapa público</p>
    </article></div>
    <div class="col-12 col-sm-6 col-xl-3"><article class="admin-stat h-100">
        <div class="admin-stat-cabecera"><span>Zonas</span><span class="admin-stat-icono morado"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2zM9 4v14M15 6v14"/></svg></span></div>
        <strong><?= count($zonas) ?></strong>
        <p>Sectores para organizar iglesias</p>
    </article></div>
</section>

<!-- ===== Formulario en ventana de diálogo =====
     Se abre con "+ Nueva iglesia"; al editar o si hubo error se abre solo (data-abrir-al-cargar). -->
<dialog class="admin-dialogo admin-dialogo-extra" id="dialogo-iglesia" aria-labelledby="titulo-form-iglesia" data-dialogo-nuevo<?= $abrirAlCargar ? ' data-abrir-al-cargar' : '' ?>>
    <section class="admin-panel admin-formulario" id="formulario-iglesia">
        <div class="admin-dialogo-cabecera">
            <div>
                <h2 id="titulo-form-iglesia"><?= $editando ? 'Editar iglesia' : 'Nueva iglesia' ?></h2>
                <p class="admin-panel-sub">Los campos con * son obligatorios.</p>
            </div>
            <button class="admin-icono-boton" type="button" data-cerrar-dialogo aria-label="Cerrar" title="Cerrar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <form method="post" action="index.php?r=guardar_iglesia" data-validar="iglesia">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="id" value="<?= (int) ($formulario['id'] ?? 0) ?>">

            <!-- Izquierda: datos de la iglesia · Derecha: mapa -->
            <div class="admin-form-dos-lados">
            <div class="admin-form-grid">
                <div>
                    <label class="form-label" for="zona_id">Zona *</label>
                    <select class="form-select" name="zona_id" id="zona_id" required>
                        <option value="">Selecciona una zona</option>
                        <?php foreach ($zonas as $zona): ?>
                            <option value="<?= (int) $zona['id'] ?>" <?= (string) ($formulario['zona_id'] ?? '') === (string) $zona['id'] ? 'selected' : '' ?>><?= htmlspecialchars($zona['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="nombre">Nombre de la iglesia *</label>
                    <input class="form-control" name="nombre" id="nombre" maxlength="150" required value="<?= htmlspecialchars($formulario['nombre'] ?? '') ?>">
                </div>
                <div class="completo">
                    <label class="form-label" for="direccion">Dirección *</label>
                    <input class="form-control" name="direccion" id="direccion" maxlength="200" required value="<?= htmlspecialchars($formulario['direccion'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label" for="pastor">Pastor</label>
                    <input class="form-control" name="pastor" id="pastor" maxlength="120" value="<?= htmlspecialchars($formulario['pastor'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input class="form-control" name="telefono" id="telefono" type="tel" maxlength="25" value="<?= htmlspecialchars($formulario['telefono'] ?? '') ?>">
                </div>
                <div class="completo">
                    <label class="form-label" for="horarios">Horarios</label>
                    <textarea class="form-control" name="horarios" id="horarios" rows="3" maxlength="5000" placeholder="Una línea por culto. Ej.:&#10;Domingo - 10:00&#10;Miércoles - 19:30"><?= htmlspecialchars($formulario['horarios'] ?? '') ?></textarea>
                    <p class="admin-ayuda">Escribe una línea por culto (Día - Hora) para que se vea en tabla en Encuéntranos.</p>
                </div>
                <div class="completo">
                    <label class="checkbox-label">
                        <input name="activa" type="checkbox" value="1" <?= ($formulario['activa'] ?? '') === '1' ? 'checked' : '' ?>>
                        Iglesia activa (visible en el portal)
                    </label>
                </div>
            </div>

            <div class="ubicacion-iglesia">
                <label class="form-label" for="iglesia-mapa">Ubicación en el mapa</label>
                <p class="admin-ayuda">Haz clic en el mapa para marcar la iglesia. Es opcional.</p>
                <input type="hidden" name="latitud" id="iglesia-latitud" value="<?= htmlspecialchars($formulario['latitud'] ?? '') ?>">
                <input type="hidden" name="longitud" id="iglesia-longitud" value="<?= htmlspecialchars($formulario['longitud'] ?? '') ?>">
                <div id="iglesia-mapa" class="mapa-iglesia" aria-label="Mapa para ubicar la iglesia"
                     data-latitud="<?= htmlspecialchars((string) $mapaConfig['latitud_inicial']) ?>"
                     data-longitud="<?= htmlspecialchars((string) $mapaConfig['longitud_inicial']) ?>"
                     data-zoom="<?= (int) $mapaConfig['zoom_inicial'] ?>"
                     data-teselas="<?= htmlspecialchars($mapaConfig['url_mapa']) ?>"></div>
                <p id="iglesia-mapa-estado" class="admin-ayuda" role="status" aria-live="polite">Cargando mapa…</p>
                <button class="btn admin-btn-linea pequeno" type="button" id="quitar-ubicacion">Quitar ubicación</button>
                <noscript>Activa JavaScript para seleccionar una ubicación. También puedes registrar la iglesia sin ubicación.</noscript>
            </div>
            </div>

            <div class="admin-form-acciones">
                <button class="btn admin-btn-oro" <?= !$zonas ? 'disabled' : '' ?>><?= $editando ? 'Guardar cambios' : 'Registrar iglesia' ?></button>
                <?php if ($editando): ?>
                    <a class="btn admin-btn-linea" href="index.php?r=iglesias">Cancelar</a>
                <?php else: ?>
                    <button class="btn admin-btn-linea" type="button" data-cerrar-dialogo>Cancelar</button>
                <?php endif; ?>
            </div>
        </form>
    </section>
</dialog>

<!-- ===== Tabla (ocupa todo el ancho) ===== -->
<section class="admin-panel admin-tabla-panel">
        <div class="admin-tabla-cabecera">
            <div>
                <h2>Iglesias registradas (<?= count($iglesias) ?>)</h2>
                <p class="admin-panel-sub">Directorio de congregaciones por zona.</p>
            </div>
            <div class="admin-filtros">
                <input class="form-control form-control-sm" type="search" data-buscar="tabla-iglesias" placeholder="Buscar iglesia o pastor…" aria-label="Buscar iglesia">
                <select class="form-select form-select-sm" data-filtro="zona" data-tabla="tabla-iglesias" aria-label="Filtrar por zona">
                    <option value="">Todas las zonas</option>
                    <?php foreach ($zonas as $zona): ?>
                        <option value="<?= htmlspecialchars($zona['nombre']) ?>"><?= htmlspecialchars($zona['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (!$iglesias): ?>
            <div class="admin-vacio">Todavía no hay iglesias registradas.</div>
        <?php else: ?>
            <div class="admin-tabla-scroll">
                <table class="admin-tabla" id="tabla-iglesias">
                    <thead>
                        <tr><th>Iglesia</th><th>Zona</th><th>Pastor</th><th>Ubicación</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($iglesias as $iglesia): ?>
                            <tr data-zona="<?= htmlspecialchars($iglesia->zona) ?>">
                                <td>
                                    <strong><?= htmlspecialchars($iglesia->nombre) ?></strong>
                                    <small><?= htmlspecialchars($iglesia->direccion) ?></small>
                                </td>
                                <td><span class="admin-chip"><?= htmlspecialchars($iglesia->zona) ?></span></td>
                                <td>
                                    <?php if ($iglesia->pastor !== null): ?>
                                        <strong class="admin-normal"><?= htmlspecialchars($iglesia->pastor) ?></strong>
                                    <?php else: ?>
                                        <span class="admin-tenue">Sin asignar</span>
                                    <?php endif; ?>
                                    <?php if ($iglesia->telefono !== null): ?><small><?= htmlspecialchars($iglesia->telefono) ?></small><?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($iglesia->latitud !== null && $iglesia->longitud !== null): ?>
                                        <a class="admin-enlace" href="https://www.openstreetmap.org/?mlat=<?= rawurlencode($iglesia->latitud) ?>&amp;mlon=<?= rawurlencode($iglesia->longitud) ?>#map=17/<?= rawurlencode($iglesia->latitud) ?>/<?= rawurlencode($iglesia->longitud) ?>" target="_blank" rel="noopener noreferrer">Ver mapa ↗</a>
                                    <?php else: ?>
                                        <span class="admin-tenue">Sin ubicación</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="admin-estado <?= $iglesia->activa ? 'activa' : 'inactiva' ?>"><?= $iglesia->activa ? 'Activa' : 'Inactiva' ?></span></td>
                                <td>
                                    <div class="admin-acciones">
                                        <a class="admin-accion" href="index.php?r=iglesias&amp;editar=<?= (int) $iglesia->id ?>#formulario-iglesia">Editar</a>
                                        <form method="post" action="index.php?r=eliminar_iglesia" onsubmit="return confirm('¿Eliminar esta iglesia? Esta acción no se puede deshacer.')">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $iglesia->id ?>">
                                            <button class="admin-accion peligro" type="submit">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="admin-vacio admin-sin-resultados" hidden>No hay iglesias que coincidan con la búsqueda.</p>
            </div>
        <?php endif; ?>
</section>

<script src="assets/vendor/leaflet/leaflet.js"></script>
<script src="assets/js/iglesia-mapa.js"></script>
