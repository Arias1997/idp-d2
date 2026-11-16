/* Validaciones de formularios. PHP repite las reglas antes de guardar. */
(function (global) {
    'use strict';
    const texto = valor => typeof valor === 'string' ? valor.trim() : '';
    const longitud = valor => Array.from(valor).length;
    const enteroPositivo = valor => /^[1-9]\d*$/.test(texto(valor));
    const correoValido = valor => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);

    function urlRedValida(valor, dominios) {
        if (!valor) return true;
        if (valor.length > 500 || !/^https?:\/\//i.test(valor) || /[\s\u0000-\u001f\u007f]/.test(valor) || valor.includes('\\') || /^https?:\/\/[^/?#]*@/i.test(valor)) return false;
        try {
            const url = new URL(valor);
            return dominios.includes(url.hostname.toLowerCase()) && !url.username && !url.password;
        } catch (_) { return false; }
    }

    function videoId(valor) {
        if (/^[A-Za-z0-9_-]{11}$/.test(valor)) return valor;
        if (!/^https?:\/\//i.test(valor) || /\s/.test(valor)) return null;
        try {
            const url = new URL(valor);
            const host = url.hostname.toLowerCase();
            let id = null;
            if (['youtu.be', 'www.youtu.be'].includes(host)) {
                id = url.pathname.replace(/^\/+|\/+$/g, '');
            } else if (['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'].includes(host)) {
                const ruta = url.pathname.match(/^\/(?:live|embed|shorts)\/([A-Za-z0-9_-]{11})\/?$/);
                id = url.pathname === '/watch' ? url.searchParams.get('v') : (ruta ? ruta[1] : null);
            }
            return typeof id === 'string' && /^[A-Za-z0-9_-]{11}$/.test(id) ? id : null;
        } catch (_) { return null; }
    }

    function fechaValida(valor) {
        if (!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(valor)) return false;
        const partes = valor.match(/\d+/g).map(Number);
        const fecha = new Date(valor);
        return !Number.isNaN(fecha.getTime()) && fecha.getFullYear() === partes[0] &&
            fecha.getMonth() + 1 === partes[1] && fecha.getDate() === partes[2] &&
            fecha.getHours() === partes[3] && fecha.getMinutes() === partes[4];
    }

    function validarDatos(tipo, datos, zonas) {
        const errores = {};
        function campo(nombre, minimo, maximo) {
            const valor = texto(datos[nombre]);
            if (longitud(valor) < minimo || longitud(valor) > maximo) {
                errores[nombre] = minimo ? 'Completa este campo (máximo ' + maximo + ' caracteres).' : 'Máximo ' + maximo + ' caracteres.';
            }
            return valor;
        }
        if (tipo === 'login') {
            if (!correoValido(texto(datos.correo))) errores.correo = 'Escribe un correo válido.';
            if (typeof datos.clave !== 'string' || !datos.clave) errores.clave = 'Escribe tu contraseña.';
        }
        if (tipo === 'registro') {
            // Mismas reglas que UsuarioValidator::validarRegistro en PHP.
            campo('nombre', 1, 120);
            const correo = texto(datos.correo);
            if (!correoValido(correo) || new TextEncoder().encode(correo).length > 150) errores.correo = 'Escribe un correo válido de hasta 150 caracteres.';
            const clave = typeof datos.clave === 'string' ? datos.clave : '';
            if (longitud(clave) < 8 || new TextEncoder().encode(clave).length > 72 || clave.includes('\0')) errores.clave = 'Usa al menos 8 caracteres y como máximo 72 bytes.';
            else if (datos.confirmar_clave !== clave) errores.confirmar_clave = 'Las contraseñas no coinciden.';
        }
        if (tipo === 'zona') {
            const nombre = campo('nombre', 1, 100).toLowerCase();
            if ((zonas || []).some(z => String(z.id) !== String(datos.id || '0') && z.nombre.trim().toLowerCase() === nombre)) {
                errores.nombre = 'Ya existe una zona con ese nombre.';
            }
        }
        if (tipo === 'iglesia') {
            if (!enteroPositivo(datos.zona_id)) errores.zona_id = 'Selecciona una zona.';
            campo('nombre', 1, 150);
            campo('direccion', 1, 200);
            campo('pastor', 0, 120);
            const telefono = campo('telefono', 0, 25);
            if (telefono && !/^[0-9 +\-]+$/.test(telefono)) errores.telefono = 'Usa números, espacios, + y -.';
            campo('horarios', 0, 5000);
            const latitud = texto(datos.latitud), longitudMapa = texto(datos.longitud);
            if (Boolean(latitud) !== Boolean(longitudMapa)) errores.latitud = 'Marca una ubicación completa o usa Quitar ubicación.';
            [['latitud', latitud, 90], ['longitud', longitudMapa, 180]].forEach(([nombre, valor, limite]) => {
                if (valor && (!/^-?\d{1,3}(?:\.\d{1,7})?$/.test(valor) || Math.abs(Number(valor)) > limite)) {
                    errores[nombre] = 'Coordenada inválida; vuelve a marcar el punto en el mapa.';
                }
            });
        }
        if (tipo === 'transmision') {
            campo('titulo', 1, 150);
            if (!enteroPositivo(datos.iglesia_id)) errores.iglesia_id = 'Selecciona una iglesia.';
            if (!fechaValida(texto(datos.fecha_hora))) errores.fecha_hora = 'Selecciona una fecha y hora válidas.';
            if (!videoId(texto(datos.youtube))) errores.youtube = 'Escribe un enlace de YouTube con un video válido.';
            if (!['programada', 'en_vivo', 'finalizada'].includes(datos.estado)) errores.estado = 'Selecciona un estado válido.';
            if (!urlRedValida(texto(datos.facebook), ['facebook.com', 'www.facebook.com', 'm.facebook.com', 'fb.watch'])) errores.facebook = 'Escribe un enlace HTTP/HTTPS de Facebook, máximo 500 caracteres.';
            if (!urlRedValida(texto(datos.tiktok), ['tiktok.com', 'www.tiktok.com', 'vm.tiktok.com'])) errores.tiktok = 'Escribe un enlace HTTP/HTTPS de TikTok, máximo 500 caracteres.';
        }
        if (tipo === 'usuario') {
            campo('nombre', 1, 120);
            const email = campo('email', 1, 150);
            if (!correoValido(email)) errores.email = 'Escribe un correo válido.';
            const clave = typeof datos.clave === 'string' ? datos.clave : '';
            if (longitud(clave) < 8 || new TextEncoder().encode(clave).length > 72 || clave.includes('\0')) errores.clave = 'Usa al menos 8 caracteres y como máximo 72 bytes.';
        }
        if (tipo === 'usuario' || tipo === 'rol') {
            if (!['administrador', 'usuario'].includes(datos.rol)) errores.rol = 'Selecciona administrador o usuario.';
            if (tipo === 'rol' && !enteroPositivo(datos.usuario_id)) errores.usuario_id = 'Selecciona una cuenta válida.';
        }
        return errores;
    }

    global.IDPValidaciones = { validarDatos, videoId, urlRedValida, fechaValida };
    if (typeof document === 'undefined') return;
    let numeroError = 0;

    document.querySelectorAll('form[data-validar]').forEach(function (form) {
        form.noValidate = true;
        function limpiar(campo) {
            if (!campo || !campo.name) return;
            campo.classList.remove('is-invalid');
            campo.removeAttribute('aria-invalid');
            const aviso = form.querySelector('[data-error-campo="' + campo.name + '"]');
            if (aviso) {
                const ids = (campo.getAttribute('aria-describedby') || '').split(' ').filter(id => id && id !== aviso.id);
                if (ids.length) campo.setAttribute('aria-describedby', ids.join(' '));
                else campo.removeAttribute('aria-describedby');
                aviso.remove();
            }
        }
        function limpiarTodo() { Array.from(form.elements).forEach(limpiar); }
        form.addEventListener('input', e => limpiar(e.target));
        form.addEventListener('change', e => limpiar(e.target));
        form.addEventListener('reset', limpiarTodo);
        const dialogo = form.closest('dialog');
        if (dialogo) dialogo.addEventListener('dialogo-abierto', limpiarTodo);

        form.addEventListener('submit', function (evento) {
            limpiarTodo();
            const datos = Object.fromEntries(new FormData(form).entries());
            const zonas = Array.from(document.querySelectorAll('[data-zona-id]')).map(fila => ({
                id: fila.dataset.zonaId, nombre: fila.dataset.zonaNombre
            }));
            const errores = validarDatos(form.dataset.validar, datos, zonas);
            let primerCampo = null;
            Object.entries(errores).forEach(function ([nombre, mensaje]) {
                const campo = form.elements.namedItem(nombre);
                if (!campo || !campo.classList) return;
                campo.classList.add('is-invalid');
                campo.setAttribute('aria-invalid', 'true');
                const aviso = document.createElement('div');
                aviso.className = 'invalid-feedback';
                aviso.id = 'error-validacion-' + (++numeroError);
                aviso.dataset.errorCampo = nombre;
                aviso.textContent = mensaje;
                campo.setAttribute('aria-describedby', ((campo.getAttribute('aria-describedby') || '') + ' ' + aviso.id).trim());
                (campo.closest('.campo-clave') || campo).after(aviso);
                primerCampo = primerCampo || (campo.type === 'hidden' ? form.querySelector('#iglesia-mapa, select, input:not([type="hidden"])') : campo);
            });
            if (Object.keys(errores).length) {
                evento.preventDefault();
                if (primerCampo) {
                    if (primerCampo.id === 'iglesia-mapa') primerCampo.tabIndex = -1;
                    primerCampo.focus();
                }
            }
        });
    });
})(typeof window !== 'undefined' ? window : globalThis);
