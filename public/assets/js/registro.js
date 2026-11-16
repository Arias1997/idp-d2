/* Registro público en la página de login.
   - Cambia entre "Iniciar sesión" y "Crear cuenta" (también con #registro en la URL).
   - Envía el formulario con fetch a index.php?r=registrar y muestra la respuesta JSON.
   Contrato del backend: docs/REGISTRO_BACKEND.md.
   validaciones.js revisa los campos antes; si encuentra errores, aquí no se envía nada. */
(function () {
    'use strict';
    const ingresar = document.getElementById('acceso-ingresar');
    const registro = document.getElementById('acceso-registro');
    const form = document.querySelector('form[data-registro]');
    if (!ingresar || !registro || !form) return;

    const enlace = document.querySelector('[data-registro-enlace]');
    const avisoError = registro.querySelector('[data-registro-error]');
    const avisoExito = ingresar.querySelector('[data-registro-exito]');
    const boton = form.querySelector('[data-registro-boton]');
    const textoBoton = boton.textContent;

    // Con JavaScript activo se ofrece la opción de crear cuenta.
    if (enlace) enlace.hidden = false;

    function mostrar(seccion, enfocar) {
        const esRegistro = seccion === 'registro';
        registro.hidden = !esRegistro;
        ingresar.hidden = esRegistro;
        if (enfocar) (esRegistro ? registro : ingresar).querySelector('input:not([type="hidden"])').focus();
    }

    document.querySelectorAll('[data-ir-a]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            history.replaceState(null, '', '#' + a.dataset.irA);
            mostrar(a.dataset.irA, true);
        });
    });
    if (location.hash === '#registro') mostrar('registro', false);

    function error(mensaje) {
        avisoError.textContent = mensaje;
        avisoError.hidden = false;
        avisoError.scrollIntoView({block: 'nearest'});
    }

    // Pide un token CSRF nuevo (GET registro) por si la sesión cambió.
    async function renovarToken() {
        const r = await fetch('index.php?r=registro', {credentials: 'same-origin', headers: {Accept: 'application/json'}});
        const datos = await r.json();
        if (datos.ok && datos.csrf) form.elements.csrf.value = datos.csrf;
        return datos;
    }

    async function enviar() {
        return fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: {Accept: 'application/json'}
        });
    }

    form.addEventListener('submit', async function (evento) {
        // validaciones.js ya canceló el envío si había errores en los campos.
        if (evento.defaultPrevented) return;
        evento.preventDefault();
        avisoError.hidden = true;
        boton.disabled = true;
        boton.textContent = 'Creando cuenta…';

        try {
            let respuesta = await enviar();
            // 403 = token vencido: se renueva una vez y se reintenta.
            if (respuesta.status === 403) {
                const token = await renovarToken();
                if (!token.ok) return error(token.mensaje || 'No se pudo crear la cuenta.');
                respuesta = await enviar();
            }
            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.ok) {
                error(datos.mensaje || 'No se pudo crear la cuenta. Inténtalo nuevamente.');
                return;
            }

            // Cuenta creada: vuelve a "Iniciar sesión" con el correo ya escrito.
            const correo = form.elements.correo.value.trim().toLowerCase();
            form.reset();
            avisoExito.textContent = datos.mensaje || 'Cuenta creada. Ya puedes iniciar sesión.';
            avisoExito.hidden = false;
            const correoLogin = ingresar.querySelector('input[name="correo"]');
            if (correoLogin) correoLogin.value = correo;
            history.replaceState(null, '', '#ingresar');
            mostrar('ingresar', false);
            const claveLogin = ingresar.querySelector('input[name="clave"]');
            if (claveLogin) claveLogin.focus();
        } catch (_) {
            error('No se pudo conectar con el servidor. Revisa tu conexión e inténtalo nuevamente.');
        } finally {
            boton.disabled = false;
            boton.textContent = textoBoton;
        }
    });
})();
