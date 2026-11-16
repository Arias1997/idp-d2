<?php
// Una vista compartida recibe únicamente los datos de la cuenta autenticada.
$rol = $usuario['rol'];
$etiquetasRol = ['administrador' => 'Administrador', 'usuario' => 'Usuario'];
preg_match('/^./us', trim($usuario['nombre']), $inicial);
?>
<section class="admin-encabezado">
    <div>
        <span class="admin-insignia"><span class="admin-punto" aria-hidden="true"></span>Mi cuenta</span>
        <h1>Mi perfil</h1>
        <p>Tu información personal registrada en el Distrito 2.</p>
    </div>
    <div class="admin-bienvenida-acciones">
        <a class="btn admin-btn-azul" href="index.php?r=transmisiones">Ir a la sala de transmisiones</a>
    </div>
</section>

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column align-items-center text-center gap-2 py-4">
                <span class="admin-perfil-avatar" aria-hidden="true"><?= htmlspecialchars(strtoupper($inicial[0] ?? 'U')) ?></span>
                <h2 class="card-title mt-2 mb-0"><?= htmlspecialchars($usuario['nombre']) ?></h2>
                <span class="admin-rol <?= htmlspecialchars($rol) ?>"><?= htmlspecialchars($etiquetasRol[$rol]) ?></span>
                <?php if (!empty($usuario['iglesia'])): ?>
                    <span class="admin-chip"><?= htmlspecialchars($usuario['iglesia']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header">Datos personales</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item admin-dato"><span>Nombre completo</span><strong><?= htmlspecialchars($usuario['nombre']) ?></strong></li>
                <li class="list-group-item admin-dato"><span>Correo electrónico</span><strong><?= htmlspecialchars($usuario['email']) ?></strong></li>
                <li class="list-group-item admin-dato"><span>Rol</span><strong><?= htmlspecialchars($etiquetasRol[$rol]) ?></strong></li>
                <?php if (!empty($usuario['iglesia'])): ?>
                    <li class="list-group-item admin-dato"><span>Iglesia</span><strong><?= htmlspecialchars($usuario['iglesia']) ?></strong></li>
                <?php endif; ?>
            </ul>
            <div class="card-body"><p class="admin-ayuda mb-0">Para solicitar cambios en tu cuenta, comunícate con el administrador del distrito.</p></div>
        </div>
    </div>
</div>
