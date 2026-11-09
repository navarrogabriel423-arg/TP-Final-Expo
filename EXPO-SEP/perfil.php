<?php
// ============================================
// perfil.php — Perfil de la escuela
// ============================================
require_once './conexion/conexion.php';

if (!isset($_SESSION['escuela_id'])) {
    header('Location: index.php'); exit;
}

$escuela_id = (int)$_SESSION['escuela_id'];

// Datos completos de la escuela
$esc = mysqli_fetch_assoc(mysqli_query($CONN,
    "SELECT * FROM e_escuelas WHERE id = $escuela_id"
));

// Estadísticas
$total_proy = mysqli_fetch_assoc(mysqli_query($CONN,
    "SELECT COUNT(*) AS t FROM e_proyectos WHERE id_escuela = $escuela_id"
))['t'];

$total_imgs = mysqli_fetch_assoc(mysqli_query($CONN,
    "SELECT COUNT(*) AS t FROM e_imagenes_proyecto ip
     INNER JOIN e_proyectos p ON ip.id_proyecto = p.id
     WHERE p.id_escuela = $escuela_id"
))['t'];

$juegos = mysqli_fetch_assoc(mysqli_query($CONN,
    "SELECT COUNT(*) AS t FROM e_proyectos WHERE id_escuela = $escuela_id AND sub_categoria='Videojuegos'"
))['t'];

$arduino = mysqli_fetch_assoc(mysqli_query($CONN,
    "SELECT COUNT(*) AS t FROM e_proyectos WHERE id_escuela = $escuela_id AND sub_categoria='Automatización'"
))['t'];

// Último proyecto
$ultimo_res = mysqli_query($CONN, "SELECT * FROM e_proyectos WHERE id_escuela = $escuela_id ORDER BY id DESC LIMIT 1");
$ultimo = $ultimo_res ? mysqli_fetch_assoc($ultimo_res) : null;

// Mensajes flash
$msg_ok  = '';
$msg_err = '';
if (isset($_SESSION['msg_ok']))  { $msg_ok  = $_SESSION['msg_ok'];  unset($_SESSION['msg_ok']); }
if (isset($_SESSION['msg_err'])) { $msg_err = $_SESSION['msg_err']; unset($_SESSION['msg_err']); }

// Iniciales para avatar
$palabras  = explode(' ', $esc['nombre']);
$iniciales = strtoupper(substr($palabras[0], 0, 1) . (isset($palabras[1]) ? substr($palabras[1], 0, 1) : ''));
?>
<?php include './public/header.php'; ?>
<title>Perfil — Portal de Proyectos</title>

