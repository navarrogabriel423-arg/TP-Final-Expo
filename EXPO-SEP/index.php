<?php
// ============================================
// index.php — Página principal / Login (CORREGIDO)
// ============================================
require_once './conexion/conexion.php';

// Si ya está logueado, redirigir
if (isset($_SESSION['escuela_id'])) {
    header('Location: dashboard.php'); exit;
}
if (isset($_SESSION['admin']) && $_SESSION['admin']) {
    header('Location: admin.php'); exit;
}

$error = '';
if (isset($_SESSION['error_login'])) {
    $error = $_SESSION['error_login'];
    unset($_SESSION['error_login']);
}
$success = '';
if (isset($_SESSION['success_registro'])) {
    $success = $_SESSION['success_registro'];
    unset($_SESSION['success_registro']);
}
?>
<?php include './public/header.php'; ?>
<title>Iniciar Sesión — Portal de Proyectos</title>

<style>
.split-container {
    display: flex;
    min-height: 100vh;
    width: 100%;
    background-color: #0f172a;
}

.split-left {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem 1rem;
    overflow-y: auto;
}

/* ═══════════════════════════════════════════════════════ */
/* CORREGIDO: La imagen se estira para cubrir TODO el    */
/* ancho y alto del panel derecho (puede deformarse)     */
/* ═══════════════════════════════════════════════════════ */
.split-right {
    flex: 1;
    display: none; 
    background-image: url('fotoindex.png'); 
    background-position: center;
    background-repeat: no-repeat;
    background-size: 100% 100%;   /* ← ANTES: cover | AHORA: 100% 100% */
    position: relative;
    min-height: 100vh;            /* ← Asegura altura completa */
}

@media (min-width: 768px) {
    .split-right {
        display: block;
    }
}
</style>

<div class="split-container">

    <div class="split-left">
        <div class="auth-card fade-in" style="max-width:460px; width: 100%; margin: auto;">

            <div class="auth-header">
                <h2>Portal de Proyectos</h2>
                <p>Iniciá sesión con la cuenta de tu escuela</p>
            </div>

            <div class="auth-body">

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form action="autenticar.php" method="POST" id="loginForm" novalidate>

                    <div class="form-group">
                        <label class="form-label" for="email">
                            <i class="fas fa-envelope"></i> Correo electrónico
                        </label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control"
                                   placeholder="escuela@ejemplo.com"
                                   required
                                   autocomplete="email">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock"></i> Contraseña
                        </label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Tu contraseña"
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="input-icon-right" id="togglePass" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block mt-2">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </form>

                <div class="auth-divider"><span>¿Tu escuela no está registrada?</span></div>

                <a href="registro.php" class="btn btn-secondary btn-block">
                    <i class="fas fa-plus-circle"></i> Registrar mi escuela
                </a>

            </div>
        </div>
    </div>

    <div class="split-right">
        <!-- La imagen fotoindex.png se renderiza como background -->
    </div>

</div>

<script>
// Toggle mostrar/ocultar contraseña
document.getElementById('togglePass').addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon  = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

// Validación básica
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value.trim();
    const pass  = document.getElementById('password').value;
    if (!email || !pass) {
        e.preventDefault();
        alert('Por favor completá todos los campos.');
    }
});
</script>

<?php include './public/footer.php'; ?>