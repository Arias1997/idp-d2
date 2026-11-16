<?php if (!empty($ipdEsAdmin)) { require __DIR__.'/admin-footer.php'; return; } // Cierre del layout del panel ?>
</main> 

<?php if (($ipdRuta ?? '') === 'login'): ?>
<!-- Footer reducido solo para la página de acceso -->
<footer class="pie-simple">
    <span>IDP-D2 · Un espacio para nuestra comunidad.</span>
    <a href="index.php">Volver al inicio</a>
</footer>

<?php else: ?>
<footer class="pie">
    <div class="pie-contenido">
        <div class="pie-superior">
            <div class="pie-frase">
                <div class="eyebrow">IDP · D-2</div>
                <p class="pie-titulo">Un lugar para crecer en fe y compartir esperanza.</p>
                <p>Nos alegra que formes parte de nuestra comunidad.</p>
            </div>
            <div class="pie-invitacion">
                <h2>Hay un lugar para ti</h2>
                <p>Encuentra la iglesia más cercana en Sacaba o acompaña los cultos en vivo desde tu casa.</p>
                <a class="btn btn-borde-oro" href="index.php?r=encuentranos">
                    Visitanos
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5"/></svg>
                </a>
                <div class="redes">
                    <!-- Cambia el # por el enlace real de cada red social. -->
                    <a href="https://www.facebook.com/profile.php?id=61552980030797" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-1.6 19.9V14.9H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.5 2.9h-2.3v7A10 10 0 0 0 12 2z"/></svg>
                    </a>
                    <a href="#" aria-label="YouTube" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 8.2a3 3 0 0 0-2.1-2.1C18 5.6 12 5.6 12 5.6s-6 0-7.9.5A3 3 0 0 0 2 8.2 31 31 0 0 0 1.6 12a31 31 0 0 0 .4 3.8 3 3 0 0 0 2.1 2.1c1.9.5 7.9.5 7.9.5s6 0 7.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .4-3.8 31 31 0 0 0-.4-3.8zM10 15V9l5.2 3z"/></svg>
                    </a>
                    <a href="https://www.tiktok.com/@idp_distrito2?_t=zs-8u7bxit47ky&_r=1" aria-label="TikTok" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.5 3a4.6 4.6 0 0 0 4 4v3.2a7.7 7.7 0 0 1-4-1.2v6.3A6.2 6.2 0 1 1 10.3 9v3.3a3 3 0 1 0 2.9 3V3z"/></svg>
                    </a>
                    <a href="#" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".6" fill="currentColor"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="pie-inferior">
            <div class="pie-marca">
                <p class="pie-logo">IDP D-2<span>.</span></p>
                <p>Iglesia de Dios de la Profecía · D-2</p>
                <p class="pie-copy">© <?= date('Y') ?> IDP D-2. Unidos en fe, cerca de ti. Sitio oficial: <a href="https://idpbolivia.org" target="_blank" rel="noopener noreferrer">idpbolivia.org</a></p>
            </div>
            <nav class="pie-enlaces" aria-label="Enlaces del pie">
                <a href="index.php#inicio">Inicio</a>
                <a href="index.php#nosotros">Sobre nosotros</a>
                <a href="index.php?r=encuentranos">Encuéntranos</a>
                <a href="index.php?r=transmisiones">Sala LIVE</a>
                <?php if (!empty($_SESSION['usuario_id'])): ?>
                    <a href="index.php?r=admin">Panel</a>
                <?php else: ?>
                    <a href="index.php?r=login">Ingresar</a>
                <?php endif; ?>
                <a class="subir" href="#" aria-label="Volver arriba">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 19V5m-6 6 6-6 6 6"/></svg>
                </a>
            </nav>
        </div>
    </div>
</footer>
<?php endif; ?>
</body>
</html>
