<?php
// Cuentas y permisos: dos roles, sin asignación de iglesias.
$usuarios = $usuarios ?? [];
$formulario = $formulario ?? [];
$etiquetasRol = ['administrador' => 'Administrador', 'usuario' => 'Usuario'];
$conteo = ['administrador' => 0, 'usuario' => 0];
foreach ($usuarios as $cuenta) {
    if (isset($conteo[$cuenta['rol']])) $conteo[$cuenta['rol']]++;
}
$crearAbierto = ($formulario['accion'] ?? '') === 'crear';
$rolAbierto = ($formulario['accion'] ?? '') === 'rol';
?>
<section class="admin-encabezado">
    <div>
        <span class="admin-insignia"><span class="admin-punto" aria-hidden="true"></span>Cuentas y permisos</span>
        <h1>Usuarios del sistema</h1>
        <p>Crea cuentas y define quién administra el distrito. Cada usuario consulta su propio perfil.</p>
    </div>
    <div class="admin-bienvenida-acciones">
        <button class="btn admin-btn-oro" type="button" data-abrir-dialogo="dialogo-usuario">+ Nuevo usuario</button>
    </div>
</section>

<?php if (!empty($_SESSION['usuario_exito'])): ?>
    <div class="admin-aviso exito" role="status"><?= htmlspecialchars($_SESSION['usuario_exito']) ?></div>
    <?php unset($_SESSION['usuario_exito']); ?>
<?php endif; ?>

<section class="row g-3" aria-label="Resumen de cuentas">
    <?php foreach (['cuentas' => 'Cuentas', 'administrador' => 'Administradores', 'usuario' => 'Usuarios'] as $clave => $etiqueta): ?>
        <div class="col-12 col-md-4"><article class="admin-stat h-100">
            <div class="admin-stat-cabecera"><span><?= $etiqueta ?></span></div>
            <strong><?= $clave === 'cuentas' ? count($usuarios) : $conteo[$clave] ?></strong>
            <p><?= $clave === 'administrador' ? 'Gestionan el distrito' : ($clave === 'usuario' ? 'Consultan su información personal' : 'Registradas en el sistema') ?></p>
        </article></div>
    <?php endforeach; ?>
</section>

<dialog class="admin-dialogo" id="dialogo-usuario" aria-labelledby="titulo-usuario"<?= $crearAbierto ? ' data-abrir-al-cargar' : '' ?>>
    <section class="admin-panel admin-formulario">
        <div class="admin-dialogo-cabecera">
            <div><h2 id="titulo-usuario">Nueva cuenta</h2><p class="admin-panel-sub">La persona podrá entrar con su correo y contraseña.</p></div>
            <button class="admin-icono-boton" type="button" data-cerrar-dialogo aria-label="Cerrar">×</button>
        </div>
        <form method="post" action="index.php?r=guardar_usuario" data-validar="usuario">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <label class="form-label" for="usuario-nombre">Nombre completo</label>
            <input class="form-control" id="usuario-nombre" name="nombre" maxlength="120" required autocomplete="name" value="<?= htmlspecialchars($crearAbierto ? ($formulario['nombre'] ?? '') : '') ?>">
            <label class="form-label" for="usuario-email">Correo electrónico</label>
            <input class="form-control" id="usuario-email" name="email" type="email" maxlength="150" required autocomplete="email" value="<?= htmlspecialchars($crearAbierto ? ($formulario['email'] ?? '') : '') ?>">
            <label class="form-label" for="usuario-clave">Contraseña inicial</label>
            <input class="form-control" id="usuario-clave" name="clave" type="password" minlength="8" maxlength="72" required autocomplete="new-password">
            <p class="admin-ayuda">Usa al menos 8 caracteres. La contraseña no se vuelve a mostrar después de guardar.</p>
            <label class="form-label" for="usuario-rol">Rol</label>
            <select class="form-select" id="usuario-rol" name="rol" required>
                <?php foreach ($etiquetasRol as $valor => $texto): ?>
                    <option value="<?= $valor ?>"<?= ($crearAbierto ? ($formulario['rol'] ?? 'usuario') : 'usuario') === $valor ? ' selected' : '' ?>><?= $texto ?></option>
                <?php endforeach; ?>
            </select>
            <div class="admin-form-acciones">
                <button class="btn admin-btn-oro" type="submit">Crear cuenta</button>
                <button class="btn admin-btn-linea" type="button" data-cerrar-dialogo>Cancelar</button>
            </div>
        </form>
    </section>
</dialog>

