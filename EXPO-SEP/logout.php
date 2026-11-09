<?php
// ============================================================
//  logout.php — Cierra la sesión del usuario o admin
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiamos todas las variables de la sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión, también se borra
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruimos la sesión textualmente
session_destroy();

// Redirigir al formulario de login principal
header("Location: index.php");
exit;
?>