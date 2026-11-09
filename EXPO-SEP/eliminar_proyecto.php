<?php
// ============================================
// eliminar_proyecto.php — Elimina un proyecto
// ============================================
require_once './conexion/conexion.php';

$es_admin   = isset($_SESSION['admin']) && $_SESSION['admin'];
$escuela_id = isset($_SESSION['escuela_id']) ? (int)$_SESSION['escuela_id'] : null;

if (!$es_admin && !$escuela_id) {
    header('Location: index.php'); exit;
}

$proyecto_id = (int)($_GET['id'] ?? 0);
if (!$proyecto_id) {
    header('Location: ' . ($es_admin ? 'admin.php' : 'dashboard.php')); exit;
}

// Verificar que el proyecto le pertenece (o es admin)
$where = $es_admin ? "" : " AND id_escuela = $escuela_id";
$result = mysqli_query($CONN, "SELECT id FROM e_proyectos WHERE id = $proyecto_id $where");

if (!$result || mysqli_num_rows($result) === 0) {
    $_SESSION['msg_err'] = 'Proyecto no encontrado o sin permiso para eliminarlo.';
    header('Location: ' . ($es_admin ? 'admin.php' : 'dashboard.php')); exit;
}

// Obtener imágenes para borrar del disco
$imgs = mysqli_query($CONN, "SELECT ruta FROM e_imagenes_proyecto WHERE id_proyecto = $proyecto_id");
while ($img = mysqli_fetch_assoc($imgs)) {
    $ruta = UPLOAD_DIR . $img['ruta'];
    if (file_exists($ruta)) @unlink($ruta);
}

// Borrar de la base de datos (cascade borra imagenes_proyecto)
mysqli_query($CONN, "DELETE FROM e_proyectos WHERE id = $proyecto_id");

$_SESSION['msg_ok'] = 'Proyecto eliminado correctamente.';
header('Location: ' . ($es_admin ? 'admin.php' : 'dashboard.php'));
exit;
