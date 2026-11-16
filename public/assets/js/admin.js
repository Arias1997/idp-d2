// Panel de administración: menú lateral en celular, filtros y búsqueda en tablas.
(function () {
    // ---------- Menú lateral en celulares ----------
    const boton = document.querySelector('.admin-menu-boton');
    const velo = document.querySelector('.admin-velo');
    function lateral(abrir) {
        document.body.classList.toggle('lateral-abierto', abrir);
        if (boton) boton.setAttribute('aria-expanded', abrir);
        if (velo) velo.hidden = !abrir;
    }
    if (boton) boton.addEventListener('click', function () {
        lateral(!document.body.classList.contains('lateral-abierto'));
    });
    if (velo) velo.addEventListener('click', function () { lateral(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') lateral(false);
    });

    // ---------- Modo claro / oscuro del panel ----------
    const botonTema = document.querySelector('[data-tema]');
    function marcarTema() {
        const claro = document.documentElement.classList.contains('tema-claro');
        botonTema.setAttribute('aria-pressed', claro);
        botonTema.setAttribute('aria-label', claro ? 'Cambiar a modo oscuro' : 'Cambiar a modo claro');
        // Bootstrap cambia sus colores (tablas, tarjetas, campos) con data-bs-theme.
        document.documentElement.setAttribute('data-bs-theme', claro ? 'light' : 'dark');
    }
    if (botonTema) {
        marcarTema();
        botonTema.addEventListener('click', function () {
            const claro = document.documentElement.classList.toggle('tema-claro');
            try { localStorage.setItem('ipd-tema-panel', claro ? 'claro' : 'oscuro'); } catch (e) {}
            marcarTema();
        });
    }

    // ---------- Filtros (select data-filtro) y búsqueda (input data-buscar) ----------
    // Cada fila <tr> lleva data-estado, data-iglesia, data-zona... según la tabla.
    function aplicarFiltros(idTabla) {
        const tabla = document.getElementById(idTabla);
        if (!tabla) return;
        const filtros = document.querySelectorAll('[data-tabla="' + idTabla + '"]');
        const buscador = document.querySelector('[data-buscar="' + idTabla + '"]');
        const texto = buscador ? buscador.value.trim().toLowerCase() : '';
        let visibles = 0;

        tabla.querySelectorAll('tbody tr').forEach(function (fila) {
            let mostrar = true;
            filtros.forEach(function (filtro) {
                if (filtro.value !== '' && fila.dataset[filtro.dataset.filtro] !== filtro.value) mostrar = false;
            });
            if (texto && !fila.textContent.toLowerCase().includes(texto)) mostrar = false;
            fila.hidden = !mostrar;
            if (mostrar) visibles++;
        });

        const aviso = tabla.parentElement.querySelector('.admin-sin-resultados');
        if (aviso) aviso.hidden = visibles > 0;
    }

    document.querySelectorAll('[data-filtro]').forEach(function (filtro) {
        filtro.addEventListener('change', function () { aplicarFiltros(filtro.dataset.tabla); });
    });
    document.querySelectorAll('[data-buscar]').forEach(function (buscador) {
        buscador.addEventListener('input', function () { aplicarFiltros(buscador.dataset.buscar); });
    });

    // ---------- Ventanas de diálogo de los formularios (Transmisiones, Iglesias, Zonas) ----------
    // showModal() pone la ventana encima de todo, bloquea el fondo y la cierra con Esc.
    function abrirDialogo(dialogo) {
        if (!dialogo.open) dialogo.showModal();
        const primerCampo = dialogo.querySelector('input:not([type="hidden"]), select, textarea');
        if (primerCampo) primerCampo.focus();
        // Aviso para otros scripts (por ejemplo, el mapa de iglesias recalcula su tamaño)
        dialogo.dispatchEvent(new Event('dialogo-abierto'));
    }
    window.abrirDialogo = abrirDialogo; // La usa la vista de Transmisiones al editar

    document.querySelectorAll('dialog.admin-dialogo').forEach(function (dialogo) {
        // Cerrar con el botón ✕ o Cancelar
        dialogo.querySelectorAll('[data-cerrar-dialogo]').forEach(function (b) {
            b.addEventListener('click', function () { dialogo.close(); });
        });
        // Cerrar con clic en el fondo oscuro (fuera de la ventana)
        dialogo.addEventListener('click', function (e) {
            if (e.target === dialogo) dialogo.close();
        });
    });

    // Botones que abren una ventana: data-abrir-dialogo="id-del-dialogo"
    document.querySelectorAll('[data-abrir-dialogo]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const dialogo = document.getElementById(boton.dataset.abrirDialogo);
            if (dialogo) abrirDialogo(dialogo);
        });
    });

    // Se abre sola al cargar si el backend devolvió un error o si se está editando.
    // El mensaje de error se copia dentro de la ventana para que se vea.
    document.querySelectorAll('dialog[data-abrir-al-cargar]').forEach(function (dialogo) {
        const aviso = document.querySelector('.admin-contenido > .admin-aviso.error');
        const form = dialogo.querySelector('form');
        if (aviso && form) form.before(aviso.cloneNode(true));
        abrirDialogo(dialogo);
    });

    // ---------- Tooltips de Bootstrap (data-bs-toggle="tooltip") ----------
    // bootstrap.bundle.min.js se carga antes que este archivo (ver admin-footer.php).
    if (window.bootstrap) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    }

    // Enlace "...#nueva": viene de una edición y quiere abrir el formulario vacío.
    if (location.hash === '#nueva') {
        const dialogo = document.querySelector('dialog[data-dialogo-nuevo]');
        if (dialogo) {
            history.replaceState(null, '', location.pathname + location.search); // Quita el #nueva
            abrirDialogo(dialogo);
        }
    }
})();
