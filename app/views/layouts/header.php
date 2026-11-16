<?php
// Ruta actual y ayuda para imágenes opcionales de public/assets/img.
$ipdRuta = is_string($_GET['r'] ?? null) ? $_GET['r'] : 'inicio';
$ipdEsInicio = $ipdRuta === 'inicio';
// Rutas del panel y destinos de los dos roles.
$ipdEsAdmin = in_array($ipdRuta, ['admin', 'iglesias', 'zonas', 'usuarios', 'resumen', 'mi_perfil'], true);
$ipdRol = strtolower((string) ($_SESSION['usuario_rol'] ?? 'usuario'));
if ($ipdRol === 'admin') $ipdRol = 'administrador';
$ipdInicioPanel = $ipdRol === 'administrador' ? 'resumen' : 'mi_perfil';
$ipdImg = static function (string $archivo): ?string {
    return is_file(__DIR__.'/../../../public/assets/img/'.$archivo) ? 'assets/img/'.$archivo : null;
};
$ipdLogo = $ipdImg('logo-IDP-B.png');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>IDP D-2 | Iglesia de Dios de la Profecía</title>
    <meta name="description" content="Iglesia de Dios de la Profecía · Distrito 2. Encuentra tu iglesia y acompaña los cultos en vivo.">
    <script>
        // Aplica las preferencias de accesibilidad antes de pintar la página.
        try {
            var ipdAcc = JSON.parse(localStorage.getItem('ipd-accesibilidad')) || {};
            ['letra-grande', 'alto-contraste'].forEach(function (c) {
                if (ipdAcc[c]) document.documentElement.classList.add(c);
            });
            // Tema del panel de administración (claro u oscuro).
            if (localStorage.getItem('ipd-tema-panel') === 'claro') {
                document.documentElement.classList.add('tema-claro');
            }
        } catch (e) {}
        <?php if ($ipdEsAdmin): ?>
        // Bootstrap usa data-bs-theme para sus colores claros u oscuros (admin.js lo cambia con el botón ☀/☾).
        document.documentElement.setAttribute('data-bs-theme',
            document.documentElement.classList.contains('tema-claro') ? 'light' : 'dark');
        <?php endif; ?>
    </script>
    <?php if ($ipdEsAdmin): ?>
    <!-- Bootstrap 5.3.3 solo en el panel. Va ANTES de style.css para que los estilos propios del sitio tengan prioridad. -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?php endif; ?>
    <!-- <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,600;1,8..60,400;1,8..60,600&family=Space+Grotesk:wght@400;500;600;700&display=swap"> -->
    <link rel="stylesheet" href="assets/css/style.css?v=4">
    <?php if ($ipdLogo): ?><link rel="icon" href="<?= htmlspecialchars($ipdLogo) ?>"><?php endif; ?>
    <script src="assets/js/sitio.js" defer></script>
    <?php if ($ipdRuta === 'login'): ?>
    <script src="assets/js/validaciones.js" defer></script>
    <!-- Crear cuenta: va después de validaciones.js para respetar sus errores -->
    <script src="assets/js/registro.js" defer></script>
    <?php endif; ?>
</head>
<?php // con-hero: páginas cuyo hero oscuro queda detrás del header transparente ?>
<?php if ($ipdEsAdmin): ?>
<body class="pagina-admin">
<a class="saltar" href="#contenido">Saltar al contenido</a>
<?php require __DIR__.'/admin-header.php'; return; // El panel usa su propio layout ?>
<?php endif; ?>
<body class="<?= $ipdEsInicio ? 'pagina-inicio' : 'pagina-interna' ?><?= in_array($ipdRuta, ['inicio', 'encuentranos', 'transmisiones'], true) ? ' con-hero' : '' ?>">
<a class="saltar" href="#contenido">Saltar al contenido</a>
<?php if ($ipdRuta === 'login'): ?>
<!-- Header reducido solo para la página de acceso -->
<header class="nav-simple">
    <a class="nav-simple-marca" href="index.php">IDP-D2 <span>Iglesia de Dios de la Profecía D-2</span></a>
    <a class="nav-simple-volver" href="index.php">Volver al inicio</a>