<main class="main-content">

    <?php if ($msg_ok):  ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg_ok) ?></div><?php endif; ?>
    <?php if ($msg_err): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($msg_err) ?></div><?php endif; ?>

    <!-- Header de perfil -->
    <div class="perfil-header">
        <div class="perfil-avatar"><?= $iniciales ?></div>
        <div class="perfil-info">
            <h2><?= htmlspecialchars($esc['nombre']) ?></h2>
            <p>
                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($esc['localidad']) ?>
                &nbsp;·&nbsp;
                <i class="fas fa-envelope"></i> <?= htmlspecialchars($esc['email']) ?>
                <?php if ($esc['telefono']): ?>
                    &nbsp;·&nbsp;
                    <i class="fas fa-phone"></i> <?= htmlspecialchars($esc['telefono']) ?>
                <?php endif; ?>
            </p>
            <p style="margin-top:.4rem; font-size:.82rem; color:rgba(255,255,255,.65);">
                Cuenta creada el <?= date('d/m/Y', strtotime($esc['created_at'])) ?>
            </p>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue"><i class="fas fa-folder-open"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $total_proy ?></div>
                <div class="stat-label">Proyectos publicados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-yellow"><i class="fas fa-hard-hat"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $juegos ?></div>
                <div class="stat-label">Construcciones</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-green"><i class="fas fa-microchip"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $arduino ?></div>
                <div class="stat-label">Tecnologia</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-cyan"><i class="fas fa-images"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $total_imgs ?></div>
                <div class="stat-label">Fotos subidas</div>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; align-items:start;">

        <!-- Datos de la cuenta -->
        <div class="card">
            <div class="card-header"><i class="fas fa-school"></i> Datos de la institución</div>
            <div class="card-body">
                <ul style="list-style:none; display:flex; flex-direction:column; gap:1rem;">
                    <li style="display:flex; gap:.75rem;">
                        <i class="fas fa-school" style="color:var(--primary); width:16px; flex-shrink:0; margin-top:.15rem;"></i>
                        <div>
                            <div style="font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em;">Nombre</div>
                            <div style="font-weight:600;"><?= htmlspecialchars($esc['nombre']) ?></div>
                        </div>
                    </li>
                    <li style="display:flex; gap:.75rem;">
                        <i class="fas fa-map-marker-alt" style="color:var(--primary); width:16px; flex-shrink:0; margin-top:.15rem;"></i>
                        <div>
                            <div style="font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em;">Localidad</div>
                            <div style="font-weight:600;"><?= htmlspecialchars($esc['localidad']) ?></div>
                        </div>
                    </li>
                    <li style="display:flex; gap:.75rem;">
                        <i class="fas fa-envelope" style="color:var(--primary); width:16px; flex-shrink:0; margin-top:.15rem;"></i>
                        <div>
                            <div style="font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em;">Correo</div>
                            <div style="font-weight:600;"><?= htmlspecialchars($esc['email']) ?></div>
                        </div>
                    </li>
                    <?php if ($esc['telefono']): ?>
                    <li style="display:flex; gap:.75rem;">
                        <i class="fas fa-phone" style="color:var(--primary); width:16px; flex-shrink:0; margin-top:.15rem;"></i>
                        <div>
                            <div style="font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em;">Teléfono</div>
                            <div style="font-weight:600;"><?= htmlspecialchars($esc['telefono']) ?></div>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Último proyecto + cambio de contraseña -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">

            <?php if ($ultimo): ?>
            <div class="card">
                <div class="card-header"><i class="fas fa-clock"></i> Último proyecto publicado</div>
                <div class="card-body">
                    <p style="font-weight:600; margin-bottom:.4rem;"><?= htmlspecialchars($ultimo['nombre']) ?></p>
                    <p style="font-size:.85rem; color:var(--text-muted);">
                        <i class="fas fa-calendar-alt"></i>
                        <?= date('d/m/Y \a \l\a\s H:i', strtotime($ultimo['created_at'])) ?>
                    </p>
                </div>
            </div>
<?php endif; ?>

            <!-- Cambiar contraseña -->
            <div class="card">
                <div class="card-header"><i class="fas fa-key"></i> Cambiar contraseña</div>
                <div class="card-body">
                    <form action="cambiar_password.php" method="POST" novalidate id="passForm">
                        <div class="form-group">
                            <label class="form-label" for="pass_actual">Contraseña actual</label>
                            <input type="password" id="pass_actual" name="pass_actual" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="pass_nueva">Nueva contraseña</label>
                            <input type="password" id="pass_nueva" name="pass_nueva" class="form-control" required minlength="8">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="pass_confirmar">Confirmar nueva contraseña</label>
                            <input type="password" id="pass_confirmar" name="pass_confirmar" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Guardar contraseña
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</main>

<style>
@media (max-width:768px) {
    main.main-content > div:last-of-type { grid-template-columns:1fr !important; }
}
</style>

<script>
document.getElementById('passForm').addEventListener('submit', function(e) {
    const nueva    = document.getElementById('pass_nueva').value;
    const confirmar= document.getElementById('pass_confirmar').value;
    if (nueva.length < 8) {
        e.preventDefault();
        alert('La nueva contraseña debe tener al menos 8 caracteres.'); return;
    }
    if (nueva !== confirmar) {
        e.preventDefault();
        alert('Las contraseñas no coinciden.'); return;
    }
});
</script>

<?php include './public/footer.php'; ?>
