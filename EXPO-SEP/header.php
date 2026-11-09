<?php
// ============================================
// public/header.php — Navbar compartida
// ============================================
$pagina_actual = basename($_SERVER['PHP_SELF']);
$es_admin      = isset($_SESSION['admin']) && $_SESSION['admin'] === true;
$logueado      = isset($_SESSION['escuela_id']) || $es_admin;
?>
<!DOCTYPE html>
<html lang="es" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= str_repeat('../', substr_count($pagina_actual, '/')) ?>style/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar-portal">
    <div class="nav-inner">

        <!-- Logo -->
        <a href="<?= $logueado ? ($es_admin ? 'admin.php' : 'dashboard.php') : 'index.php' ?>" class="nav-brand">
            <div class="nav-logo-icon"><i class="fas fa-flask"></i></div>
            <div class="nav-brand-text">
                <span class="nav-title">Portal de Proyectos</span>
                <span class="nav-subtitle">EEST N°2 · Rep. Argentina</span>
            </div>
        </a>

        <!-- Links -->
        <div class="nav-links" id="navLinks">
            <?php if ($logueado): ?>

                <?php if ($es_admin): ?>
                    <a href="admin.php" class="nav-link <?= $pagina_actual === 'admin.php' ? 'active' : '' ?>">
                        <i class="fas fa-th-large"></i> Todos los Proyectos
                    </a>
                <?php else: ?>
                    <a href="dashboard.php" class="nav-link <?= $pagina_actual === 'dashboard.php' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> Mis Proyectos
                    </a>
                    <a href="cargar_proyecto.php" class="nav-link <?= $pagina_actual === 'cargar_proyecto.php' ? 'active' : '' ?>">
                        <i class="fas fa-plus-circle"></i> Cargar Proyecto
                    </a>
                    <a href="perfil.php" class="nav-link <?= $pagina_actual === 'perfil.php' ? 'active' : '' ?>">
                        <i class="fas fa-school"></i> Perfil
                    </a>
                <?php endif; ?>

                <a href="logout.php" class="nav-link nav-link-logout">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>

            <?php else: ?>
                <a href="index.php" class="nav-link <?= $pagina_actual === 'index.php' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <a href="registro.php" class="nav-link <?= $pagina_actual === 'registro.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-plus"></i> Registrarse
                </a>
            <?php endif; ?>
        </div>

        <!-- Controles derecha -->
        <div class="nav-controls">
            <!-- Dark mode toggle -->
            <button class="theme-toggle" id="themeToggle" title="Cambiar tema">
                <i class="fas fa-sun icon-sun"></i>
                <i class="fas fa-moon icon-moon"></i>
            </button>

            <!-- Hamburger -->
            <button class="nav-hamburger" id="navHamburger" aria-label="Menú">
                <span></span><span></span><span></span>
            </button>
        </div>

    </div>
</nav>

<script>
// ── Dark mode ────────────────────────────────
(function() {
    const html    = document.getElementById('html-root');
    const toggle  = document.getElementById('themeToggle');
    const stored  = localStorage.getItem('portal-theme');

    if (stored === 'dark') html.classList.add('dark-mode');

    if (toggle) {
        toggle.addEventListener('click', function() {
            html.classList.toggle('dark-mode');
            localStorage.setItem('portal-theme',
                html.classList.contains('dark-mode') ? 'dark' : 'light');
        });
    }
})();

// ── Hamburger menú móvil ─────────────────────
document.getElementById('navHamburger')?.addEventListener('click', function() {
    document.getElementById('navLinks').classList.toggle('open');
    this.classList.toggle('active');
});
</script>
