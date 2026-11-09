<?php
// ============================================
// procesar_proyecto.php — Guarda el proyecto
// ============================================
require_once './conexion/conexion.php';

// 1. DEFINÍ LA FUNCIÓN PRIMERO (para evitar el Fatal Error)
$esc = function($s) use ($CONN) { return mysqli_real_escape_string($CONN, $s); };

if (!isset($_SESSION['escuela_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

// Límite de tamaño del PDF
if (!defined('MAX_SIZE_PDF')) {
    define('MAX_SIZE_PDF', 5 * 1024 * 1024);
}

$escuela_id = (int)$_SESSION['escuela_id'];

// ── Campos de texto ───────────────────────────
$nombre            = trim($_POST['nombre']            ?? '');
$descripcion       = trim($_POST['descripcion']       ?? '');
$carrera           = trim($_POST['carrera']            ?? '');
$sub_categoria     = trim($_POST['sub_categoria']      ?? '');
$modalidad_trabajo = trim($_POST['modalidad_trabajo']  ?? '');
$tipo_formulario   = "normal";
$cargado_por       = trim($_POST['cargado_por']        ?? '');
$plataforma_virtual = trim($_POST['plataforma_virtual'] ?? '');
$propuesta         = trim($_POST['propuesta']          ?? '');
$video_url         = trim($_POST['video_url']          ?? '');

// Guardar para repoblar en caso de error
$_SESSION['old_proyecto'] = compact('nombre','descripcion','carrera','sub_categoria','modalidad_trabajo','tipo_formulario','cargado_por','plataforma_virtual','propuesta');

// ── Validaciones de catálogos ─────────────────
$carreras_ok = ['Construcciones', 'Tecnología'];
$subcategorias_ok = [
    'Construcciones' => ['Medioambiente', 'Proyectos de modelos constructivos'],
    'Tecnología'      => ['Medioambiente', 'Videojuegos', 'Programación', 'Automatización'],
];
$modalidades_ok = ['Presencial', 'Virtual', 'Híbrido'];
$tipos_form_ok  = ['Club de Ciencias', 'Robotica', 'normal'];
$cargado_por_ok = ['Docente', 'Alumno'];
$plataformas_ok = ['Webex'];

if (!$nombre || !$descripcion || !$carrera || !$sub_categoria || !$modalidad_trabajo || !$tipo_formulario || !$cargado_por) {
    $_SESSION['error_proyecto'] = 'Completá todos los campos obligatorios.';
    header('Location: cargar_proyecto.php'); exit;
}

// Si la modalidad requiere plataforma (Virtual o Híbrido), validar presencia y valor
if (($modalidad_trabajo === 'Virtual' || $modalidad_trabajo === 'Híbrido')) {
    if (!$plataforma_virtual) {
        $_SESSION['error_proyecto'] = 'Seleccioná la plataforma virtual.';
        header('Location: cargar_proyecto.php'); exit;
    }
    if (!in_array($plataforma_virtual, $plataformas_ok, true)) {
        $_SESSION['error_proyecto'] = 'Plataforma virtual no válida.';
        header('Location: cargar_proyecto.php'); exit;
    }
}

if (!in_array($carrera, $carreras_ok, true)) {
    $_SESSION['error_proyecto'] = 'Carrera no válida.';
    header('Location: cargar_proyecto.php'); exit;
}

if (!in_array($sub_categoria, $subcategorias_ok[$carrera] ?? [], true)) {
    $_SESSION['error_proyecto'] = 'Subcategoría no válida para la carrera seleccionada.';
    header('Location: cargar_proyecto.php'); exit;
}

if (!in_array($modalidad_trabajo, $modalidades_ok, true)) {
    $_SESSION['error_proyecto'] = 'Modalidad de trabajo no válida.';
    header('Location: cargar_proyecto.php'); exit;
}

if (!in_array($tipo_formulario, $tipos_form_ok, true)) {
    $_SESSION['error_proyecto'] = 'Tipo de formulario no válido.';
    header('Location: cargar_proyecto.php'); exit;
}

if (!in_array($cargado_por, $cargado_por_ok, true)) {
    $_SESSION['error_proyecto'] = 'Cargado por no válido.';
    header('Location: cargar_proyecto.php'); exit;
}

// ── Validar y subir el PDF (obligatorio) ──────
if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error_proyecto'] = 'Debés subir el informe en PDF.';
    header('Location: cargar_proyecto.php'); exit;
}

$pdf = $_FILES['archivo'];

if ($pdf['size'] > MAX_SIZE_PDF) {
    $_SESSION['error_proyecto'] = 'El PDF supera el límite de ' . round(MAX_SIZE_PDF / 1024 / 1024) . ' MB.';
    header('Location: cargar_proyecto.php'); exit;
}

$pdf_ext  = strtolower(pathinfo($pdf['name'], PATHINFO_EXTENSION));
$pdf_mime = mime_content_type($pdf['tmp_name']);

if ($pdf_ext !== 'pdf' || $pdf_mime !== 'application/pdf') {
    $_SESSION['error_proyecto'] = 'El informe debe ser un archivo PDF válido.';
    header('Location: cargar_proyecto.php'); exit;
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$archivo_nombre = uniqid('informe_', true) . '.pdf';

if (!move_uploaded_file($pdf['tmp_name'], UPLOAD_DIR . $archivo_nombre)) {
    $_SESSION['error_proyecto'] = 'Error al subir el informe. Intentá nuevamente.';
    header('Location: cargar_proyecto.php'); exit;
}

// ── Imágenes (opcionales) ─────────────────────
$imagenes_guardadas = [];

if (!empty($_FILES['imagenes']['name'][0])) {

    $files = $_FILES['imagenes'];
    $count = count($files['name']);

    if ($count > MAX_IMAGENES) {
        @unlink(UPLOAD_DIR . $archivo_nombre);
        $_SESSION['error_proyecto'] = 'Podés subir hasta ' . MAX_IMAGENES . ' imágenes.';
        header('Location: cargar_proyecto.php'); exit;
    }

    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

        if ($files['size'][$i] > MAX_SIZE_IMG) {
            foreach ($imagenes_guardadas as $f) { @unlink(UPLOAD_DIR . $f); }
            @unlink(UPLOAD_DIR . $archivo_nombre);
            $_SESSION['error_proyecto'] = "La imagen '{$files['name'][$i]}' supera los 3 MB.";
            header('Location: cargar_proyecto.php'); exit;
        }

        $ext  = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        $mime = mime_content_type($files['tmp_name'][$i]);
        $mimes_ok = ['image/jpeg','image/png','image/gif','image/webp'];

        if (!in_array($ext, IMG_EXTENSIONES) || !in_array($mime, $mimes_ok)) {
            foreach ($imagenes_guardadas as $f) { @unlink(UPLOAD_DIR . $f); }
            @unlink(UPLOAD_DIR . $archivo_nombre);
            $_SESSION['error_proyecto'] = "El archivo '{$files['name'][$i]}' no es una imagen válida.";
            header('Location: cargar_proyecto.php'); exit;
        }

        $nuevo_nombre = uniqid('proj_', true) . '.' . $ext;

        if (move_uploaded_file($files['tmp_name'][$i], UPLOAD_DIR . $nuevo_nombre)) {
            $imagenes_guardadas[] = $nuevo_nombre;
        }
    }
}
// ── Insertar proyecto y metadatos (con transacción) ─────────────────────────
mysqli_begin_transaction($CONN);
try {
    $propuesta_sql = $propuesta !== '' ? "'{$esc($propuesta)}'" : 'NULL';

    $sql = "INSERT INTO e_proyectos (id_escuela, nombre, descripcion, archivo, modalidad_trabajo, plataforma_virtual, propuesta, carrera, sub_categoria, tipo_formulario, cargado_por, video_url)
            VALUES ($escuela_id, '{$esc($nombre)}', '{$esc($descripcion)}', '{$esc($archivo_nombre)}',
                    '{$esc($modalidad_trabajo)}', '{$esc($plataforma_virtual)}', $propuesta_sql, '{$esc($carrera)}', '{$esc($sub_categoria)}', '{$esc($tipo_formulario)}', '{$esc($cargado_por)}', '$video_url')";

    if (!mysqli_query($CONN, $sql)) {
        throw new Exception('Error al guardar el proyecto.');
    }

    $proyecto_id = mysqli_insert_id($CONN);

    // Insertar imágenes
    foreach ($imagenes_guardadas as $ruta) {
        $ruta_safe = mysqli_real_escape_string($CONN, $ruta);
        if (!mysqli_query($CONN, "INSERT INTO e_imagenes_proyecto (id_proyecto, ruta) VALUES ($proyecto_id, '$ruta_safe')")) {
            throw new Exception('Error al guardar una imagen.');
        }
    }

    // Insertar docente (si se envió)
    if (!empty($_POST['docente_dni'] ?? '') || !empty($_POST['docente_nombre'] ?? '')) {
        // Validaciones básicas servidor
        $required_doc = ['docente_dni','docente_nombre','docente_apellido','docente_nacimiento','docente_celular','docente_mail','docente_curso'];
        foreach ($required_doc as $rf) {
            if (empty(trim($_POST[$rf] ?? ''))) {
                throw new Exception('Faltan datos obligatorios del docente.');
            }
        }

        $d_dni    = $esc($_POST['docente_dni']);
        $d_nombre = $esc($_POST['docente_nombre']);
        $d_apelli = $esc($_POST['docente_apellido']);
        $d_dieta  = $esc($_POST['docente_dieta'] ?? '');
        $d_nacim  = $esc($_POST['docente_nacimiento']);
        $d_celu   = $esc($_POST['docente_celular']);
        $d_mail   = $esc($_POST['docente_mail']);
        $d_curso  = $esc($_POST['docente_curso']);

        $sql_doc = "INSERT INTO e_docentes (id_proyecto, dni, nombre, apellido, dieta_especial, fecha_nacimiento, celular, mail, curso)
                    VALUES ($proyecto_id, '$d_dni', '$d_nombre', '$d_apelli', '$d_dieta', '$d_nacim', '$d_celu', '$d_mail', '$d_curso')";
        if (!mysqli_query($CONN, $sql_doc)) {
            throw new Exception('Error al guardar los datos del docente.');
        }
    }

    // Insertar alumnos (si se enviaron)
    if (isset($_POST['alumnos']) && is_array($_POST['alumnos'])) {
        $alumnos = $_POST['alumnos'];
        if (count($alumnos) > 4) throw new Exception('Solo se permiten hasta 4 alumnos por proyecto.');
        foreach ($alumnos as $alumno) {
            $a_dni    = $esc($alumno['dni'] ?? '');
            $a_nombre = $esc($alumno['nombre'] ?? '');
            $a_apelli = $esc($alumno['apellido'] ?? '');
            $a_dieta  = $esc($alumno['dieta'] ?? '');
            $a_nacim  = $esc($alumno['nacimiento'] ?? '');
            $a_celu   = $esc($alumno['celular'] ?? '');
            $a_mail   = $esc($alumno['mail'] ?? '');
            $a_curso  = $esc($alumno['curso'] ?? '');

            if (!$a_dni || !$a_nombre || !$a_apelli || !$a_nacim || !$a_curso) {
                throw new Exception('Faltan datos obligatorios de un alumno.');
            }

            $sql_al = "INSERT INTO e_alumnos (id_proyecto, dni, nombre, apellido, dieta_especial, fecha_nacimiento, celular, mail, curso)
                       VALUES ($proyecto_id, '$a_dni', '$a_nombre', '$a_apelli', '$a_dieta', '$a_nacim', '$a_celu', '$a_mail', '$a_curso')";
            if (!mysqli_query($CONN, $sql_al)) {
                throw new Exception('Error al guardar los datos de un alumno.');
            }
        }
    }

    // Si todo salió bien
    mysqli_commit($CONN);

    unset($_SESSION['old_proyecto']);
    $_SESSION['msg_ok'] = '¡Proyecto publicado exitosamente!';
    header('Location: dashboard.php');
    exit;

} catch (Exception $e) {
    // Limpiar archivos subidos
    foreach ($imagenes_guardadas as $f) { @unlink(UPLOAD_DIR . $f); }
    @unlink(UPLOAD_DIR . $archivo_nombre);
    mysqli_rollback($CONN);
    $_SESSION['error_proyecto'] = $e->getMessage();
    header('Location: cargar_proyecto.php'); exit;
}