<?php
// ============================================
// procesar_registro.php — Registra una escuela (AUDITADO Y CORREGIDO)
// ============================================
require_once './conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.php'); exit;
}

// ═══════════════════════════════════════════════════════
// VALIDACIÓN CLOUDFLARE TURNSTILE (CAPTCHA)
// ═══════════════════════════════════════════════════════

// Secret Key de Cloudflare Turnstile
define('TURNSTILE_SECRET_KEY', '0x4AAAAAADvRguC1UAS3io85jCtH8HTdznE');

$turnstile_response = $_POST['cf-turnstile-response'] ?? '';

if (empty($turnstile_response)) {
    $_SESSION['error_registro'] = 'Por favor, completá la verificación de seguridad (Captcha).';
    header('Location: registro.php'); exit;
}

// Verificar token contra la API de Cloudflare
$verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
$data = [
    'secret'   => TURNSTILE_SECRET_KEY,
    'response' => $turnstile_response,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
        'timeout' => 10
    ]
];

$context  = stream_context_create($options);
$response = @file_get_contents($verify_url, false, $context);

if ($response === false) {
    $_SESSION['error_registro'] = 'Error de conexión con el servicio de verificación. Intentá nuevamente.';
    header('Location: registro.php'); exit;
}

$result = json_decode($response, true);

if (!$result || $result['success'] !== true) {
    $error_msg = 'La verificación de seguridad falló. Intentá nuevamente.';
    if (isset($result['error-codes']) && is_array($result['error-codes'])) {
        $error_msg .= ' (' . implode(', ', $result['error-codes']) . ')';
    }
    $_SESSION['error_registro'] = $error_msg;
    header('Location: registro.php'); exit;
}
// ═══════════════════════════════════════════════════════
// FUNCIONES DE VALIDACIÓN BACK-END
// ═══════════════════════════════════════════════════════

function validarRegex($valor, $regex) {
    return preg_match($regex, $valor);
}

function validarLongitud($valor, $min = null, $max = null) {
    $len = strlen($valor);
    if ($min !== null && $len < $min) return false;
    if ($max !== null && $len > $max) return false;
    return true;
}

function sanitizar($conn, $valor) {
    return mysqli_real_escape_string($conn, trim($valor));
}

function redirigirError($mensaje, $datos = []) {
    $_SESSION['error_registro'] = $mensaje;
    if (!empty($datos)) {
        $_SESSION['old_registro'] = $datos;
    }
    header('Location: registro.php'); exit;
}

// ═══════════════════════════════════════════════════════
// 1. CAPTURA Y SANITIZACIÓN DE DATOS
// ═══════════════════════════════════════════════════════

$campos = [
    'nombre'             => trim($_POST['nombre']            ?? ''),
    'localidad'          => trim($_POST['localidad']         ?? ''),
    'pais'               => trim($_POST['pais']              ?? ''),
    'provincia'          => trim($_POST['provincia']         ?? ''),
    'modalidad_escuela'  => trim($_POST['modalidad_escuela'] ?? ''),
    'email'              => trim($_POST['email']             ?? ''),
    'telefono'           => trim($_POST['telefono']          ?? ''),
    'cue'                => trim($_POST['cue']               ?? ''),
    'mail_institucional' => trim($_POST['mail_institucional']?? ''),
    'director_nombre'    => trim($_POST['director_nombre']   ?? ''),
    'director_apellido'  => trim($_POST['director_apellido'] ?? ''),
    'director_comentario'=> trim($_POST['director_comentario'] ?? ''),
    'password'           => $_POST['password']               ?? '',
    'confirm'            => $_POST['confirm_password']       ?? '',
];

// Guardar para repoblar en caso de error
$_SESSION['old_registro'] = array_diff_key($campos, ['password' => 1, 'confirm' => 1]);

// ═══════════════════════════════════════════════════════
// 2. VALIDACIÓN DE CAMPOS OBLIGATORIOS
// ═══════════════════════════════════════════════════════

$obligatorios = ['nombre', 'localidad', 'pais', 'provincia', 'modalidad_escuela', 
                 'email', 'telefono', 'cue', 'mail_institucional', 
                 'director_nombre', 'director_apellido', 'director_comentario',
                 'password', 'confirm'];

foreach ($obligatorios as $campo) {
    if (empty($campos[$campo])) {
        redirigirError('Completá todos los campos obligatorios.', $_SESSION['old_registro']);
    }
}

// ═══════════════════════════════════════════════════════
// 3. VALIDACIÓN POR TIPO DE CAMPO (DICCIONARIO DE REGLAS)
// ═══════════════════════════════════════════════════════

// NOMBRE: Letras, números, espacios. Max 50
if (!validarRegex($campos['nombre'], '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s]+$/')) {
    redirigirError('El nombre de la institución solo puede contener letras, números y espacios.', $_SESSION['old_registro']);
}
if (!validarLongitud($campos['nombre'], 1, 50)) {
    redirigirError('El nombre de la institución debe tener entre 1 y 50 caracteres.', $_SESSION['old_registro']);
}

// LOCALIDAD: Solo letras y espacios. Max 40
if (!validarRegex($campos['localidad'], '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/')) {
    redirigirError('La localidad solo puede contener letras y espacios.', $_SESSION['old_registro']);
}
if (!validarLongitud($campos['localidad'], 1, 40)) {
    redirigirError('La localidad debe tener entre 1 y 40 caracteres.', $_SESSION['old_registro']);
}

