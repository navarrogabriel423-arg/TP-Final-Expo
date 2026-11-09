<?php
// ============================================
// setup_admin.php
// Ejecutá este archivo UNA SOLA VEZ para generar
// el hash de la contraseña del admin.
// Después borralo o protegelo con .htaccess.
// ============================================

// 1. Cambiá esta contraseña por la que quieras usar
$password_admin = 'Admin2025!';

// 2. Genera el hash
$hash = password_hash($password_admin, PASSWORD_ARGON2ID, [
    'memory_cost' => 1 << 17,
    'time_cost'   => 3,
    'threads'     => 1,
]);

echo "<h2>Hash generado para el administrador:</h2>";
echo "<code style='word-break:break-all;'>" . $hash . "</code>";
echo "<br><br>";
echo "<strong>Copiá ese hash y reemplazá el valor de ADMIN_PASSWORD en config.php</strong>";
echo "<br><br>";
echo "<em>Contraseña usada: <strong>" . htmlspecialchars($password_admin) . "</strong></em>";
echo "<br><br>";
echo "<strong style='color:red'>⚠ Borrá este archivo después de usarlo.</strong>";
