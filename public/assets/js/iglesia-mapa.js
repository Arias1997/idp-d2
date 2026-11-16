// Leaflet completa los campos ocultos de la iglesia al marcar un punto.
const latitudIglesia = document.getElementById('iglesia-latitud');
const longitudIglesia = document.getElementById('iglesia-longitud');
const estadoMapaIglesia = document.getElementById('iglesia-mapa-estado');
const botonQuitarUbicacion = document.getElementById('quitar-ubicacion');
let mapaIglesia;
let marcadorIglesia;
let falloMapaIglesia = false;

function ubicacionGuardadaIglesia() {
    if (latitudIglesia.value === '' || longitudIglesia.value === '') return null;
    const lat = Number(latitudIglesia.value);
    const lng = Number(longitudIglesia.value);
    if (!Number.isFinite(lat) || !Number.isFinite(lng) ||
        lat < -90 || lat > 90 || lng < -180 || lng > 180) return null;
    return {lat, lng};
}

function marcarUbicacionIglesia(posicion) {
    // Ajusta la longitud si el usuario se desplaza a otra copia del mundo.
    const punto = L.latLng(posicion.lat, posicion.lng).wrap();
    latitudIglesia.value = punto.lat.toFixed(7);
    longitudIglesia.value = punto.lng.toFixed(7);
    if (marcadorIglesia) {
        marcadorIglesia.setLatLng(punto);
    } else {
        marcadorIglesia = L.marker(punto, {title: 'Ubicación de la iglesia'}).addTo(mapaIglesia);
    }
    estadoMapaIglesia.textContent = 'Ubicación seleccionada. Pulsa el botón del formulario para guardar la ubicación.';
    botonQuitarUbicacion.disabled = false;
}

function errorMapaIglesia() {
    falloMapaIglesia = true;
    estadoMapaIglesia.textContent = 'El mapa no está disponible. Puedes guardar la iglesia sin ubicación o conservar la que ya seleccionaste.';
}

function iniciarMapaIglesia() {
    try {
        if (typeof L === 'undefined') {
            errorMapaIglesia();
            return;
        }
        const contenedor = document.getElementById('iglesia-mapa');
        const guardada = ubicacionGuardadaIglesia();
        const centro = guardada || {
            lat: Number(contenedor.dataset.latitud),
            lng: Number(contenedor.dataset.longitud)
        };
        mapaIglesia = L.map(contenedor, {
            minZoom: 3,
            maxZoom: 19,
            scrollWheelZoom: false
        }).setView([centro.lat, centro.lng], guardada ? 16 : Number(contenedor.dataset.zoom));

        const capa = L.tileLayer(contenedor.dataset.teselas, {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });
        capa.on('tileerror', function() {
            estadoMapaIglesia.textContent = 'No se pudo cargar parte del mapa. Comprueba tu conexión a Internet.';
        });
        capa.addTo(mapaIglesia);

        if (guardada) {
            marcarUbicacionIglesia(guardada);
        } else {
            estadoMapaIglesia.textContent = 'Haz clic en el mapa para ubicar la iglesia.';
        }
        mapaIglesia.on('click', function(evento) {
            if (evento.latlng) marcarUbicacionIglesia(evento.latlng);
        });
    } catch (error) {
        errorMapaIglesia();
    }
}

// También permite quitar una ubicación si el mapa no llega a cargar.
botonQuitarUbicacion.disabled = latitudIglesia.value === '' && longitudIglesia.value === '';
botonQuitarUbicacion.addEventListener('click', function() {
    latitudIglesia.value = '';
    longitudIglesia.value = '';
    if (marcadorIglesia) marcadorIglesia.remove();
    marcadorIglesia = null;
    estadoMapaIglesia.textContent = falloMapaIglesia
        ? 'Sin ubicación. Puedes guardar la iglesia.'
        : 'Ubicación quitada. Puedes marcar otro punto.';
    botonQuitarUbicacion.disabled = true;
});

iniciarMapaIglesia();

// El formulario está en una ventana de diálogo que empieza cerrada (tamaño 0).
// Al abrirla (evento de admin.js), Leaflet recalcula el tamaño y centra el marcador.
const dialogoIglesia = document.getElementById('dialogo-iglesia');
if (dialogoIglesia) {
    dialogoIglesia.addEventListener('dialogo-abierto', function() {
        if (!mapaIglesia) return;
        mapaIglesia.invalidateSize();
        if (marcadorIglesia) mapaIglesia.setView(marcadorIglesia.getLatLng(), 16);
    });
}