</header>
<?php else: ?>
<header class="nav" id="nav">
    <a class="brand" href="index.php" aria-label="IDP D-2, ir al inicio">
        <?php if ($ipdLogo): ?>
            <img class="brand-logo" src="<?= htmlspecialchars($ipdLogo) ?>" alt="">
        <?php else: ?>
            <span class="brand-logo brand-emblema" aria-hidden="true">✦</span>
        <?php endif; ?>
        <strong>IDP D-2</strong>
        <span class="brand-sub">Iglesia de Dios de la Profecía · D-2</span>
    </a>

    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu" aria-label="Abrir menú">
        <span></span><span></span><span></span>
    </button>

    <nav class="menu" id="menu" aria-label="Principal">
        <a class="capsula<?= $ipdEsInicio ? ' activa' : '' ?>" href="index.php#inicio" data-seccion="inicio">Inicio</a>
        <a class="capsula" href="index.php#nosotros" data-seccion="nosotros">Valores</a>
        <a class="capsula<?= $ipdRuta === 'encuentranos' ? ' activa' : '' ?>" href="index.php?r=encuentranos">Iglesias</a>
        <a class="capsula capsula-vivo<?= $ipdRuta === 'transmisiones' ? ' activa' : '' ?>" href="index.php?r=transmisiones">
            <span class="punto-vivo" aria-hidden="true"></span>Sala de Transmisiones
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
        </a>

        <div class="desplegable">
            <button class="capsula desplegable-boton" type="button" aria-expanded="false" aria-haspopup="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="7" r="1.2" fill="currentColor"/><path d="M7.5 10h9M12 10v4m0 0-2.5 4M12 14l2.5 4"/></svg>
                Accesibilidad
                <svg class="flecha" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 10h10l-5 5z"/></svg>
            </button>
            <div class="desplegable-menu" hidden>
                <button type="button" data-acc="letra-grande" aria-pressed="false">Letra grande</button>
                <button type="button" data-acc="alto-contraste" aria-pressed="false">Alto contraste</button>
            </div>
        </div>

        <?php if (!empty($_SESSION['usuario_id'])): ?>
            <div class="desplegable">
                <button class="capsula desplegable-boton<?= $ipdEsAdmin ? ' activa' : '' ?>" type="button" aria-expanded="false" aria-haspopup="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M6.2 18.8a7 7 0 0 1 11.6 0"/></svg>
                    Panel
                    <svg class="flecha" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 10h10l-5 5z"/></svg>
                </button>
                <div class="desplegable-menu" hidden>
                    <?php if ($ipdRol === 'administrador'): ?>
                        <a href="index.php?r=resumen">Resumen</a>
                        <a href="index.php?r=admin">Transmisiones</a>
                        <a href="index.php?r=iglesias">Iglesias</a>
                        <a href="index.php?r=zonas">Zonas</a>
                        <a href="index.php?r=usuarios">Usuarios</a>
                    <?php else: ?>
                        <a href="index.php?r=mi_perfil">Mi perfil</a>
                    <?php endif; ?>
                    <form method="post" action="index.php?r=salir">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                        <button type="submit">Cerrar sesión</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <a class="capsula<?= $ipdRuta === 'login' ? ' activa' : '' ?>" href="index.php?r=login">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M6.2 18.8a7 7 0 0 1 11.6 0"/></svg>
                Ingresar
            </a>
        <?php endif; ?>
    </nav>
</header>
<?php endif; ?>
<main id="contenido">
<?php // En login el error se muestra dentro del formulario (ver auth/login.php). ?>
<?php if ($ipdRuta !== 'login' && !empty($_SESSION['error'])): ?>
    <div class="alert" role="alert"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
