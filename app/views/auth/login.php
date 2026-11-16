<?php
// Acceso de administradores y usuarios. El formulario envía a index.php?r=entrar
// con los campos: csrf, correo y clave (no cambiar estos nombres).
// El bloque "Crear cuenta" usa el registro público del backend (docs/REGISTRO_BACKEND.md):
// public/assets/js/registro.js lo envía con fetch a index.php?r=registrar y muestra la respuesta JSON.
$logoLogin = $ipdImg('logo-IDP-D2.png') ?? $ipdLogo;
$errorLogin = $_SESSION['error'] ?? '';
$correoLogin = $_SESSION['login_correo'] ?? '';
unset($_SESSION['error'], $_SESSION['login_correo']);
?>
<section class="acceso">
    <div class="acceso-tarjeta">
        <!-- Panel izquierdo oscuro -->
        <div class="acceso-panel" aria-hidden="true">
            <div class="acceso-eyebrow">Sé parte de nuestra comunidad</div>
            <div class="acceso-logo">
                <?php if ($logoLogin): ?>
                    <img src="<?= htmlspecialchars($logoLogin) ?>" alt="">
                <?php else: ?>
                    <span>IDP-D2</span>
                <?php endif; ?>
            </div>
            <p class="acceso-iglesia">Iglesia de Dios de la Profecía D-2</p>
            <p class="acceso-lema">Unidos en fe, cerca de ti.</p>
            <p class="acceso-sub">Bienvenido a tu espacio de comunidad.</p>
        </div>

        <!-- Formulario: Iniciar sesión -->
        <div class="acceso-formulario" id="acceso-ingresar">
            <h1>Iniciar sesión</h1>
            <p class="acceso-intro">Ingresa con tu cuenta para acceder a tu espacio.</p>

            <!-- Mensaje verde después de crear una cuenta (lo llena registro.js) -->
            <div class="acceso-exito" role="status" data-registro-exito hidden></div>

            <?php if ($errorLogin !== ''): ?>
                <div class="acceso-error" role="alert"><?= htmlspecialchars($errorLogin) ?></div>
            <?php endif; ?>

            <form method="post" action="index.php?r=entrar" data-validar="login">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

                <label for="correo">Correo electrónico</label>
                <input id="correo" type="email" name="correo" required maxlength="150" autocomplete="username" placeholder="nombre@correo.com" value="<?= htmlspecialchars($correoLogin) ?>">

                <label for="clave">Contraseña</label>
                <div class="campo-clave">
                    <input id="clave" type="password" name="clave" required autocomplete="current-password" placeholder="Ingresa tu contraseña">
                    <button type="button" data-mostrar-clave="clave" aria-pressed="false" aria-label="Mostrar u ocultar contraseña">Mostrar</button>
                </div>

                <button class="btn acceso-boton" type="submit">Iniciar sesión</button>
            </form>

            <!-- Solo se muestra con JavaScript activo: el registro funciona con fetch -->
            <p class="acceso-cambiar" data-registro-enlace hidden>
                ¿No tienes una cuenta?
                <a href="#registro" data-ir-a="registro">Crear cuenta</a>
            </p>
            <p class="acceso-ayuda">¿Necesitas ayuda para acceder?<br>Contacta al administrador del distrito.</p>
        </div>

        <!-- Formulario: Crear cuenta (rol usuario). Oculto hasta que se elige "Crear cuenta". -->
        <div class="acceso-formulario" id="acceso-registro" hidden>
            <h1>Crear cuenta</h1>
            <p class="acceso-intro">Regístrate para ver tu perfil en el Distrito 2.</p>

            <div class="acceso-error" role="alert" data-registro-error hidden></div>

            <form method="post" action="index.php?r=registrar" data-validar="registro" data-registro>
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

                <label for="registro-nombre">Nombre completo</label>
                <input id="registro-nombre" type="text" name="nombre" required maxlength="120" autocomplete="name" placeholder="Tu nombre y apellido">

                <label for="registro-correo">Correo electrónico</label>
                <input id="registro-correo" type="email" name="correo" required maxlength="150" autocomplete="email" placeholder="nombre@correo.com">

                <label for="registro-clave">Contraseña</label>
                <div class="campo-clave">
                    <input id="registro-clave" type="password" name="clave" required minlength="8" autocomplete="new-password" placeholder="Mínimo 8 caracteres" aria-describedby="registro-clave-ayuda">
                    <button type="button" data-mostrar-clave="registro-clave" aria-pressed="false" aria-label="Mostrar u ocultar contraseña">Mostrar</button>
                </div>
                <small class="acceso-nota" id="registro-clave-ayuda">Usa al menos 8 caracteres.</small>

                <label for="registro-confirmar">Confirmar contraseña</label>
                <div class="campo-clave">
                    <input id="registro-confirmar" type="password" name="confirmar_clave" required autocomplete="new-password" placeholder="Repite tu contraseña">
                    <button type="button" data-mostrar-clave="registro-confirmar" aria-pressed="false" aria-label="Mostrar u ocultar contraseña">Mostrar</button>
                </div>

                <button class="btn acceso-boton" type="submit" data-registro-boton>Crear cuenta</button>
            </form>

            <p class="acceso-cambiar">
                ¿Ya tienes una cuenta?
                <a href="#ingresar" data-ir-a="ingresar">Iniciar sesión</a>
            </p>
        </div>
    </div>
</section>
