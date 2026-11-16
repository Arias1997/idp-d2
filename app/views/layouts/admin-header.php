<?php
// Layout del panel de administración: barra lateral + barra superior.
// Se incluye desde header.php cuando la ruta es del panel ($ipdEsAdmin).
// El menú lateral cambia según el rol ($ipdRol, definido en header.php).
$menuAdministrador = [
    'resumen' => ['Resumen', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 10 12 3l9 7v11H3zM9 21v-8h6v8"/></svg>'],
    'admin' => ['Transmisiones', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="2"/><path d="M7.8 7.8a6 6 0 0 0 0 8.4m8.4 0a6 6 0 0 0 0-8.4M4.9 4.9a10 10 0 0 0 0 14.2m14.2 0a10 10 0 0 0 0-14.2"/></svg>'],
    'iglesias' => ['Iglesias', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4M10 4h4M5 21V11l7-5 7 5v10M9 21v-5h6v5M3 21h18"/></svg>'],
    'zonas' => ['Zonas', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2zM9 4v14M15 6v14"/></svg>'],
    'usuarios' => ['Usuarios', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18.5 14a6.5 6.5 0 0 1 3 6"/></svg>'],
];
$iconoPerfil = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M6.2 18.8a7 7 0 0 1 11.6 0"/></svg>';
$menuAdministrador['mi_perfil'] = ['Mi perfil', $iconoPerfil];
$menusPorRol = [
    'administrador' => $menuAdministrador,
    'usuario' => ['mi_perfil' => ['Mi perfil', $iconoPerfil]],
];
$adminMenu = $menusPorRol[$ipdRol] ?? $menusPorRol['usuario'];
$rolEtiqueta = $ipdRol === 'administrador' ? 'Administrador' : 'Usuario';
$tituloMenu = $ipdRol === 'administrador' ? 'Administración' : 'Mi cuenta';
$adminNombre = $_SESSION['usuario_nombre'] ?? $rolEtiqueta;
$adminInicial = function_exists('mb_substr') ? mb_strtoupper(mb_substr($adminNombre, 0, 1)) : strtoupper(substr($adminNombre, 0, 1));
?>
<div class="admin-layout">
    <!-- ===== Barra lateral ===== -->
    <aside class="admin-lateral" id="admin-lateral">
        <a class="admin-marca" href="index.php?r=<?= $ipdInicioPanel ?>">
            <?php if ($ipdLogo): ?>
                <img src="<?= htmlspecialchars($ipdLogo) ?>" alt="">
            <?php else: ?>
                <span class="admin-marca-logo" aria-hidden="true">IDP</span>
            <?php endif; ?>
            <span>
                <strong>IDP</strong>
                <small>Distrito 2 · Sacaba</small>
            </span>
        </a>

        <nav class="admin-nav" aria-label="<?= $tituloMenu ?>">
            <span class="admin-nav-titulo"><?= $tituloMenu ?></span>
            <?php foreach ($adminMenu as $ruta => [$texto, $icono]): ?>
                <a href="index.php?r=<?= $ruta ?>"<?= $ipdRuta === $ruta ? ' class="activo" aria-current="page"' : '' ?>>
                    <?= $icono ?>
                    <?= $texto ?>
                </a>
            <?php endforeach; ?>

            <?php if ($ipdRol === 'administrador'): ?>
                <span class="admin-nav-titulo">Reportes</span>
                <!-- Abre el resumen del distrito en PDF. -->
                <a href="index.php?r=reporte_resumen" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M12 18v-6M9 15l3 3 3-3"/></svg>
                    Resumen en PDF
                </a>
            <?php endif; ?>

            <span class="admin-nav-titulo">Sitio web</span>
            <a href="index.php" target="portal-ipd">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg>
                Ver portal web
            </a>
        </nav>

        <div class="admin-lateral-pie">
            <span class="admin-punto" aria-hidden="true"></span>
            <span>
                <strong>Iglesia de Dios de la Profecía</strong>
                <small>Sacaba · Cochabamba, BOL</small>
            </span>
        </div>
    </aside>
    <div class="admin-velo" data-cerrar-lateral hidden></div>

    <div class="admin-principal">
        <!-- ===== Barra superior ===== -->
        <header class="admin-barra">
            <button class="admin-menu-boton" type="button" aria-controls="admin-lateral" aria-expanded="false" aria-label="Abrir menú del panel">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <p class="admin-migas">
                <span><?= $ipdRol === 'administrador' ? 'Administración distrital' : 'Mi cuenta' ?></span>
                <span aria-hidden="true">/</span>
                <strong><?= htmlspecialchars($adminMenu[$ipdRuta][0] ?? 'Panel') ?></strong>
            </p>

            <div class="admin-barra-acciones">
                <!-- Modo claro / oscuro: muestra sol en modo oscuro y luna en modo claro -->
                <button class="admin-icono-boton" type="button" data-tema aria-pressed="false" aria-label="Cambiar a modo claro" title="Modo claro / oscuro">
                    <svg class="icono-sol" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    <svg class="icono-luna" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                </button>

                <!-- Accesibilidad -->
                <div class="desplegable">
                    <button class="admin-icono-boton desplegable-boton" type="button" aria-expanded="false" aria-haspopup="true" aria-label="Accesibilidad" title="Accesibilidad">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="7" r="1.2" fill="currentColor"/><path d="M7.5 10h9M12 10v4m0 0-2.5 4M12 14l2.5 4"/></svg>
                    </button>
                    <div class="desplegable-menu" hidden>
                        <button type="button" data-acc="letra-grande" aria-pressed="false">Letra grande</button>
                        <button type="button" data-acc="alto-contraste" aria-pressed="false">Alto contraste</button>
                    </div>
                </div>

                <?php if ($ipdRol === 'administrador'): ?>
                    <!-- Exportar el resumen del administrador en PDF. -->
                    <a class="btn admin-btn-oro pequeno admin-exportar" href="index.php?r=reporte_resumen" target="_blank" rel="noopener" title="Descargar el resumen del distrito en PDF">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M12 18v-6M9 15l3 3 3-3"/></svg>
                        <span>Exportar PDF</span>
                    </a>
                <?php endif; ?>

                <a class="admin-portal" href="index.php" target="portal-ipd">
                    Ver portal web
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
                </a>

                <div class="admin-usuario">
                    <span class="admin-avatar" aria-hidden="true"><?= htmlspecialchars($adminInicial) ?></span>
                    <span class="admin-usuario-texto">
                        <strong><?= htmlspecialchars($adminNombre) ?></strong>
                        <small><?= $rolEtiqueta ?> · D-2 Sacaba</small>
                    </span>
                </div>

                <form method="post" action="index.php?r=salir">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                    <button class="admin-icono-boton" type="submit" aria-label="Cerrar sesión" title="Cerrar sesión">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <main id="contenido" class="admin-contenido">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="admin-aviso error" role="alert"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
