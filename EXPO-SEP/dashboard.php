<?php
// ============================================
// dashboard.php — Panel de la escuela (Corregido y Limitado)
// ============================================
require_once './conexion/conexion.php';

if (!isset($_SESSION['escuela_id'])) {
    header('Location: index.php'); exit;
}

$escuela_id = (int)$_SESSION['escuela_id'];

// Mensajes flash
$msg_ok  = '';
$msg_err = '';
if (isset($_SESSION['msg_ok']))  { $msg_ok  = $_SESSION['msg_ok'];  unset($_SESSION['msg_ok']); }
if (isset($_SESSION['msg_err'])) { $msg_err = $_SESSION['msg_err']; unset($_SESSION['msg_err']); }

function ejecutar_o_morir($CONN, $sql) {
    $res = mysqli_query($CONN, $sql);
    if ($res === false) {
        die('Error SQL: ' . mysqli_error($CONN) . ' | Consulta: ' . $sql);
    }
    return $res;
}

// ── CONTROL DE TOPES: Verificar si ya tiene Club o Robot ──
$ya_tiene_robot = mysqli_fetch_assoc(ejecutar_o_morir($CONN,
    "SELECT COUNT(*) AS total FROM e_proyectos WHERE id_escuela = $escuela_id AND tipo_formulario = 'Robótica'"
))['total'] > 0;

$ya_tiene_club = mysqli_fetch_assoc(ejecutar_o_morir($CONN,
    "SELECT COUNT(*) AS total FROM e_proyectos WHERE id_escuela = $escuela_id AND tipo_formulario = 'Club de Ciencias'"
))['total'] > 0;


// ── Estadísticas ──────────────────────────────
$total_proyectos = mysqli_fetch_assoc(ejecutar_o_morir($CONN,
    "SELECT COUNT(*) AS total FROM e_proyectos WHERE id_escuela = $escuela_id"
))['total'];

$total_imagenes = mysqli_fetch_assoc(ejecutar_o_morir($CONN,
    "SELECT COUNT(*) AS total FROM e_imagenes_proyecto ip
     INNER JOIN e_proyectos p ON ip.id_proyecto = p.id
     WHERE p.id_escuela = $escuela_id"
))['total'];

$construcciones = mysqli_fetch_assoc(ejecutar_o_morir($CONN,
    "SELECT COUNT(*) AS total FROM e_proyectos WHERE id_escuela = $escuela_id AND carrera = 'Construcciones'"
))['total'];

$tecnologia = mysqli_fetch_assoc(ejecutar_o_morir($CONN,
    "SELECT COUNT(*) AS total FROM e_proyectos WHERE id_escuela = $escuela_id AND carrera = 'Tecnología'"
))['total'];

// ── Filtro ────────────────────────────────────
$filtro_carrera = mysqli_real_escape_string($CONN, $_GET['carrera'] ?? '');
$busqueda       = mysqli_real_escape_string($CONN, $_GET['q'] ?? '');

$where = "WHERE p.id_escuela = $escuela_id";
if ($filtro_carrera) { $where .= " AND p.carrera = '$filtro_carrera'"; }
if ($busqueda)        { $where .= " AND p.nombre LIKE '%$busqueda%'"; }

$proyectos_result = ejecutar_o_morir($CONN,
    "SELECT p.*,
        (SELECT ruta FROM e_imagenes_proyecto WHERE id_proyecto = p.id LIMIT 1) AS primera_imagen
     FROM e_proyectos p
     $where
     ORDER BY p.created_at DESC"
);
?>
<?php include './public/header.php'; ?>
<title>Mis Proyectos — Portal de Proyectos</title>

