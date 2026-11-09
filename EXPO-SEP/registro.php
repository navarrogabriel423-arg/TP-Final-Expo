<?php
// ============================================
// registro.php — Registro de escuelas (AUDITADO Y CORREGIDO)
// ============================================
require_once './conexion/conexion.php';

if (isset($_SESSION['escuela_id'])) {
    header('Location: dashboard.php'); exit;
}

$error = '';
if (isset($_SESSION['error_registro'])) {
    $error = $_SESSION['error_registro'];
    unset($_SESSION['error_registro']);
}

$old = $_SESSION['old_registro'] ?? [];
unset($_SESSION['old_registro']);
?>
<?php include './public/header.php'; ?>
<title>Registrar Escuela — Portal de Proyectos</title>

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
    align-items: flex-start;
    padding: 2rem 1rem;
    overflow-y: auto;
}

.split-right {
    flex: 1;
    display: none;
    background-color: #0d192b;
    position: fixed;
    top: 0;
    right: 0;
    width: 50%;
    height: 100vh;
    justify-content: center;
    align-items: center;
    padding: 0;
    overflow: hidden;
    z-index: 1;
}

.video-story-container {
    height: 100vh;
    width: auto;
    aspect-ratio: 9 / 16;
    max-width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
}

.video-story-container video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

@media (min-width: 768px) {
    .split-right {
        display: flex;
    }
    .split-left {
        margin-right: 50%;
    }
}

/* Estilos para errores de validación en tiempo real */
.input-error {
    border-color: var(--danger) !important;
    box-shadow: 0 0 0 3px rgba(239,68,68,.15) !important;
}

.input-valid {
    border-color: var(--success) !important;
}

.validation-msg {
    font-size: .78rem;
    margin-top: .3rem;
    min-height: 1.2rem;
}

.validation-msg.error {
    color: var(--danger);
}

.validation-msg.success {
    color: var(--success);
}
</style>