// PAÍS: Solo letras y espacios. Max 40
if (!validarRegex($campos['pais'], '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/')) {
    redirigirError('El país solo puede contener letras y espacios.', $_SESSION['old_registro']);
}
if (!validarLongitud($campos['pais'], 1, 40)) {
    redirigirError('El país debe tener entre 1 y 40 caracteres.', $_SESSION['old_registro']);
}

// PROVINCIA: Solo letras y espacios. Max 40
if (!validarRegex($campos['provincia'], '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/')) {
    redirigirError('La provincia solo puede contener letras y espacios.', $_SESSION['old_registro']);
}
if (!validarLongitud($campos['provincia'], 1, 40)) {
    redirigirError('La provincia debe tener entre 1 y 40 caracteres.', $_SESSION['old_registro']);
}

// CUE: Exactamente 9 dígitos numéricos
if (!ctype_digit($campos['cue']) || strlen($campos['cue']) !== 9) {
    redirigirError('El CUE debe contener exactamente 9 dígitos numéricos.', $_SESSION['old_registro']);
}

// MAIL INSTITUCIONAL: Formato email válido. Max 100
if (!filter_var($campos['mail_institucional'], FILTER_VALIDATE_EMAIL)) {
    redirigirError('El mail institucional no tiene un formato válido.', $_SESSION['old_registro']);
}
if (!validarLongitud($campos['mail_institucional'], 1, 100)) {
    redirigirError('El mail institucional debe tener entre 1 y 100 caracteres.', $_SESSION['old_registro']);
}

// MODALIDAD: Lista blanca
$modalidades_validas = [
    'Educación Secundaria Técnica',
    'Educación Secundaria Orientada',
    'Educación Secundaria Artística Especializada',
    'Educación Secundaria de Jóvenes y Adultos',
    'Educación Secundaria Especial',
];
if (!in_array($campos['modalidad_escuela'], $modalidades_validas, true)) {
    redirigirError('La modalidad de escuela seleccionada no es válida.', $_SESSION['old_registro']);
}

// EMAIL: Formato válido. Max 100
if (!filter_var($campos['email'], FILTER_VALIDATE_EMAIL)) {
    redirigirError('El correo electrónico no es válido.', $_SESSION['old_registro']);
}
if (!validarLongitud($campos['email'], 1, 100)) {
    redirigirError('El correo electrónico debe tener entre 1 y 100 caracteres.', $_SESSION['old_registro']);
}

// TELÉFONO: Solo números, entre 10 y 15 dígitos
if (!ctype_digit($campos['telefono'])) {
    redirigirError('El teléfono debe contener solo números.', $_SESSION['old_registro']);
}
if (!validarLongitud($campos['telefono'], 10, 15)) {
    redirigirError('El teléfono debe tener entre 10 y 15 dígitos.', $_SESSION['old_registro']);
}

// CONTRASEÑA: Mínimo 8 caracteres
if (strlen($campos['password']) < 8) {
    redirigirError('La contraseña debe tener al menos 8 caracteres.', $_SESSION['old_registro']);
}

// CONFIRMACIÓN: Debe coincidir
if ($campos['password'] !== $campos['confirm']) {
    redirigirError('Las contraseñas no coinciden.', $_SESSION['old_registro']);
}

// ═══════════════════════════════════════════════════════
// 4. VERIFICAR EMAIL ÚNICO
// ═══════════════════════════════════════════════════════

$email_safe = sanitizar($CONN, $campos['email']);
$check = mysqli_query($CONN, "SELECT id FROM e_escuelas WHERE email = '$email_safe'");
if (mysqli_num_rows($check) > 0) {
    redirigirError('Ya existe una cuenta con ese correo electrónico.', $_SESSION['old_registro']);
}

// ═══════════════════════════════════════════════════════
// 5. HASHEAR CONTRASEÑA E INSERTAR
// ═══════════════════════════════════════════════════════

$hash = password_hash($campos['password'], PASSWORD_ARGON2ID, [
    'memory_cost' => 1 << 17,
    'time_cost'   => 3,
    'threads'     => 1,
]);

$nombre_safe      = sanitizar($CONN, $campos['nombre']);
$localidad_safe   = sanitizar($CONN, $campos['localidad']);
$pais_safe        = sanitizar($CONN, $campos['pais']);
$provincia_safe   = sanitizar($CONN, $campos['provincia']);
$modalidad_safe   = sanitizar($CONN, $campos['modalidad_escuela']);
$telefono_safe    = sanitizar($CONN, $campos['telefono']);
$hash_safe        = sanitizar($CONN, $hash);
$cue_safe         = sanitizar($CONN, $campos['cue']);
$mail_inst_safe   = sanitizar($CONN, $campos['mail_institucional']);
$dir_nom_safe     = sanitizar($CONN, $campos['director_nombre']);
$dir_ape_safe     = sanitizar($CONN, $campos['director_apellido']);
$dir_com_safe     = sanitizar($CONN, $campos['director_comentario']);

$insert = "INSERT INTO e_escuelas (nombre, localidad, email, password, telefono, pais, provincia, modalidad_escuela, cue, mail_institucional, director_nombre, director_apellido, director_comentario)
           VALUES ('$nombre_safe', '$localidad_safe', '$email_safe', '$hash_safe', '$telefono_safe', '$pais_safe', '$provincia_safe', '$modalidad_safe', '$cue_safe', '$mail_inst_safe', '$dir_nom_safe', '$dir_ape_safe', '$dir_com_safe')";

if (mysqli_query($CONN, $insert)) {
    unset($_SESSION['old_registro']);
    $_SESSION['success_registro'] = '¡Cuenta creada! Ya podés iniciar sesión.';
    header('Location: index.php');
} else {
    redirigirError('Error al registrar. Intentá nuevamente.', $_SESSION['old_registro']);
}
exit;