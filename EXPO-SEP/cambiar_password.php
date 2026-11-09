<?php
// ============================================
// cambiar_password.php
// ============================================
require_once './conexion/conexion.php';

if (!isset($_SESSION['escuela_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$escuela_id   = (int)$_SESSION['escuela_id'];
$pass_actual  = $_POST['pass_actual']  ?? '';
$pass_nueva   = $_POST['pass_nueva']   ?? '';
$pass_confirm = $_POST['pass_confirmar'] ?? '';

if (!$pass_actual || !$pass_nueva || !$pass_confirm) {
    $_SESSION['msg_err'] = 'Completá todos los campos.';
    header('Location: perfil.php'); exit;
}

if ($pass_nueva !== $pass_confirm) {
    $_SESSION['msg_err'] = 'Las contraseñas nuevas no coinciden.';
    header('Location: perfil.php'); exit;
}

if (strlen($pass_nueva) < 8) {
    $_SESSION['msg_err'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
    header('Location: perfil.php'); exit;
}

// Verificar contraseña actual
$esc = mysqli_fetch_assoc(mysqli_query($CONN,
    "SELECT password FROM e_escuelas WHERE id = $escuela_id"
));

if (!password_verify($pass_actual, $esc['password'])) {
    $_SESSION['msg_err'] = 'La contraseña actual es incorrecta.';
    header('Location: perfil.php'); exit;
}

// Actualizar
$hash = password_hash($pass_nueva, PASSWORD_ARGON2ID, [
    'memory_cost' => 1 << 17, 'time_cost' => 3, 'threads' => 1
]);
$hash_safe = mysqli_real_escape_string($CONN, $hash);
mysqli_query($CONN, "UPDATE e_escuelas SET password = '$hash_safe' WHERE id = $escuela_id");

$_SESSION['msg_ok'] = 'Contraseña actualizada correctamente.';
header('Location: perfil.php');
exit;