<main class="main-content">

    <?php if ($msg_ok):  ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><?= htmlspecialchars($msg_ok) ?></div><?php endif; ?>
    <?php if ($msg_err): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($msg_err) ?></div><?php endif; ?>

    <div class="d-flex align-center gap-2 mb-3" style="flex-wrap:wrap; justify-content:space-between;">
        <div>
            <div class="page-title">Mis Proyectos</div>
            <div class="page-subtitle">
                <?= htmlspecialchars($_SESSION['escuela_nombre'] ?? 'S/D') ?> · <?= htmlspecialchars($_SESSION['escuela_localidad'] ?? 'S/D') ?>
            </div>
        </div>
        
        <div class="d-flex gap-1" style="flex-wrap:wrap; justify-content:flex-end;">
            <a href="cargar_proyecto.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Cargar nuevo proyecto
            </a>
            
            <?php if (!$ya_tiene_robot): ?>
                <a href="cargar_robot.php" class="btn btn-primary">
                    <i class="fas fa-robot"></i> Cargar robot
                </a>
            <?php endif; ?>
            
            <?php if (!$ya_tiene_club): ?>
                <a href="cargar_club.php" class="btn btn-primary">
                    <i class="fas fa-flask"></i> Cargar club de ciencias
                </a>
            <?php endif; ?>

        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue"><i class="fas fa-folder-open"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $total_proyectos ?></div>
                <div class="stat-label">Proyectos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-yellow"><i class="fas fa-hard-hat"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $construcciones ?></div>
                <div class="stat-label">Construcciones</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-green"><i class="fas fa-microchip"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $tecnologia ?></div>
                <div class="stat-label">Tecnología</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-cyan"><i class="fas fa-images"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $total_imagenes ?></div>
                <div class="stat-label">Fotos subidas</div>
            </div>
        </div>
    </div>

    <form method="GET" class="filters-bar" id="filterForm">
        <input type="text"
               name="q"
               class="form-control"
               placeholder="🔍 Buscar por nombre del proyecto..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <select name="carrera" class="form-control" style="max-width:240px;" onchange="document.getElementById('filterForm').submit()">
            <option value="">Todas las carreras</option>
            <option value="Construcciones" <?= ($filtro_carrera === 'Construcciones') ? 'selected' : '' ?>>🏗️ Construcciones</option>
            <option value="Tecnología"     <?= ($filtro_carrera === 'Tecnología')     ? 'selected' : '' ?>>💻 Tecnología</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-search"></i> Buscar
        </button>
        <?php if ($filtro_carrera || !empty($_GET['q'])): ?>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-times"></i> Limpiar
            </a>
        <?php endif; ?>
    </form>

    <?php if (mysqli_num_rows($proyectos_result) === 0): ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>Sin proyectos aún</h3>
            <p>Cargá el primer proyecto de tu escuela usando el botón de arriba.</p>
        </div>
    <?php else: ?>
        <div class="proyectos-grid">
            <?php while ($p = mysqli_fetch_assoc($proyectos_result)): ?>
                <?php
                    $es_construcciones = $p['carrera'] === 'Construcciones';
                    $cat_class = $es_construcciones ? 'cat-arduino' : 'cat-juegos';
                    $cat_icon  = $es_construcciones ? 'fas fa-hard-hat' : 'fas fa-microchip';
                ?>
                <div class="proyecto-card">

                    <?php if ($p['primera_imagen']): ?>
                        <img src="./uploads/<?= htmlspecialchars($p['primera_imagen']) ?>"
                             alt="<?= htmlspecialchars($p['nombre']) ?>"
                             class="proyecto-card-img">
                    <?php else: ?>
                        <div class="proyecto-card-img-placeholder">
                            <i class="<?= $cat_icon ?>"></i>
                        </div>
                    <?php endif; ?>

                    <div class="proyecto-card-body">
                        <span class="proyecto-card-cat <?= $cat_class ?>">
                            <i class="<?= $cat_icon ?>"></i>
                            <?= htmlspecialchars($p['carrera']) ?>
                        </span>

                        <div class="proyecto-card-title"><?= htmlspecialchars($p['nombre']) ?></div>

                        <div class="proyecto-card-meta">
                            <span><i class="fas fa-layer-group"></i> <?= htmlspecialchars($p['sub_categoria']) ?></span>
                            <span><i class="fas fa-laptop-house"></i> <?= htmlspecialchars($p['modalidad_trabajo']) ?></span>
                            <span><i class="fas fa-user-tag"></i> <?= htmlspecialchars($p['tipo_formulario'] ?? 'No especificado') ?></span>
                            <span><i class="fas fa-user-edit"></i> Por: <?= htmlspecialchars($p['cargado_por'] ?? 'Docente') ?></span>
                        </div>

                        <div class="proyecto-card-desc">
                            <?= htmlspecialchars($p['descripcion']) ?>
                        </div>
                    </div>

                    <div class="proyecto-card-footer">
                        <span style="font-size:.75rem; color:var(--text-muted);">
                            <i class="fas fa-calendar-alt"></i>
                            <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                        </span>
                        <div class="d-flex gap-1">
                            <?php if (!empty($p['archivo'])): ?>
                                <a href="./uploads/<?= htmlspecialchars($p['archivo']) ?>" target="_blank" class="btn btn-outline btn-sm">
                                    <i class="fas fa-file-pdf"></i> Informe
                                </a>
                            <?php endif; ?>
                            <a href="ver_proyecto.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                            <a href="eliminar_proyecto.php?id=<?= $p['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Eliminar este proyecto? Esta acción no se puede deshacer.')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>

                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

</main>

<?php include './public/footer.php'; ?>