<div class="split-container">
    
    <div class="split-left">
        <div class="auth-card fade-in" style="max-width:520px; width: 100%; margin: auto;">

            <div class="auth-header">
                <div class="auth-icon"><i class="fas fa-school"></i></div>
                <h2>Registrar mi escuela</h2>
                <p>Creá la cuenta de tu institución para subir proyectos</p>
            </div>

            <div class="auth-body">

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="procesar_registro.php" method="POST" novalidate id="regForm">

                    <!-- NOMBRE DE LA INSTITUCIÓN -->
                    <div class="form-group">
                        <label class="form-label" for="nombre">
                            Nombre de la institución <span style="color:var(--danger)">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-school"></i>
                            <input type="text"
                                   id="nombre"
                                   name="nombre"
                                   class="form-control"
                                   placeholder="Ej: EEST N°5 General San Martín"
                                   value="<?= htmlspecialchars($old['nombre'] ?? '') ?>"
                                   required
                                   maxlength="50"
                                   data-regex="^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s]+$"
                                   data-max="50"
                                   data-msg="Solo letras, números y espacios. Máx. 50 caracteres.">
                        </div>
                        <div class="validation-msg" id="msg-nombre"></div>
                    </div>

                    <!-- LOCALIDAD -->
                    <div class="form-group">
                        <label class="form-label" for="localidad">
                            Localidad <span style="color:var(--danger)">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-map-marker-alt"></i>
                            <input type="text"
                                   id="localidad"
                                   name="localidad"
                                   class="form-control"
                                   placeholder="Ej: San Miguel, Buenos Aires"
                                   value="<?= htmlspecialchars($old['localidad'] ?? '') ?>"
                                   required
                                   maxlength="40"
                                   data-regex="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$"
                                   data-max="40"
                                   data-msg="Solo letras y espacios. Máx. 40 caracteres.">
                        </div>
                        <div class="validation-msg" id="msg-localidad"></div>
                    </div>

                    <!-- PAÍS -->
                    <div class="form-group">
                        <label class="form-label" for="pais">
                            País <span style="color:var(--danger)">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-flag"></i>
                            <input type="text"
                                   id="pais"
                                   name="pais"
                                   class="form-control"
                                   placeholder="Ej: Argentina"
                                   value="<?= htmlspecialchars($old['pais'] ?? '') ?>"
                                   required
                                   maxlength="40"
                                   data-regex="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$"
                                   data-max="40"
                                   data-msg="Solo letras y espacios. Máx. 40 caracteres.">
                        </div>
                        <div class="validation-msg" id="msg-pais"></div>
                    </div>

                    <!-- PROVINCIA -->
                    <div class="form-group">
                        <label class="form-label" for="provincia">
                            Provincia <span style="color:var(--danger)">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-map"></i>
                            <input type="text"
                                   id="provincia"
                                   name="provincia"
                                   class="form-control"
                                   placeholder="Ej: Buenos Aires"
                                   value="<?= htmlspecialchars($old['provincia'] ?? '') ?>"
                                   required
                                   maxlength="40"
                                   data-regex="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$"
                                   data-max="40"
                                   data-msg="Solo letras y espacios. Máx. 40 caracteres.">
                        </div>
                        <div class="validation-msg" id="msg-provincia"></div>
                    </div>

                    <!-- CUE -->
                    <div class="form-group">
                        <label class="form-label" for="cue">CUE <span style="color:var(--danger)">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-id-card"></i>
                            <input type="text" 
                                   id="cue" 
                                   name="cue" 
                                   class="form-control" 
                                   placeholder="Ej: 060000000" 
                                   required 
                                   maxlength="9"
                                   minlength="9"
                                   inputmode="numeric"
                                   value="<?= htmlspecialchars($old['cue'] ?? '') ?>"
                                   data-regex="^\d{9}$"
                                   data-exact="9"
                                   data-msg="Exactamente 9 dígitos numéricos.">
                        </div>
                        <div class="validation-msg" id="msg-cue"></div>
                    </div>

                    <!-- MAIL INSTITUCIONAL -->
                    <div class="form-group">
                        <label class="form-label" for="mail_institucional">Mail institucional <span style="color:var(--danger)">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-envelope-open-text"></i>
                            <input type="email" 
                                   id="mail_institucional" 
                                   name="mail_institucional" 
                                   class="form-control" 
                                   placeholder="directivo@escuela.edu.ar" 
                                   required 
                                   maxlength="100"
                                   value="<?= htmlspecialchars($old['mail_institucional'] ?? '') ?>"
                                   data-regex="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                                   data-max="100"
                                   data-msg="Formato de email inválido.">
                        </div>
                        <div class="validation-msg" id="msg-mail_institucional"></div>
                    </div>

                                        <!-- DIRECTOR: NOMBRE -->
                    <div class="form-group">
                        <label class="form-label" for="director_nombre">Nombre del director <span style="color:var(--danger)">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-user"></i>
                            <input type="text" id="director_nombre" name="director_nombre" class="form-control" placeholder="Ej: Juan" required maxlength="20" value="<?= htmlspecialchars($old['director_nombre'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- DIRECTOR: APELLIDO -->
                    <div class="form-group">
                        <label class="form-label" for="director_apellido">Apellido del director <span style="color:var(--danger)">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-user"></i>
                            <input type="text" id="director_apellido" name="director_apellido" class="form-control" placeholder="Ej: García" required maxlength="20" value="<?= htmlspecialchars($old['director_apellido'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- DIRECTOR: COMENTARIO -->
                    <div class="form-group">
                        <label class="form-label" for="director_comentario">Comentario / propuesta <span style="color:var(--danger)">*</span></label>
                        <div class="input-icon-wrap">
                            <textarea id="director_comentario" name="director_comentario" class="form-control" placeholder="Propuestas u observaciones..." required rows="2" maxlength="50"><?= htmlspecialchars($old['director_comentario'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- MODALIDAD -->
                    <div class="form-group">
                        <label class="form-label" for="modalidad_escuela">
                            Modalidad de la escuela <span style="color:var(--danger)">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-book"></i>
                            <select id="modalidad_escuela"
                                    name="modalidad_escuela"
                                    class="form-control"
                                    required>
                                <?php
                                $modalidades = [
                                    'Educación Secundaria Técnica',
                                    'Educación Secundaria Orientada',
                                    'Educación Secundaria Artística Especializada',
                                    'Educación Secundaria de Jóvenes y Adultos',
                                    'Educación Secundaria Especial',
                                ];
                                $modalidad_old = $old['modalidad_escuela'] ?? '';
                                ?>
                                <option value="" disabled <?= $modalidad_old === '' ? 'selected' : '' ?>>Seleccioná una opción</option>
                                <?php foreach ($modalidades as $m): ?>
                                    <option value="<?= htmlspecialchars($m) ?>" <?= $modalidad_old === $m ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- EMAIL -->
                    <div class="form-group">
                        <label class="form-label" for="email">
                            Correo electrónico <span style="color:var(--danger)">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control"
                                   placeholder="maildirectorcontacto@gmail.com"
                                   value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                                   required
                                   maxlength="100"
                                   data-regex="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                                   data-max="100"
                                   data-msg="Formato de email inválido.">
                        </div>
                        <div class="validation-msg" id="msg-email"></div>
                    </div>

                    <!-- TELÉFONO -->
                    <div class="form-group">
                        <label class="form-label" for="telefono">
                            Teléfono <span style="color:var(--danger)">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-phone"></i>
                            <input type="text"
                                   id="telefono"
                                   name="telefono"
                                   class="form-control"
                                   placeholder="Ej: 1145678901"
                                   required
                                   value="<?= htmlspecialchars($old['telefono'] ?? '') ?>"
                                   maxlength="15"
                                   minlength="10"
                                   inputmode="numeric"
                                   data-regex="^\d{10,15}$"
                                   data-min="10"
                                   data-max="15"
                                   data-msg="Solo números. Entre 10 y 15 dígitos.">
                        </div>
                        <div class="validation-msg" id="msg-telefono"></div>
                    </div>

                    <!-- CONTRASEÑA -->
                    <div class="form-group">
                        <label class="form-label" for="password">
                            Contraseña <span style="color:var(--danger)">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Mínimo 8 caracteres"
                                   required
                                   minlength="8"
                                   data-min="8"
                                   data-msg="Mínimo 8 caracteres.">
                            <button type="button" class="input-icon-right" id="togglePass" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="validation-msg" id="msg-password"></div>
                    </div>

                    <!-- CONFIRMAR CONTRASEÑA -->
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">
                            Confirmar contraseña <span style="color:var(--danger)">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password"
                                   id="confirm_password"
                                   name="confirm_password"
                                   class="form-control"
                                   placeholder="Repetí la contraseña"
                                   required
                                   data-match="password"
                                   data-msg="Las contraseñas no coinciden.">
                        </div>
                        <div class="validation-msg" id="msg-confirm_password"></div>
                    </div>
                                    <!-- Cloudflare Turnstile -->
                <div class="form-group" style="display:flex; justify-content:center; margin:1.5rem 0;">
                    <div class="cf-turnstile" 
                         data-sitekey="0x4AAAAAADvRgpgmj1vGU7Z5" 
                         data-theme="light"
                         data-callback="onTurnstileSuccess"></div>
                </div>
                    <button type="submit" class="btn btn-success btn-lg btn-block mt-2" id="btnSubmit">
                        <i class="fas fa-check-circle"></i> Crear cuenta
                    </button>
                    
                </form>

                <div class="auth-divider"><span>¿Ya tenés cuenta?</span></div>

                <a href="index.php" class="btn btn-secondary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                </a>

            </div>
        </div>
    </div>

    <div class="split-right">
        <div class="video-story-container">
            <video src="registrovideo.mp4" controls autoplay muted loop preload="auto" playsinline>
                Tu navegador no soporta la reproducción de este video.
            </video>
        </div>
    </div>

</div>

<script>
// ═══════════════════════════════════════════════════════
// VALIDACIÓN FRONT-END EN TIEMPO REAL
// ═══════════════════════════════════════════════════════

// Diccionario de regex
const REGEX = {
    letras: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/,
    letrasNumeros: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s]+$/,
    textoLibre: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s.,;:!?()-]+$/,
    numeros: /^\d+$/,
    email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
    cue: /^\d{9}$/,
    telefono: /^\d{10,15}$/
};