<dialog class="admin-dialogo" id="dialogo-rol" aria-labelledby="titulo-form-rol"<?= $rolAbierto ? ' data-abrir-al-cargar' : '' ?>>
    <section class="admin-panel admin-formulario">
        <div class="admin-dialogo-cabecera">
            <div><h2 id="titulo-form-rol">Cambiar permisos</h2><p class="admin-panel-sub" id="rol-usuario-nombre">Selecciona el rol de la cuenta.</p></div>
            <button class="admin-icono-boton" type="button" data-cerrar-dialogo aria-label="Cerrar">×</button>
        </div>
        <form method="post" action="index.php?r=asignar_rol" id="form-rol" data-validar="rol">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="usuario_id" id="rol-usuario-id" value="<?= (int) ($rolAbierto ? ($formulario['usuario_id'] ?? 0) : 0) ?>">
            <label class="form-label" for="rol-rol">Rol</label>
            <select class="form-select" name="rol" id="rol-rol" required>
                <?php foreach ($etiquetasRol as $valor => $texto): ?>
                    <option value="<?= $valor ?>"<?= ($rolAbierto ? ($formulario['rol'] ?? 'usuario') : 'usuario') === $valor ? ' selected' : '' ?>><?= $texto ?></option>
                <?php endforeach; ?>
            </select>
            <p class="admin-ayuda">El administrador gestiona el sistema. El usuario consulta su información personal.</p>
            <div class="admin-form-acciones">
                <button class="btn admin-btn-oro" type="submit">Guardar permisos</button>
                <button class="btn admin-btn-linea" type="button" data-cerrar-dialogo>Cancelar</button>
            </div>
        </form>
    </section>
</dialog>

<section class="admin-panel admin-tabla-panel">
    <div class="admin-tabla-cabecera">
        <div><h2>Cuentas registradas (<?= count($usuarios) ?>)</h2><p class="admin-panel-sub">Tu cuenta conserva el acceso de administrador.</p></div>
        <div class="admin-filtros">
            <input class="form-control form-control-sm" type="search" data-buscar="tabla-usuarios" placeholder="Buscar nombre o correo…" aria-label="Buscar usuario">
            <select class="form-select form-select-sm" data-filtro="rol" data-tabla="tabla-usuarios" aria-label="Filtrar por rol">
                <option value="">Todos los roles</option>
                <?php foreach ($etiquetasRol as $valor => $texto): ?><option value="<?= $valor ?>"><?= $texto ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php if (!$usuarios): ?>
        <div class="admin-vacio">Todavía no hay cuentas para mostrar.</div>
    <?php else: ?>
        <div class="admin-tabla-scroll">
            <table class="admin-tabla" id="tabla-usuarios">
                <thead><tr><th>Usuario</th><th>Rol</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($usuarios as $cuenta): ?>
                    <?php
                    $propio = (int) $cuenta['id'] === (int) ($_SESSION['usuario_id'] ?? 0);
                    $datos = ['id' => (int) $cuenta['id'], 'nombre' => $cuenta['nombre'], 'rol' => $cuenta['rol']];
                    ?>
                    <tr data-rol="<?= htmlspecialchars($cuenta['rol']) ?>">
                        <td><strong><?= htmlspecialchars($cuenta['nombre']) ?><?= $propio ? ' <span class="badge text-bg-primary">Tú</span>' : '' ?></strong><small><?= htmlspecialchars($cuenta['email']) ?></small></td>
                        <td><span class="admin-rol <?= htmlspecialchars($cuenta['rol']) ?>"><?= htmlspecialchars($etiquetasRol[$cuenta['rol']] ?? 'Sin acceso') ?></span></td>
                        <td>
                            <?php if ($propio): ?>
                                <span class="admin-tenue">Tu cuenta</span>
                            <?php else: ?>
                                <button class="admin-accion" type="button" data-usuario-rol="<?= htmlspecialchars(json_encode($datos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">Cambiar rol</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p class="admin-vacio admin-sin-resultados" hidden>No hay cuentas con ese filtro.</p>
        </div>
    <?php endif; ?>
</section>

<script>
document.querySelectorAll('[data-usuario-rol]').forEach(function (boton) {
    boton.addEventListener('click', function () {
        const datos = JSON.parse(boton.dataset.usuarioRol);
        const dialogo = document.getElementById('dialogo-rol');
        document.getElementById('rol-usuario-id').value = datos.id;
        document.getElementById('rol-usuario-nombre').textContent = 'Cuenta: ' + datos.nombre;
        document.getElementById('rol-rol').value = ['administrador', 'usuario'].includes(datos.rol) ? datos.rol : 'usuario';
        dialogo.querySelectorAll('.admin-aviso').forEach(function (aviso) { aviso.remove(); });
        window.abrirDialogo(dialogo);
    });
});
</script>
