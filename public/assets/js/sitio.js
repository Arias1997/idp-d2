// Comportamiento general del sitio: menú, desplegables, accesibilidad y scroll.
(function () {
    const raiz = document.documentElement;
    const CLAVE = 'ipd-accesibilidad';

    function leerPreferencias() {
        try { return JSON.parse(localStorage.getItem(CLAVE)) || {}; } catch (e) { return {}; }
    }
    function guardarPreferencias(p) {
        try { localStorage.setItem(CLAVE, JSON.stringify(p)); } catch (e) {}
    }

    // Accesibilidad: letra grande y alto contraste.
    const preferencias = leerPreferencias();
    document.querySelectorAll('[data-acc]').forEach(function (boton) {
        const clase = boton.dataset.acc;
        boton.setAttribute('aria-pressed', raiz.classList.contains(clase));
        boton.addEventListener('click', function () {
            const activo = raiz.classList.toggle(clase);
            boton.setAttribute('aria-pressed', activo);
            preferencias[clase] = activo;
            guardarPreferencias(preferencias);
        });
    });

    // Menús desplegables.
    function cerrarDesplegables(excepto) {
        document.querySelectorAll('.desplegable').forEach(function (d) {
            if (d === excepto) return;
            d.querySelector('.desplegable-menu').hidden = true;
            d.querySelector('.desplegable-boton').setAttribute('aria-expanded', 'false');
        });
    }
    document.querySelectorAll('.desplegable').forEach(function (d) {
        const boton = d.querySelector('.desplegable-boton');
        const menu = d.querySelector('.desplegable-menu');
        boton.addEventListener('click', function (e) {
            e.stopPropagation();
            const abrir = menu.hidden;
            cerrarDesplegables(d);
            menu.hidden = !abrir;
            boton.setAttribute('aria-expanded', abrir);
        });
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.desplegable')) cerrarDesplegables();
    });

    // Menú en celulares.
    const toggle = document.querySelector('.nav-toggle');
    const menu = document.getElementById('menu');
    function cerrarMenu() {
        document.body.classList.remove('menu-abierto');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }
    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            const abierto = document.body.classList.toggle('menu-abierto');
            toggle.setAttribute('aria-expanded', abierto);
            toggle.setAttribute('aria-label', abierto ? 'Cerrar menú' : 'Abrir menú');
        });
        menu.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', cerrarMenu); });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { cerrarDesplegables(); cerrarMenu(); }
    });

    // Header más sólido al bajar.
    const nav = document.getElementById('nav');
    if (nav) { // En login se usa un header reducido sin id="nav"
        const alHacerScroll = function () { nav.classList.toggle('nav-solida', window.scrollY > 40); };
        alHacerScroll();
        window.addEventListener('scroll', alHacerScroll, {passive: true});
    }

    // Botón Mostrar/Ocultar contraseña.
    document.querySelectorAll('[data-mostrar-clave]').forEach(function (boton) {
        const campo = document.getElementById(boton.dataset.mostrarClave);
        if (!campo) return;
        boton.addEventListener('click', function () {
            const mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            boton.textContent = mostrar ? 'Ocultar' : 'Mostrar';
            boton.setAttribute('aria-pressed', mostrar);
        });
    });

    // Volver arriba.
    document.querySelectorAll('.subir').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    });

    // Marca la cápsula de la sección visible en la página de inicio.
    if (document.body.classList.contains('pagina-inicio') && 'IntersectionObserver' in window) {
        const enlaces = Array.prototype.slice.call(document.querySelectorAll('.menu a[data-seccion]'));
        const observador = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (!entrada.isIntersecting) return;
                enlaces.forEach(function (a) {
                    a.classList.toggle('activa', a.dataset.seccion === entrada.target.id);
                });
            });
        }, {rootMargin: '-45% 0px -50% 0px'});
        enlaces.forEach(function (a) {
            const seccion = document.getElementById(a.dataset.seccion);
            if (seccion) observador.observe(seccion);
        });
    }
})();
