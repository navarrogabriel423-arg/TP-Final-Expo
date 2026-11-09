<?php
// ============================================
// conexion/conexion.php
// ============================================

session_start();

// ── Datos de conexión ────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Cambiá por tu usuario de MySQL
define('DB_PASS', '');           // Cambiá por tu contraseña
define('DB_NAME', 'portal_proyectos');

$CONN = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$CONN) {
    die('<div style="font-family:sans-serif;color:#991b1b;padding:2rem;">
        <strong>Error de conexión:</strong> ' . mysqli_connect_error() . '
    </div>');
}

mysqli_set_charset($CONN, 'utf8mb4');

// Incluir configuración global
require_once __DIR__ . '/../config.php';
