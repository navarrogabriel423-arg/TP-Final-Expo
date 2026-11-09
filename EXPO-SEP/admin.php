<?php
// ============================================
// admin.php — Panel del administrador (FIXED)
// ============================================
require_once './conexion/conexion.php';

if (!isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header('Location: index.php'); exit;
}

$msg_ok  = '';
$msg_err = '';
if (isset($_SESSION['msg_ok']))  { $msg_ok  = $_SESSION['msg_ok'];  unset($_SESSION['msg_ok']); }
if (isset($_SESSION['msg_err'])) { $msg_err = $_SESSION['msg_err']; unset($_SESSION['msg_err']); }

// ── Estadísticas globales corregidas ──────────
$stats = [
    'proyectos' => mysqli_fetch_assoc(mysqli_query($CONN, "SELECT COUNT(*) AS t FROM e_proyectos"))['t'],
    'escuelas'  => mysqli_fetch_assoc(mysqli_query($CONN, "SELECT COUNT(DISTINCT id_escuela) AS t FROM e_proyectos"))['t'],
    'construcciones' => mysqli_fetch_assoc(mysqli_query($CONN, "SELECT COUNT(*) AS t FROM e_proyectos WHERE carrera = 'Construcciones'"))['t'],
    'tecnologia'     => mysqli_fetch_assoc(mysqli_query($CONN, "SELECT COUNT(*) AS t FROM e_proyectos WHERE carrera = 'Tecnología'"))['t']
];

// ── Filtros del Administrador ──────────────────
$filtro_carrera = mysqli_real_escape_string($CONN, $_GET['escuela'] ?? ''); // Mapeado al select de carreras
$filtro_cat     = mysqli_real_escape_string($CONN, $_GET['cat'] ?? '');
$busqueda       = mysqli_real_escape_string($CONN, $_GET['q'] ?? '');

$where = "WHERE 1=1"; 
if ($filtro_carrera) { $where .= " AND p.carrera = '$filtro_carrera'"; } // <-- CORREGIDO: Filtra por texto de carrera
if ($filtro_cat)     { $where .= " AND p.sub_categoria = '$filtro_cat'"; } 
if ($busqueda)       { $where .= " AND (p.nombre LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%' OR e.nombre LIKE '%$busqueda%')"; } 

// Consulta unificada con datos de escuela e imagen
$proyectos_result = mysqli_query($CONN, 
    "SELECT p.*, e.nombre AS escuela_nombre, e.localidad,
        (SELECT ruta FROM e_imagenes_proyecto WHERE id_proyecto = p.id LIMIT 1) AS primera_imagen,
        (SELECT COUNT(*) FROM e_imagenes_proyecto WHERE id_proyecto = p.id) AS total_fotos
     FROM e_proyectos p
     INNER JOIN e_escuelas e ON p.id_escuela = e.id
     $where
     ORDER BY p.created_at DESC"
);
?>
<?php include './public/header.php'; ?>
<title>Panel Admin — Portal de Proyectos</title>

