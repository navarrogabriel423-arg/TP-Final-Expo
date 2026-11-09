<?php
// ============================================
// ver_proyecto.php — Detalle de un proyecto completo (CORREGIDO)
// ============================================
require_once './conexion/conexion.php';

if (!isset($_SESSION['escuela_id']) && !(isset($_SESSION['admin']) && $_SESSION['admin'])) {
    header('Location: index.php'); exit;
}

$es_admin   = isset($_SESSION['admin']) && $_SESSION['admin'];
$escuela_id = $es_admin ? null : (int)$_SESSION['escuela_id'];
$proyecto_id = (int)($_GET['id'] ?? 0);

if (!$proyecto_id) {
    header('Location: ' . ($es_admin ? 'admin.php' : 'dashboard.php')); exit;
}

// 1. Obtener datos principales del proyecto y de la escuela
$q = "SELECT p.*, e.nombre AS escuela_nombre, e.localidad
      FROM e_proyectos p
      INNER JOIN e_escuelas e ON p.id_escuela = e.id
      WHERE p.id = $proyecto_id";

if (!$es_admin) {
    $q .= " AND p.id_escuela = $escuela_id";
}
$result = mysqli_query($CONN, $q);

if (!$result || mysqli_num_rows($result) === 0) {
    $_SESSION['msg_err'] = 'Proyecto no encontrado.';
    header('Location: ' . ($es_admin ? 'admin.php' : 'dashboard.php')); exit;
}

$p = mysqli_fetch_assoc($result);

// 2. Obtener imágenes del proyecto (proyectos normales)
$imgs = [];
$r_imgs = mysqli_query($CONN, "SELECT ruta FROM e_imagenes_proyecto WHERE id_proyecto = $proyecto_id ORDER BY id");
while ($row = mysqli_fetch_assoc($r_imgs)) {
    $imgs[] = $row['ruta'];
}

// 3. Obtener Alumnos asociados
$alumnos = [];
$r_alumnos = mysqli_query($CONN, "SELECT * FROM e_alumnos WHERE id_proyecto = $proyecto_id ORDER BY apellido, nombre");
while ($row = mysqli_fetch_assoc($r_alumnos)) {
    $alumnos[] = $row;
}

// 4. Obtener Docentes asociados
$docentes = [];
$r_docentes = mysqli_query($CONN, "SELECT * FROM e_docentes WHERE id_proyecto = $proyecto_id ORDER BY apellido, nombre");
while ($row = mysqli_fetch_assoc($r_docentes)) {
    $docentes[] = $row;
}

// 5. Obtener datos de Club de Ciencias (si existen)
$club = mysqli_fetch_assoc(mysqli_query($CONN, "SELECT * FROM e_club_ciencias WHERE id_proyecto = $proyecto_id"));

// 6. Obtener datos de Robótica (CORREGIDO: sin fetch solitario que pierde el primer robot)
$robotica = mysqli_fetch_assoc(mysqli_query($CONN, "SELECT * FROM e_robotica WHERE id_proyecto = $proyecto_id"));
$robots = [];
$r_robots = mysqli_query($CONN, "SELECT * FROM e_robots WHERE id_proyecto = $proyecto_id ORDER BY id");
while ($row = mysqli_fetch_assoc($r_robots)) {
    $robots[] = $row;
}

$es_construcciones = $p['carrera'] === 'Construcciones';
$cat_class = $es_construcciones ? 'cat-arduino' : 'cat-juegos';
$cat_icon  = $es_construcciones ? 'fas fa-hard-hat' : 'fas fa-microchip';
$back      = $es_admin ? 'admin.php' : 'dashboard.php';
?>
<?php include './public/header.php'; ?>
<title><?= htmlspecialchars($p['nombre']) ?> — Portal de Proyectos</title>

