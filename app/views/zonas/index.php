<?php
// Panel: registro, edición y eliminación de zonas.
// Recibe: $zonas (objetos Zona) y $formulario (id y nombre).
$editando = (int) ($formulario['id'] ?? 0) > 0;
// La ventana del formulario se abre sola si se está editando o si el backend devolvió un error.
$abrirAlCargar = $editando || isset($_SESSION['zona_formulario']);
?>
<!-- ===== Encabezado ===== -->
<section class="admin-encabezado">
    <div>
        <span class="admin-insignia"><span class="admin-punto" aria-hidden="true"></span>Organización territorial</span>
        <h1>Zonas del distrito</h1>
        <p>Organiza las iglesias por sector o localidad de Sacaba.</p>
    </div>
    <div class="admin-bienvenida-acciones">
        <?php if ($editando): ?>
            <!-- Si se está editando, recarga la página limpia y abre el formulario vacío -->
            <a class="btn admin-btn-oro" href="index.php?r=zonas#nueva">+ Registrar una nueva zona</a>
        <?php else: ?>
            <button class="btn admin-btn-oro" type="button" data-abrir-dialogo="dialogo-zona">+ Registrar una nueva zona</button>
        <?php endif; ?>
        <a class="btn admin-btn-linea" href="index.php?r=iglesias">Ir a iglesias</a>
    </div>
</section>

<?php if (!empty($_SESSION['zona_exito'])): ?>
    <div class="admin-aviso exito" role="status">
        <?= htmlspecialchars($_SESSION['zona_exito']) ?>
        <a href="index.php?r=iglesias">Registrar una iglesia →</a>
    </div>
    <?php unset($_SESSION['zona_exito']); ?>
<?php endif; ?>

<!-- ===== Estadísticas ===== -->
<!-- Grid de Bootstrap: 1 columna en celular y 2 desde tablet -->
<section class="row g-3" aria-label="Resumen">
    <div class="col-12 col-md-6"><article class="admin-stat h-100">
        <div class="admin-stat-cabecera"><span>Zonas registradas</span><span class="admin-stat-icono morado"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2zM9 4v14M15 6v14"/></svg></span></div>
        <strong><?= count($zonas) ?></strong>
        <p>Disponibles al registrar iglesias</p>
    </article></div>
    <div class="col-12 col-md-6"><article class="admin-stat h-100">
        <div class="admin-stat-cabecera"><span>Siguiente paso</span><span class="admin-stat-icono azul"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4M10 4h4M5 21V11l7-5 7 5v10M9 21v-5h6v5M3 21h18"/></svg></span></div>
        <p class="admin-stat-texto">Con las zonas listas, registra las iglesias y marca su ubicación en el mapa.</p>
        <a class="admin-enlace" href="index.php?r=iglesias">Registrar iglesias →</a>
    </article></div>
</section>

<!-- ===== Formulario en ventana de diálogo =====
     Se abre con "+ Nueva zona"; al editar o si hubo error se abre solo (data-abrir-al-cargar). -->
<dialog class="admin-dialogo" id="dialogo-zona" aria-labelledby="titulo-form-zona" data-dialogo-nuevo<?= $abrirAlCargar ? ' data-abrir-al-cargar' : '' ?>>
    <section class="admin-panel admin-formulario" id="formulario-zona">
        <div class="admin-dialogo-cabecera">
            <div>
                <h2 id="titulo-form-zona"><?= $editando ? 'Editar zona' : 'Nueva zona' ?></h2>
                <p class="admin-panel-sub">El nombre no puede repetirse.</p>
            </div>
            <button class="admin-icono-boton" type="button" data-cerrar-dialogo aria-label="Cerrar" title="Cerrar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <form method="post" action="index.php?r=guardar_zona" data-validar="zona">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="id" value="<?= (int) ($formulario['id'] ?? 0) ?>">
            <label class="form-label" for="nombre-zona">Nombre de la zona</label>
            <input class="form-control" name="nombre" id="nombre-zona" maxlength="100" required placeholder="Ej.: Zona Central" value="<?= htmlspecialchars($formulario['nombre'] ?? '') ?>">
            <div class="admin-form-acciones">
                <button class="btn admin-btn-oro"><?= $editando ? 'Guardar cambios' : 'Registrar zona' ?></button>
                <?php if ($editando): ?>
                    <a class="btn admin-btn-linea" href="index.php?r=zonas">Cancelar</a>
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
                <h2>Zonas registradas (<?= count($zonas) ?>)</h2>
                <p class="admin-panel-sub">Ordenadas alfabéticamente.</p>
            </div>
            <?php if (count($zonas) > 5): ?>
                <div class="admin-filtros">
                    <input class="form-control form-control-sm" type="search" data-buscar="tabla-zonas" placeholder="Buscar zona…" aria-label="Buscar zona">
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$zonas): ?>
            <div class="admin-vacio">Todavía no hay zonas registradas.</div>
        <?php else: ?>
            <div class="admin-tabla-scroll">
                <table class="admin-tabla" id="tabla-zonas">
                    <thead>
                        <tr><th>#</th><th>Zona</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($zonas as $n => $zona): ?>
                            <tr data-zona-id="<?= (int) $zona->id ?>" data-zona-nombre="<?= htmlspecialchars($zona->nombre, ENT_QUOTES, 'UTF-8') ?>">
                                <td class="admin-tenue"><?= $n + 1 ?></td>
                                <td><strong><?= htmlspecialchars($zona->nombre) ?></strong></td>
                                <td>
                                    <div class="admin-acciones">
                                        <a class="admin-accion" href="index.php?r=zonas&amp;editar=<?= (int) $zona->id ?>#formulario-zona">Editar</a>
                                        <form method="post" action="index.php?r=eliminar_zona" onsubmit="return confirm('¿Eliminar esta zona? Esta acción no se puede deshacer.')">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $zona->id ?>">
                                            <button class="admin-accion peligro" type="submit">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="admin-vacio admin-sin-resultados" hidden>No hay zonas que coincidan.</p>
            </div>
        <?php endif; ?>
</section>