// Función de validación genérica
function validarCampo(input) {
    const valor = input.value.trim();
    const regexAttr = input.dataset.regex;
    const maxAttr = parseInt(input.dataset.max);
    const minAttr = parseInt(input.dataset.min);
    const exactAttr = parseInt(input.dataset.exact);
    const matchAttr = input.dataset.match;
    const msgEl = document.getElementById('msg-' + input.id);
    
    let esValido = true;
    let mensaje = '';
    
    // Requerido
    if (input.required && valor === '') {
        esValido = false;
        mensaje = 'Este campo es obligatorio.';
    }
    
    // Regex
    else if (regexAttr && valor !== '') {
        const regex = new RegExp(regexAttr);
        if (!regex.test(valor)) {
            esValido = false;
            mensaje = input.dataset.msg || 'Formato inválido.';
        }
    }
    
    // Longitud mínima
    if (esValido && minAttr && valor.length < minAttr) {
        esValido = false;
        mensaje = `Mínimo ${minAttr} caracteres.`;
    }
    
    // Longitud exacta
    if (esValido && exactAttr && valor.length !== exactAttr) {
        esValido = false;
        mensaje = `Debe tener exactamente ${exactAttr} caracteres.`;
    }
    
    // Longitud máxima
    if (esValido && maxAttr && valor.length > maxAttr) {
        esValido = false;
        mensaje = `Máximo ${maxAttr} caracteres.`;
    }
    
    // Coincidencia con otro campo
    if (esValido && matchAttr) {
        const campoMatch = document.getElementById(matchAttr);
        if (campoMatch && valor !== campoMatch.value) {
            esValido = false;
            mensaje = input.dataset.msg || 'Los campos no coinciden.';
        }
    }
    
    // Aplicar estilos
    if (valor === '') {
        input.classList.remove('input-error', 'input-valid');
        if (msgEl) msgEl.textContent = '';
    } else if (esValido) {
        input.classList.remove('input-error');
        input.classList.add('input-valid');
        if (msgEl) {
            msgEl.textContent = '✓ Válido';
            msgEl.className = 'validation-msg success';
        }
    } else {
        input.classList.remove('input-valid');
        input.classList.add('input-error');
        if (msgEl) {
            msgEl.textContent = '✗ ' + mensaje;
            msgEl.className = 'validation-msg error';
        }
    }
    
    return esValido;
}

