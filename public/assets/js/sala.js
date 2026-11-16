// Sala de transmisiones: botón Compartir.
// Usa el menú de compartir del celular; en computadoras copia el enlace.
(function () {
    document.querySelectorAll('[data-compartir]').forEach(function (boton) {
        const texto = boton.querySelector('span');
        boton.addEventListener('click', function () {
            const datos = {title: boton.dataset.titulo, url: window.location.href};
            if (navigator.share) {
                navigator.share(datos).catch(function () {}); // Si el usuario cancela, no pasa nada
                return;
            }
            if (navigator.clipboard) {
                navigator.clipboard.writeText(datos.url).then(function () {
                    texto.textContent = '¡Enlace copiado!';
                    setTimeout(function () { texto.textContent = 'Compartir'; }, 2500);
                }).catch(function () {
                    window.prompt('Copia este enlace:', datos.url);
                });
            } else {
                window.prompt('Copia este enlace:', datos.url);
            }
        });
    });
})();
