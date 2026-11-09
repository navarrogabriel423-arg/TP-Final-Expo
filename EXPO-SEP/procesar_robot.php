<?php
// ============================================
// procesar_robot.php — Procesamiento del Formulario (Corregido)
// ============================================
require_once './conexion/conexion.php';

session_start();

if (!isset($_SESSION['escuela_id'])) {
    header('Location: index.php');
    exit;
}

$escuela_id = (int) $_SESSION['escuela_id'];

// BLINDAJE: Evitar entrada por URL directa si ya existe un Robot
$check_robot = mysqli_query($CONN, "SELECT id FROM e_proyectos WHERE id_escuela = $escuela_id AND tipo_formulario = 'Robótica'");
if (mysqli_num_rows($check_robot) > 0) {
    $_SESSION['msg_err'] = "Tu escuela ya tiene un proyecto de Robótica registrado.";
    header('Location: dashboard.php');
    exit;
}

// Función para manejar errores y redirigir
function fallar($mensaje)
{
    global $CONN;
    mysqli_rollback($CONN); // Deshacer cualquier guardado a medias
    $_SESSION['error_robot'] = $mensaje;
    $_SESSION['old_robot'] = $_POST;
    header('Location: cargar_robot.php');
    exit;
}

// ── Función para procesar y validar imágenes ──
function subir_imagen($archivo, $directorio = './uploads/')
{
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Error al subir la imagen. Código: ' . $archivo['error']];
    }
    if ($archivo['size'] > 5242880) { // 5 MB
        return ['error' => 'La imagen supera los 5 MB.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    if ($mime === 'image/webp') {
        return ['error' => 'El formato WebP no está permitido. Usá JPG o PNG.'];
    }

    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    $nuevo_nombre = uniqid('img_') . '_' . time() . '.' . $extension;
    $ruta_destino = $directorio . $nuevo_nombre;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        return ['exito' => $nuevo_nombre];
    } else {
        return ['error' => 'Falló el guardado del archivo en el servidor.'];
    }
}