// Asignar validación en tiempo real a todos los inputs
document.querySelectorAll('#regForm input, #regForm textarea, #regForm select').forEach(input => {
    input.addEventListener('input', function() {
        validarCampo(this);
    });
    
    input.addEventListener('blur', function() {
        validarCampo(this);
    });
});

// Validación completa al enviar
document.getElementById('regForm').addEventListener('submit', function(e) {
    let formValido = true;
    const campos = this.querySelectorAll('input, textarea, select');
    
    campos.forEach(campo => {
        if (!validarCampo(campo)) {
            formValido = false;
        }
    });
    
    if (!formValido) {
        e.preventDefault();
        alert('Por favor, corregí los errores marcados en el formulario antes de continuar.');
        
        // Focus en el primer campo con error
        const primerError = this.querySelector('.input-error');
        if (primerError) primerError.focus();
    }
});

// Toggle password
document.getElementById('togglePass').addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon  = this.querySelector('i');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
});

// Validación de coincidencia de contraseñas en tiempo real
document.getElementById('confirm_password').addEventListener('input', function() {
    const pass = document.getElementById('password').value;
    const msgEl = document.getElementById('msg-confirm_password');
    
    if (this.value === '') {
        this.classList.remove('input-error', 'input-valid');
        msgEl.textContent = '';
    } else if (this.value === pass) {
        this.classList.remove('input-error');
        this.classList.add('input-valid');
        msgEl.textContent = '✓ Las contraseñas coinciden';
        msgEl.className = 'validation-msg success';
    } else {
        this.classList.remove('input-valid');
        this.classList.add('input-error');
        msgEl.textContent = '✗ Las contraseñas no coinciden';
        msgEl.className = 'validation-msg error';
    }
});
</script>

<?php include './public/footer.php'; ?>