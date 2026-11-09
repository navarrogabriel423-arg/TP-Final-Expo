<?php
// ============================================================
//  conexion/conexion.php — Configuración central del sistema
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── CONSTANTES DEL ADMINISTRADOR ──
define('ADMIN_EMAIL', 'admin@escuela.com'); 
define('ADMIN_PASSWORD', 'Admin2026!');     
define('ADMIN_NOMBRE', 'Administrador General'); 

// ── CONFIGURACIÓN DE PROYECTOS ──
define('MAX_IMAGENES', 10);             // Subimos a 10 el límite, adaptado a tu formulario visual
define('MAX_SIZE_IMG', 5242880);       // Peso máximo por foto (5 MB en bytes)
define('IMG_EXTENSIONES', ['jpg', 'jpeg', 'png', 'gif', 'webp']); // <-- AGREGÁ ESTA LÍNEA

$DB_HOST   = 'localhost';
$DB_USER   = 'root';      
$DB_PASS   = '';          
$DB_NAME   = 'escuela'; 

// Conexión segura
$CONN = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$CONN) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

mysqli_set_charset($CONN, 'utf8mb4');

// Definición de la constante para las imágenes
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../uploads/');
}

// ── REDIRECCIONES DE SEGURIDAD ──
function requiere_login_portal() {
    if (empty($_SESSION['escuela_id']) && empty($_SESSION['admin'])) {
        header('Location: index.php');
        exit;
    }
}

function requiere_admin() {
    requiere_login_portal();
    if (empty($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header('Location: index.php');
        exit;
    }
}

function esc($conn, $valor) {
    return mysqli_real_escape_string($conn, trim($valor));
}
?>