<main class="main-content">

    <?php if ($msg_ok):  ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg_ok) ?></div><?php endif; ?>
    <?php if ($msg_err): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($msg_err) ?></div><?php endif; ?>

    <div class="page-title">Panel de Administración</div>
    <div class="page-subtitle">Todos los proyectos del portal · <?= ADMIN_NOMBRE ?></div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue"><i class="fas fa-folder-open"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['proyectos'] ?></div>
                <div class="stat-label">Proyectos totales</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-cyan"><i class="fas fa-school"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['escuelas'] ?></div>
                <div class="stat-label">Escuelas con proyectos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-yellow"><i class="fas fa-microchip"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['tecnologia'] ?></div>
                <div class="stat-label">Tecnología</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-green"><i class="fas fa-hard-hat"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $stats['construcciones'] ?></div>
                <div class="stat-label">Construcciones</div>
            </div>
        </div>
    </div>

    <form method="GET" class="filters-bar" id="filterForm">
        <input type="text"
               name="q"
               class="form-control"
               placeholder="🔍 Buscar por título o escuela..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

        <select name="escuela" class="form-control">
            <option value="">Todas las carreras</option>
            <option value="Construcciones" <?= $filtro_carrera == 'Construcciones' ? 'selected' : '' ?>>Construcciones</option>
            <option value="Tecnología" <?= $filtro_carrera == 'Tecnología' ? 'selected' : '' ?>>Tecnología</option>
        </select>

        <select name="cat" class="form-control">
            <option value="">Todas las categorías</option>
            <option value="Medioambiente" <?= $filtro_cat == 'Medioambiente' ? 'selected' : '' ?>>Medioambiente</option>
            <option value="Proyectos de modelos constructivos" <?= $filtro_cat == 'Proyectos de modelos constructivos' ? 'selected' : '' ?>>Proyectos de modelos constructivos</option>
            <option value="Automatización" <?= $filtro_cat == 'Automatización' ? 'selected' : '' ?>>Automatización</option>
            <option value="Programación" <?= $filtro_cat == 'Programación' ? 'selected' : '' ?>>Programación</option>
            <option value="Videojuegos" <?= $filtro_cat == 'Videojuegos' ? 'selected' : '' ?>>Videojuegos</option>
        </select>

        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-search"></i> Filtrar
        </button>

        <?php if ($filtro_cat || $filtro_carrera || !empty($_GET['q'])): ?>
            <a href="admin.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-times"></i> Limpiar
            </a>
        <?php endif; ?>
    </form>

    <?php $total_filtrado = mysqli_num_rows($proyectos_result); ?>
    <p style="font-size:.85rem; color:var(--text-muted); margin-bottom:1rem;">
        <?= $total_filtrado ?> proyecto<?= $total_filtrado !== 1 ? 's' : '' ?> encontrado<?= $total_filtrado !== 1 ? 's' : '' ?>
    </p>

    <?php if ($total_filtrado === 0): ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>Sin resultados</h3>
            <p>Probá con otros filtros o términos de búsqueda.</p>
        </div>
    <?php else: ?>
        <div class="proyectos-grid">
            <?php while ($p = mysqli_fetch_assoc($proyectos_result)): ?>
                <?php
                    $es_juegos = ($p['sub_categoria'] === 'Videojuegos');
                    $cat_class = $es_juegos ? 'cat-juegos' : 'cat-arduino';
                    $cat_icon  = $es_juegos ? 'fas fa-gamepad' : 'fas fa-microchip';
                ?>
                <div class="proyecto-card">

                    <?php if (!empty($p['primera_imagen'])): ?>
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
                                <?= htmlspecialchars($p['carrera'] ?? 'General') ?>
                        </span>

                        <div class="proyecto-card-title"><?= htmlspecialchars($p['nombre'] ?? 'Sin nombre') ?></div>

                        <div class="proyecto-card-alumno">
                            <i class="fas fa-graduation-cap"></i>
                            <?= htmlspecialchars($p['carrera'] ?? 'Técnica') ?>
                        </div>

                        <div class="proyecto-card-meta">
                            <span><i class="fas fa-users"></i> <?= htmlspecialchars($p['modalidad_trabajo'] ?? 'Grupal') ?></span>
                            
                            <?php if (isset($p['total_fotos']) && $p['total_fotos'] > 0): ?>
                                <span><i class="fas fa-images"></i> <?= $p['total_fotos'] ?> foto(s)</span>
                            <?php endif; ?>
                        </div>

                        <div class="proyecto-card-desc">
                            <?= htmlspecialchars($p['descripcion']) ?>
                        </div>
                    </div>

                    <div class="proyecto-card-footer">
                        <div>
                            <div class="proyecto-card-escuela">
                                <i class="fas fa-school"></i> <?= htmlspecialchars($p['escuela_nombre']) ?>
                            </div>
                            <div style="font-size:.72rem; color:var(--text-light); margin-top:.2rem;">
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['localidad'] ?? 'S/D') ?> · <i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="ver_proyecto.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="eliminar_proyecto.php?id=<?= $p['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('¿Eliminar este proyecto?')">
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