<?php
// ============================================
// procesar_club.php — Procesamiento del Formulario de Club de Ciencias (Corregido)
// ============================================
require_once './conexion/conexion.php';

session_start();

if (!isset($_SESSION['escuela_id'])) {
    header('Location: index.php'); exit;
}

$escuela_id = (int)$_SESSION['escuela_id'];

// BLINDAJE: Evitar entrada por URL directa si ya existe un Club
$check_club = mysqli_query($CONN, "SELECT id FROM e_proyectos WHERE id_escuela = $escuela_id AND tipo_formulario = 'Club de Ciencias'");
if (mysqli_num_rows($check_club) > 0) {
    $_SESSION['msg_err'] = "Tu escuela ya tiene un Club de Ciencias registrado.";
    header('Location: dashboard.php');
    exit;
}
// Función para manejar errores, deshacer cambios y redirigir
function fallar($mensaje) {
    global $CONN;
    mysqli_rollback($CONN); // Deshacer cualquier guardado a medias
    $_SESSION['error_club'] = $mensaje;
    // Guardar los datos viejos para no hacerle tipear todo de nuevo
    $_SESSION['old_club'] = $_POST; 
    header('Location: cargar_club.php');
    exit;
}

// ── Función para procesar y validar imágenes ──
function subir_imagen($archivo, $directorio = './uploads/') {
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Error al subir la imagen. Código: ' . $archivo['error']];
    }
    if ($archivo['size'] > 5242880) { // 5 MB
        return ['error' => 'La imagen supera los 5 MB.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nuevo_nombre = uniqid('logo_club_') . '_' . time() . '.' . $extension;
    $ruta_destino = $directorio . $nuevo_nombre;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        return ['exito' => $nuevo_nombre];
    } else {
        return ['error' => 'Falló el guardado del archivo en el servidor.'];
    }
}

// ── Inicio del procesamiento POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Iniciar Transacción (Si algo falla, no se guarda NADA)
    mysqli_begin_transaction($CONN);

    try {
        // 1. Procesar Logo del Club
        $subida_logo = subir_imagen($_FILES['logo']);
        if (isset($subida_logo['error'])) {
            fallar("Logo del club: " . $subida_logo['error']);
        }
        $ruta_logo = $subida_logo['exito'];

        // 2. Crear Proyecto Padre en e_proyectos
        $nombre_club = mysqli_real_escape_string($CONN, trim($_POST['nombre']));
        $descripcion = mysqli_real_escape_string($CONN, trim($_POST['descripcion']));
        $cargado_por = mysqli_real_escape_string($CONN, trim($_POST['cargado_por']));
        
        $sql_proyecto = "INSERT INTO e_proyectos (id_escuela, nombre, carrera, tipo_formulario, modalidad_trabajo, cargado_por, descripcion) 
                         VALUES ($escuela_id, '$nombre_club', 'Tecnologia', 'Club de Ciencias', 'Presencial', '$cargado_por', '$descripcion')";
        
        if (!mysqli_query($CONN, $sql_proyecto)) fallar("Error al crear el proyecto del club.");
        $id_proyecto_nuevo = mysqli_insert_id($CONN);

        // 3. Crear registro específico del Club (CORREGIDO: Nombre de tabla y columnas según tu captura)
        $sql_club = "INSERT INTO e_club_ciencias (id_proyecto, nombre_club, ruta_logo, descripcion) 
                     VALUES ($id_proyecto_nuevo, '$nombre_club', '$ruta_logo', '$descripcion')";
        
        if (!mysqli_query($CONN, $sql_club)) fallar("Error al guardar los detalles específicos del club.");

        // 4. Guardar Docente (Sincronizado con fecha_nacimiento)
        $d_dni    = mysqli_real_escape_string($CONN, $_POST['docente_dni']);
        $d_nombre = mysqli_real_escape_string($CONN, $_POST['docente_nombre']);
        $d_apelli = mysqli_real_escape_string($CONN, $_POST['docente_apellido']);
        $d_dieta  = mysqli_real_escape_string($CONN, $_POST['docente_dieta']);
        $d_nacim  = mysqli_real_escape_string($CONN, $_POST['docente_nacimiento']);
        $d_celu   = mysqli_real_escape_string($CONN, $_POST['docente_celular']);
        $d_mail   = mysqli_real_escape_string($CONN, $_POST['docente_mail']);
        $d_curso  = mysqli_real_escape_string($CONN, $_POST['docente_curso']);

        $sql_docente = "INSERT INTO e_docentes (id_proyecto, dni, nombre, apellido, dieta_especial, fecha_nacimiento, celular, mail, curso) 
                        VALUES ($id_proyecto_nuevo, '$d_dni', '$d_nombre', '$d_apelli', '$d_dieta', '$d_nacim', '$d_celu', '$d_mail', '$d_curso')";
        if (!mysqli_query($CONN, $sql_docente)) fallar("Error al guardar los datos del docente.");

        // 5. Guardar Alumnos (Sincronizado con fecha_nacimiento)
        if (isset($_POST['alumnos']) && is_array($_POST['alumnos'])) {
            foreach ($_POST['alumnos'] as $alumno) {
                $a_dni    = mysqli_real_escape_string($CONN, $alumno['dni']);
                $a_nombre = mysqli_real_escape_string($CONN, $alumno['nombre']);
                $a_apelli = mysqli_real_escape_string($CONN, $alumno['apellido']);
                $a_dieta  = mysqli_real_escape_string($CONN, $alumno['dieta'] ?? '');
                $a_nacim  = mysqli_real_escape_string($CONN, $alumno['nacimiento']);
                $a_celu   = mysqli_real_escape_string($CONN, $alumno['celular'] ?? '');
                $a_mail   = mysqli_real_escape_string($CONN, $alumno['mail'] ?? '');
                $a_curso  = mysqli_real_escape_string($CONN, $alumno['curso']);

                $sql_alumno = "INSERT INTO e_alumnos (id_proyecto, dni, nombre, apellido, dieta_especial, fecha_nacimiento, celular, mail, curso) 
                               VALUES ($id_proyecto_nuevo, '$a_dni', '$a_nombre', '$a_apelli', '$a_dieta', '$a_nacim', '$a_celu', '$a_mail', '$a_curso')";
                if (!mysqli_query($CONN, $sql_alumno)) fallar("Error al guardar los datos de un alumno.");
            }
        }

        // Confirmamos la transacción si todo anduvo bien
        mysqli_commit($CONN);

        // Limpiar datos viejos de la sesión
        unset($_SESSION['old_club']);

        // Redirigir con éxito
        $_SESSION['msg_ok'] = "¡El Club de Ciencias '$nombre_club' se registró exitosamente!";
        header('Location: dashboard.php');
        exit;

    } catch (Exception $e) {
        fallar("Ocurrió un error inesperado: " . $e->getMessage());
    }
}
?>