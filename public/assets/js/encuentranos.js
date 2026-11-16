// Página Encuéntranos: carrusel del hero, filtro por zona y mapa de iglesias.
(function () {
    // ---------- Carrusel: cambia de foto cada 6 segundos ----------
    const fotos = document.querySelectorAll('.enc-slide');
    const reducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (fotos.length > 1 && !reducirMovimiento) {
        let actual = 0;
        setInterval(function () {
            fotos[actual].classList.remove('activa');
            actual = (actual + 1) % fotos.length;
            fotos[actual].classList.add('activa');
        }, 6000);
    }

    // ---------- Filtro por zona ----------
    const filtros = document.querySelectorAll('.enc-filtro');
    const tarjetas = document.querySelectorAll('.enc-iglesia');
    filtros.forEach(function (boton) {
        boton.addEventListener('click', function () {
            filtros.forEach(function (b) { b.setAttribute('aria-pressed', b === boton); });
            const zona = boton.dataset.zona;
            tarjetas.forEach(function (t) {
                t.hidden = zona !== '' && t.dataset.zona !== zona;
            });
        });
    });

    // ---------- Mapa ----------
    const contenedor = document.getElementById('mapa-encuentranos');
    const marcadores = {};
    let mapa = null;

    // Botón "Abrir en Google Maps" debajo del mapa.
    // Empieza con el enlace general del distrito y cambia a la iglesia elegida.
    const botonGoogle = document.getElementById('abrir-google-maps');
    function apuntarGoogleMaps(iglesia) {
        if (!botonGoogle) return;
        botonGoogle.href = 'https://www.google.com/maps/search/?api=1&query=' + iglesia.lat + ',' + iglesia.lng;
        botonGoogle.textContent = 'Abrir ' + iglesia.nombre + ' en Google Maps';
    }

    if (contenedor && typeof L !== 'undefined') {
        let iglesias = [];
        try { iglesias = JSON.parse(contenedor.dataset.iglesias); } catch (e) {}

        mapa = L.map(contenedor, {scrollWheelZoom: false}).setView(
            [Number(contenedor.dataset.latitud), Number(contenedor.dataset.longitud)],
            Number(contenedor.dataset.zoom)
        );
        L.tileLayer(contenedor.dataset.teselas, {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(mapa);

        const puntos = [];
        iglesias.forEach(function (iglesia) {
            // Se arma con textContent para no interpretar HTML que venga de la base de datos.
            const popup = document.createElement('div');
            const nombre = document.createElement('strong');
            nombre.textContent = iglesia.nombre;
            const zona = document.createElement('div');
            zona.textContent = iglesia.zona;
            const direccion = document.createElement('div');
            direccion.textContent = iglesia.direccion;
            const ruta = document.createElement('a');
            ruta.href = 'https://www.google.com/maps/dir/?api=1&destination=' + iglesia.lat + ',' + iglesia.lng;
            ruta.target = '_blank';
            ruta.rel = 'noopener noreferrer';
            ruta.textContent = 'Cómo llegar →';
            popup.append(nombre, zona, direccion, ruta);

            marcadores[iglesia.indice] = L.marker([iglesia.lat, iglesia.lng], {title: iglesia.nombre})
                .addTo(mapa)
                .bindPopup(popup)
                // Al abrir el globo de una iglesia, el botón de abajo apunta a esa iglesia
                .on('popupopen', function () { apuntarGoogleMaps(iglesia); });
            puntos.push([iglesia.lat, iglesia.lng]);
        });

        if (puntos.length > 1) {
            mapa.fitBounds(puntos, {padding: [40, 40], maxZoom: 15});
        } else if (puntos.length === 1) {
            mapa.setView(puntos[0], 15);
        }
    } else if (contenedor) {
        contenedor.classList.add('enc-mapa-vacio');
        contenedor.textContent = 'No se pudo cargar el mapa. Comprueba tu conexión a Internet.';
    }

    // ---------- Botón "Ver en mapa" de cada tarjeta ----------
    document.querySelectorAll('[data-ver-mapa]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const marcador = marcadores[boton.dataset.verMapa];
            if (!mapa || !marcador) return;
            contenedor.scrollIntoView({behavior: 'smooth', block: 'center'});
            mapa.setView(marcador.getLatLng(), 16);
            marcador.openPopup();
        });
    });
})();
