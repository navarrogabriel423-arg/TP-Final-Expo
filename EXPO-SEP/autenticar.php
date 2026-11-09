<?php
// ============================================================
//  autenticar.php — Procesa el inicio de sesión (Fix Localidad)
// ============================================================
require_once './conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    $_SESSION['error_login'] = 'Por favor completá todos los campos.';
    header('Location: index.php');
    exit;
}

// 1. Verificación estricta del Administrador (Texto plano directo)
if ($email === ADMIN_EMAIL && $password === ADMIN_PASSWORD) {
    $_SESSION['admin'] = true;
    $_SESSION['escuela_nombre'] = ADMIN_NOMBRE;
    header('Location: admin.php');
    exit;
}

// 2. Verificación estricta de Escuelas en la Base de Datos
$email_safe = mysqli_real_escape_string($CONN, $email);
// Cambiado a tu tabla real 'e_escuelas'
$query = "SELECT * FROM e_escuelas WHERE email = '$email_safe'";
$result = mysqli_query($CONN, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $escuela = mysqli_fetch_assoc($result);
    
    // Verifica tanto si es un hash Argon2id válido, como un fallback de texto plano por si metiste usuarios a mano en la DB
    if (password_verify($password, $escuela['password']) || $password === $escuela['password']) {
        $_SESSION['escuela_id']        = $escuela['id'];
        $_SESSION['escuela_nombre']    = $escuela['nombre'];
        $_SESSION['escuela_localidad'] = $escuela['localidad'];
        
        header('Location: dashboard.php');
        exit;
    }
}

// 3. REBOTE CRÍTICO: Si no entró en ninguno de los IF anteriores, los datos son falsos.
$_SESSION['error_login'] = 'El correo electrónico o la contraseña son incorrectos.';
header('Location: index.php');
exit;

// Si llegó acá es porque el usuario no existe o la contraseña está mal
$_SESSION['error_login'] = 'El correo electrónico o la contraseña son incorrectos.';
header('Location: index.php');
exit;
?>