// ── Inicio del procesamiento POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Iniciar Transacción
    mysqli_begin_transaction($CONN);

    try {
        // 1. Procesar Foto Grupal
        $subida_grupal = subir_imagen($_FILES['foto_grupal']);
        if (isset($subida_grupal['error'])) {
            fallar("Foto grupal: " . $subida_grupal['error']);
        }
        $ruta_foto_grupal = $subida_grupal['exito'];

        // 2. Crear Proyecto Padre en e_proyectos
        $docente_curso = mysqli_real_escape_string($CONN, trim($_POST['docente_curso']));
        $nombre_proyecto = "Proyecto Robótica - Curso " . $docente_curso;
        $cargado_por = isset($_POST['cargado_por']) ? mysqli_real_escape_string($CONN, $_POST['cargado_por']) : '';
        $descripcion = isset($_POST['descripcion']) ? mysqli_real_escape_string($CONN, $_POST['descripcion']) : '';

        $sql_proyecto = "INSERT INTO e_proyectos (id_escuela, nombre, carrera, tipo_formulario, modalidad_trabajo, cargado_por, descripcion) 
                         VALUES ($escuela_id, '$nombre_proyecto', 'Tecnología', 'Robótica', 'Presencial', '$cargado_por', '$descripcion')";

        if (!mysqli_query($CONN, $sql_proyecto))
            fallar("Error al crear el proyecto padre.");
        $id_proyecto_nuevo = mysqli_insert_id($CONN);

        // 3. Guardar Docente (Mantiene tu columna dieta_especial)
        $d_dni = mysqli_real_escape_string($CONN, $_POST['docente_dni']);
        $d_nombre = mysqli_real_escape_string($CONN, $_POST['docente_nombre']);
        $d_apelli = mysqli_real_escape_string($CONN, $_POST['docente_apellido']);
        $d_dieta = mysqli_real_escape_string($CONN, $_POST['docente_dieta']);
        $d_nacim = mysqli_real_escape_string($CONN, $_POST['docente_nacimiento']);
        $d_celu = mysqli_real_escape_string($CONN, $_POST['docente_celular']);
        $d_mail = mysqli_real_escape_string($CONN, $_POST['docente_mail']);

        $sql_docente = "INSERT INTO e_docentes (id_proyecto, dni, nombre, apellido, dieta_especial, fecha_nacimiento, celular, mail, curso) 
                        VALUES ($id_proyecto_nuevo, '$d_dni', '$d_nombre', '$d_apelli', '$d_dieta', '$d_nacim', '$d_celu', '$d_mail', '$docente_curso')";
        if (!mysqli_query($CONN, $sql_docente))
            fallar("Error al guardar los datos del docente.");

        // 4. Guardar Alumnos
        if (isset($_POST['alumnos']) && is_array($_POST['alumnos'])) {
            foreach ($_POST['alumnos'] as $alumno) {
                $a_dni = mysqli_real_escape_string($CONN, $alumno['dni']);
                $a_nombre = mysqli_real_escape_string($CONN, $alumno['nombre']);
                $a_apelli = mysqli_real_escape_string($CONN, $alumno['apellido']);
                $a_dieta = mysqli_real_escape_string($CONN, $alumno['dieta']);
                $a_nacim = mysqli_real_escape_string($CONN, $alumno['nacimiento']);
                $a_celu = mysqli_real_escape_string($CONN, $alumno['celular']);
                $a_mail = mysqli_real_escape_string($CONN, $alumno['mail']);
                $a_curso = mysqli_real_escape_string($CONN, $alumno['curso']);

                $sql_alumno = "INSERT INTO e_alumnos (id_proyecto, dni, nombre, apellido, dieta_especial, fecha_nacimiento, celular, mail, curso) 
                               VALUES ($id_proyecto_nuevo, '$a_dni', '$a_nombre', '$a_apelli', '$a_dieta', '$a_nacim', '$a_celu', '$a_mail', '$a_curso')";
                if (!mysqli_query($CONN, $sql_alumno))
                    fallar("Error al guardar los datos de un alumno.");
            }
        }

        // 5. Guardar datos generales en e_robotica (UNA SOLA VEZ)
        // Tomamos la frase del primer robot (o ajustalo si tenés un campo general)
        $frase_general = isset($_POST['robots'][0]['frase']) ? mysqli_real_escape_string($CONN, $_POST['robots'][0]['frase']) : '';
        
        $sql_robotica = "INSERT INTO e_robotica (id_proyecto, ruta_foto_grupal, frase) 
                         VALUES ('$id_proyecto_nuevo', '$ruta_foto_grupal', '$frase_general')";
        
        if (!mysqli_query($CONN, $sql_robotica)) {
            fallar("Error al guardar los datos generales en la tabla e_robotica.");
        }
        
        // Guardamos el ID de e_robotica para usarlo en todos los robots
        $id_robotica = mysqli_insert_id($CONN);

        // 6. Guardar cada robot en e_robots
        if (isset($_POST['robots']) && is_array($_POST['robots'])) {
            foreach ($_POST['robots'] as $idx => $robot) {
                $r_nombre = mysqli_real_escape_string($CONN, $robot['nombre']);
                $r_modali = mysqli_real_escape_string($CONN, $robot['modalidad']);
                $r_frase = mysqli_real_escape_string($CONN, $robot['frase']);

                // Rearmamos el array del archivo de la foto del robot
                $archivo_simulado = [
                    'name' => $_FILES['robots']['name'][$idx]['foto'],
                    'type' => $_FILES['robots']['type'][$idx]['foto'],
                    'tmp_name' => $_FILES['robots']['tmp_name'][$idx]['foto'],
                    'error' => $_FILES['robots']['error'][$idx]['foto'],
                    'size' => $_FILES['robots']['size'][$idx]['foto']
                ];

                $subida_robot = subir_imagen($archivo_simulado);
                if (isset($subida_robot['error'])) {
                    fallar("Foto del robot '$r_nombre': " . $subida_robot['error']);
                }
                $ruta_foto_robot = $subida_robot['exito'];

                // Insertamos en e_robots vinculando al $id_robotica generado afuera
                $sql_robot = "INSERT INTO e_robots (id_proyecto, id_robotica, nombre_robot, ruta_foto_robot, modalidad_robot, frase)
                              VALUES ('$id_proyecto_nuevo', '$id_robotica', '$r_nombre', '$ruta_foto_robot', '$r_modali', '$r_frase')";
                
                if (!mysqli_query($CONN, $sql_robot)) {
                    fallar("Error al guardar el robot '$r_nombre' en la tabla e_robots.");
                }
            }
        }

        // Si todo salió bien, confirmamos los cambios en la BD
        mysqli_commit($CONN);

        $_SESSION['msg_ok'] = "¡Todos los datos (Docente, Alumnos y Robots) se cargaron exitosamente!";
        header('Location: dashboard.php');
        exit;

    } catch (Exception $e) {
        fallar("Ocurrió un error inesperado: " . $e->getMessage());
    }
}
?>