<main class="main-content">

    <div style="margin-bottom:1.5rem;">
        <a href="<?= $back ?>" style="color:var(--primary); font-size:.9rem;">
            <i class="fas fa-arrow-left"></i>
            <?= $es_admin ? 'Todos los proyectos' : 'Mis proyectos' ?>
        </a>
    </div>

    <div style="display:grid; grid-template-columns:1fr 340px; gap:2rem; align-items:start;">

        <div>

            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-body">
                    <span class="proyecto-card-cat <?= $cat_class ?>" style="margin-bottom:1rem; display:inline-flex;">
                        <i class="<?= $cat_icon ?>"></i>
                        <?= htmlspecialchars($p['carrera']) ?>
                    </span>
                    <h1 style="font-size:1.75rem; font-weight:800; color:var(--text); margin-bottom:.75rem;">
                        <?= htmlspecialchars($p['nombre']) ?>
                    </h1>

                    <div style="display:flex; gap:1.5rem; flex-wrap:wrap; margin-bottom:1.25rem; font-size:.88rem; color:var(--text-muted);">
                        <span><i class="fas fa-school" style="color:var(--primary)"></i> <?= htmlspecialchars($p['escuela_nombre']) ?></span>
                        <span><i class="fas fa-map-marker-alt" style="color:var(--primary)"></i> <?= htmlspecialchars($p['localidad'] ?? 'S/D') ?></span>
                        <span><i class="fas fa-layer-group" style="color:var(--primary)"></i> <?= htmlspecialchars($p['sub_categoria']) ?></span>
                        <span><i class="fas fa-laptop-house" style="color:var(--primary)"></i> <?= htmlspecialchars($p['modalidad_trabajo']) ?></span>
                        <span><i class="fas fa-user" style="color:var(--primary)"></i> <?= !empty($p['cargado_por']) ? htmlspecialchars($p['cargado_por']) : '-' ?></span>
                        <span><i class="fas fa-calendar-alt" style="color:var(--primary)"></i> <?= date('d/m/Y', strtotime($p['created_at'])) ?></span>
                    </div>

                    <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:.5rem; color:var(--text);">Descripción</h3>
                    <p style="color:var(--text); line-height:1.75; font-size:.95rem; white-space:pre-wrap;">
                        <?= htmlspecialchars($p['descripcion']) ?>
                    </p>
                </div>
            </div>

            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header">
                    <i class="fas fa-users"></i> Participantes del Proyecto
                </div>
                <div class="card-body">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                        
                        <div>
                            <h4 style="font-size:1rem; font-weight:700; margin-bottom:.75rem; color:var(--primary);">
                                <i class="fas fa-user-graduate"></i> Alumnos (<?= count($alumnos) ?>)
                            </h4>
                            <?php if (count($alumnos) > 0): ?>
                                <ul style="list-style:none; display:flex; flex-direction:column; gap:.5rem;">
                                    <?php for ($i = 0; $i < count($alumnos); $i++): ?>
                                        <li style="padding:.5rem; background:var(--bg-subtle); border-radius:6px; font-size:.9rem;">
                                            <strong><?= htmlspecialchars($alumnos[$i]['apellido'] . ', ' . $alumnos[$i]['nombre']) ?></strong>
                                            <div style="font-size:.78rem; color:var(--text-muted); margin-top:.15rem;">
                                                DNI: <?= htmlspecialchars($alumnos[$i]['dni']) ?>
                                                <?php if (!empty($alumnos[$i]['dieta_especial'])): ?>
                                                    | <span style="color:var(--warning);"><i class="fas fa-apple-alt"></i> <?= htmlspecialchars($alumnos[$i]['dieta_especial']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            <?php else: ?>
                                <p style="font-size:.88rem; color:var(--text-light); italic">No hay alumnos registrados.</p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <h4 style="font-size:1rem; font-weight:700; margin-bottom:.75rem; color:var(--success);">
                                <i class="fas fa-chalkboard-teacher"></i> Docentes / Asesores (<?= count($docentes) ?>)
                            </h4>
                            <?php if (count($docentes) > 0): ?>
                                <ul style="list-style:none; display:flex; flex-direction:column; gap:.5rem;">
                                    <?php for ($i = 0; $i < count($docentes); $i++): ?>
                                        <li style="padding:.5rem; background:var(--bg-subtle); border-radius:6px; font-size:.9rem;">
                                            <strong><?= htmlspecialchars($docentes[$i]['apellido'] . ', ' . $docentes[$i]['nombre']) ?></strong>
                                            <div style="font-size:.78rem; color:var(--text-muted); margin-top:.15rem;">
                                                DNI: <?= htmlspecialchars($docentes[$i]['dni']) ?> | Rol: <?= htmlspecialchars($docentes[$i]['rol'] ?? 'Asesor') ?>
                                            </div>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            <?php else: ?>
                                <p style="font-size:.88rem; color:var(--text-light); italic">No hay docentes registrados.</p>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>

            <?php
                $modalidad_normalizada = mb_strtolower(str_replace(['í','Í'], ['i','i'], $p['modalidad_trabajo'] ?? ''), 'UTF-8');
            ?>
            <?php if (!empty($p['plataforma_virtual']) && in_array($modalidad_normalizada, ['virtual','hibrido'], true)): ?>
                <div class="card" style="margin-bottom:1.5rem;">
                    <div class="card-header">
                        <i class="fas fa-video"></i> Plataforma Virtual
                    </div>
                    <div class="card-body">
                        <div style="font-size:.95rem; display:flex; gap:1rem; align-items:center;">
                            <span style="color:var(--text-muted);">Plataforma utilizada:</span>
                            <strong style="font-size:1rem; color:var(--primary);"><?= htmlspecialchars($p['plataforma_virtual']) ?></strong>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($club): ?>
                <div class="card" style="margin-bottom:1.5rem;">
                    <div class="card-header">
                        <i class="fas fa-flask"></i> Detalles del Club de Ciencias
                    </div>
                    <div class="card-body">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; font-size:.9rem;">
                            <div><span style="color:var(--text-muted);">Nombre del Club:</span> <strong><?= htmlspecialchars($club['nombre_club'] ?? 'S/D') ?></strong></div>
                            <div><span style="color:var(--text-muted);">Categoría de Trabajo:</span> <strong><?= htmlspecialchars($p['modalidad_trabajo'] ?? 'S/D') ?></strong></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($robotica || count($robots) > 0): ?>
                <div class="card" style="margin-bottom:1.5rem;">
                    <div class="card-header">
                        <i class="fas fa-robot"></i> Información de Robótica
                    </div>
                    <div class="card-body">
                        <?php if ($robotica): ?>
                            <div style="margin-bottom:1rem; font-size:.9rem;">
                                <span style="color:var(--text-muted);">Tipo de Desafío / Competencia:</span> <strong><?= htmlspecialchars($robots[0]['modalidad_robot'] ?? 'S/D') ?></strong>
                            </div>
                        <?php endif; ?>

                        <?php if (count($robots) > 0): ?>
                            <h4 style="font-size:.95rem; font-weight:700; margin-bottom:.5rem; color:var(--text);">Robots Competidores:</h4>
                            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:1rem;">
                                <?php for ($i = 0; $i < count($robots); $i++): ?>
                                    <div style="padding:.75rem; border:1px solid var(--border); border-radius:8px; background:var(--bg-subtle);">
                                        <div style="font-weight:700; color:var(--primary); font-size:.9rem;"><i class="fas fa-microchip"></i> <?= htmlspecialchars($robots[$i]['nombre_robot']) ?></div>
                                        <div style="font-size:.8rem; color:var(--text-muted); margin-top:.25rem;">Categoría: <?= htmlspecialchars($robots[$i]['modalidad_robot']) ?></div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($imgs)): ?>
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header">
                    <i class="fas fa-images"></i> Imágenes del Proyecto (<?= count($imgs) ?>)
                </div>
                <div class="card-body">
                    <div class="galeria-grid">
                        <?php for ($i = 0; $i < count($imgs); $i++): ?>
                            <div class="galeria-item" onclick="openLightbox('./uploads/<?= htmlspecialchars($imgs[$i]) ?>')">
                                <img src="./uploads/<?= htmlspecialchars($imgs[$i]) ?>" alt="Imagen del proyecto">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($p['video_url'])): 
                $url = $p['video_url'];
                parse_str(parse_url($url, PHP_URL_QUERY), $my_array_of_vars);
                $video_id = $my_array_of_vars['v'] ?? basename(parse_url($url, PHP_URL_PATH));
            ?>
                <?php if ($video_id): ?>
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <div class="card-header">
                            <i class="fab fa-youtube"></i> Video del Proyecto
                        </div>
                        <div class="card-body">
                            <div class="video-container" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
                                <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
                                        src="https://www.youtube.com/embed/<?= htmlspecialchars($video_id) ?>" 
                                        frameborder="0" allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($p['archivo'])): ?>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-pdf"></i> Informe del proyecto
                </div>
                <div class="card-body">
                    <a href="./uploads/<?= htmlspecialchars($p['archivo']) ?>" target="_blank" rel="noopener" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> Ver / Descargar informe (PDF)
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($p['propuesta'])): ?>
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header">
                    <i class="fas fa-lightbulb"></i> Propuesta de actividades
                </div>
                <div class="card-body">
                    <p style="color:var(--text); line-height:1.75; font-size:.95rem; white-space:pre-wrap;">
                        <?= htmlspecialchars($p['propuesta']) ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- ═══════════════════════════════════════════════════════ -->
            <!-- NUEVO: IMÁGENES ESPECÍFICAS DE ROBÓTICA Y CLUB        -->
            <!-- ═══════════════════════════════════════════════════════ -->

            <!-- A) FOTO GRUPAL DEL EQUIPO DE ROBÓTICA -->
            <?php if ($robotica && !empty($robotica['ruta_foto_grupal'])): ?>
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header">
                    <i class="fas fa-users"></i> Foto Grupal del Equipo de Robótica
                </div>
                <div class="card-body">
                    <!-- Ruta estándar: ./uploads/ -->
                    <div class="galeria-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                        <div class="galeria-item" onclick="openLightbox('./uploads/<?= htmlspecialchars($robotica['ruta_foto_grupal']) ?>')">
                            <img src="./uploads/<?= htmlspecialchars($robotica['ruta_foto_grupal']) ?>" alt="Foto grupal del equipo de robótica">
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- B) FOTOS INDIVIDUALES DE CADA ROBOT -->
            <?php if (count($robots) > 0): ?>
                <?php 
                    // Filtrar solo robots que tengan foto
                    $robots_con_foto = array_filter($robots, fn($r) => !empty($r['ruta_foto_robot']));
                ?>
                <?php if (count($robots_con_foto) > 0): ?>
                <div class="card" style="margin-bottom:1.5rem;">
                    <div class="card-header">
                        <i class="fas fa-robot"></i> Fotos de los Robots
                    </div>
                    <div class="card-body">
                        <div class="galeria-grid">
                            <?php foreach ($robots_con_foto as $robot): ?>
                                <div class="galeria-item" onclick="openLightbox('./uploads/<?= htmlspecialchars($robot['ruta_foto_robot']) ?>')">
                                    <img src="./uploads/<?= htmlspecialchars($robot['ruta_foto_robot']) ?>" alt="<?= htmlspecialchars($robot['nombre_robot']) ?>">
                                    <div style="padding:.35rem .5rem; font-size:.75rem; text-align:center; color:var(--text-muted); background:var(--bg-subtle); border-top:1px solid var(--border);">
                                        <?= htmlspecialchars($robot['nombre_robot']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- C) LOGO DEL CLUB DE CIENCIAS -->
            <?php if ($club && !empty($club['ruta_logo'])): ?>
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header">
                    <i class="fas fa-image"></i> Logo del Club de Ciencias
                </div>
                <div class="card-body" style="text-align:center;">
                    <!-- Ruta estándar: ./uploads/ -->
                    <div class="galeria-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); max-width:400px; margin:0 auto;">
                        <div class="galeria-item" onclick="openLightbox('./uploads/<?= htmlspecialchars($club['ruta_logo']) ?>')">
                            <img src="./uploads/<?= htmlspecialchars($club['ruta_logo']) ?>" alt="Logo del Club de Ciencias">
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <div>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Resumen
                </div>
                <div class="card-body" style="padding:1.25rem;">
                    <ul style="list-style:none; display:flex; flex-direction:column; gap:.875rem;">
                        <li style="display:flex; gap:.75rem; align-items:flex-start;">
                            <i class="fas fa-school" style="color:var(--primary); margin-top:.1rem; flex-shrink:0;"></i>
                            <div>
                                <div style="font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em;">Escuela</div>
                                <div style="font-weight:600; font-size:.9rem;"><?= htmlspecialchars($p['escuela_nombre']) ?></div>
                            </div>
                        </li>
                        <li style="display:flex; gap:.75rem; align-items:flex-start;">
                            <i class="fas fa-graduation-cap" style="color:var(--primary); margin-top:.1rem; flex-shrink:0;"></i>
                            <div>
                                <div style="font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em;">Carrera</div>
                                <div style="font-weight:600; font-size:.9rem;"><?= htmlspecialchars($p['carrera']) ?></div>
                            </div>
                        </li>
                        <li style="display:flex; gap:.75rem; align-items:flex-start;">
                            <i class="fas fa-layer-group" style="color:var(--primary); margin-top:.1rem; flex-shrink:0;"></i>
                            <div>
                                <div style="font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em;">Subcategoría</div>
                                <div style="font-weight:600; font-size:.9rem;"><?= htmlspecialchars($p['sub_categoria']) ?></div>
                            </div>
                        </li>
                        <li style="display:flex; gap:.75rem; align-items:flex-start;">
                            <i class="fas fa-laptop-house" style="color:var(--primary); margin-top:.1rem; flex-shrink:0;"></i>
                            <div>
                                <div style="font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em;">Modalidad</div>
                                <div style="font-weight:600; font-size:.9rem;"><?= htmlspecialchars($p['modalidad_trabajo']) ?></div>
                            </div>
                        </li>
                        <li style="display:flex; gap:.75rem; align-items:flex-start;">
                            <i class="fas fa-user-tag" style="color:var(--primary); margin-top:.1rem; flex-shrink:0;"></i>
                            <div>
                                <div style="font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em;">Cargado por</div>
                                <div style="font-weight:600; font-size:.9rem;">
                                    <?= !empty($p['cargado_por']) ? htmlspecialchars($p['cargado_por']) : '-' ?>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <?php if (!$es_admin): ?>
                        <hr style="border-color:var(--border); margin:1.25rem 0;">
                        <a href="eliminar_proyecto.php?id=<?= $p['id'] ?>"
                           class="btn btn-danger btn-block"
                           onclick="return confirm('¿Eliminar este proyecto?')">
                            <i class="fas fa-trash"></i> Eliminar proyecto
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</main>

<div class="modal-overlay" id="lightbox" onclick="closeLightbox(event)">
    <div style="max-width:90vw; max-height:90vh; position:relative;">
        <img id="lightboxImg" src="" alt=""
             style="max-width:90vw; max-height:90vh; border-radius:var(--radius); object-fit:contain;">
        <button onclick="closeLightbox()" style="position:absolute;top:-12px;right:-12px;background:white;border:none;width:32px;height:32px;border-radius:50%;font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow);">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<style>
@media (max-width: 768px) {
    main.main-content > div:first-of-type {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function openLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightbox').classList.add('open');
}
function closeLightbox(e) {
    if (!e || e.target === document.getElementById('lightbox') || e.target.tagName === 'BUTTON' || e.target.tagName === 'I') {
        document.getElementById('lightbox').classList.remove('open');
    }
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>

<?php include './public/footer.php'; ?>