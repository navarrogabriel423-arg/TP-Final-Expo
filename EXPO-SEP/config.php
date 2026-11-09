<?php
// ============================================
// config.php — Constantes globales del portal
// ============================================

// ── Credenciales del administrador ──────────
// Cambiá estos valores antes de publicar en producción
define('ADMIN_EMAIL',    'admin@eest2.edu.ar');
define('ADMIN_PASSWORD', '$2y$10$PLACEHOLDER'); // Se genera en setup_admin.php
define('ADMIN_NOMBRE',   'EEST N°2 - Administrador');

// ── Límites de subida ────────────────────────
define('MAX_IMAGENES',   10);
define('MAX_SIZE_IMG',   3 * 1024 * 1024); // 3 MB por imagen
define('UPLOAD_DIR',     __DIR__ . '/uploads/');
define('UPLOAD_URL',     'uploads/');

// ── Extensiones permitidas ───────────────────
define('IMG_EXTENSIONES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ── Nombre del sitio ─────────────────────────
define('SITE_NAME', 'Portal de Proyectos Escolares');
define('SITE_YEAR', date('Y'));
