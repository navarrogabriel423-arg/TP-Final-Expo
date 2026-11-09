<?php
session_start();
include('./conexion/conexion.php');

$options = [
    'memory_cost' => 1<<17, // 128 MB
    'time_cost'   => 3,
    'threads'     => 1
];

// Variables para alertas internas y control de pasos
$mensaje_error = "";
$mensaje_exito = "";
$mostrar_paso2 = false;
$escuela_encontrada = null;

// ==========================================
// PASO 1: BÚSQUEDA POR CUE Y MAIL
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buscar_escuela'])) {
    $cue = trim($_POST['cue']);
    $mail_institucional = trim($_POST['mail_institucional']);

    if (strlen($cue) !== 9 || !is_numeric($cue)) {
        $mensaje_error = "El CUE debe tener exactamente 9 dígitos numéricos.";
    } elseif (empty($mail_institucional)) {
        $mensaje_error = "Por favor, ingresa el correo institucional.";
    } else {
        $cue = mysqli_real_escape_string($CONN, $cue);
        $mail_institucional = mysqli_real_escape_string($CONN, $mail_institucional);

        // Buscar en la tabla e_escuelas
        $query = "SELECT id, cue, nombre, localidad, mail_institucional, email, password 
                  FROM e_escuelas 
                  WHERE cue = '$cue' AND mail_institucional = '$mail_institucional'";
        $result = mysqli_query($CONN, $query);

        if (mysqli_num_rows($result) == 1) {
            $escuela_encontrada = mysqli_fetch_assoc($result);
            
            // Si ya tiene contraseña, significa que ya está registrada en el sistema
            if (!empty($escuela_encontrada['password'])) {
                $_SESSION['error_login'] = "Esta institución ya está registrada. Iniciá sesión directamente.";
                header("Location: index.php");
                exit;
            }
            
            // Si existe y no tiene clave, avisamos que existe y pasamos al Paso 2
            $mensaje_exito = "¡Institución verificada con éxito! Proceda a crear su contraseña.";
            $mostrar_paso2 = true;
        } else {
            // Si NO existe en la base de datos
            $mensaje_error = "La escuela no existe en el sistema o los datos son incorrectos. Verificá e intentá de nuevo.";
        }
    }
}

// ==========================================
// PASO 2: ASIGNACIÓN DE CONTRASEÑA EN LA DB
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_password'])) {
    $id_escuela = $_POST['id_escuela'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $mensaje_error = "Las contraseñas no coinciden.";
        $mostrar_paso2 = true;
        
        // Mantener los datos de la escuela en pantalla
        $id_escuela = mysqli_real_escape_string($CONN, $id_escuela);
        $query = "SELECT * FROM e_escuelas WHERE id = '$id_escuela'";
        $result = mysqli_query($CONN, $query);
        $escuela_encontrada = mysqli_fetch_assoc($result);
    } else {
        // Encriptar con Argon2id
        $password_hashed = password_hash($password, PASSWORD_ARGON2ID, $options);
        $id_escuela = mysqli_real_escape_string($CONN, $id_escuela);

        $query = "UPDATE e_escuelas SET password = '$password_hashed' WHERE id = '$id_escuela'";
        $result = mysqli_query($CONN, $query);

        if ($result) {
            // Éxito total: Avisamos y ahora sí redirigimos a index.php para iniciar sesión
            $_SESSION['success_login'] = "¡Contraseña creada con éxito! Ya podés iniciar sesión.";
            header("Location: index.php");
            exit;
        } else {
            $mensaje_error = "Hubo un problema al guardar la contraseña en el servidor.";
            $mostrar_paso2 = true;
            
            $query = "SELECT * FROM e_escuelas WHERE id = '$id_escuela'";
            $result = mysqli_query($CONN, $query);
            $escuela_encontrada = mysqli_fetch_assoc($result);
        }
    }
}

include('./public/header.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style/styles.css">
    <title>Verificación de Institución - EEST N°2</title>

    <style>
    /* Diseño moderno de dos columnas integrado con fotoindex.png */
    .split-container {
        display: flex;
        min-height: 100vh;
        width: 100%;
        background-color: #0b1329;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .split-left {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        overflow-y: auto;
    }

    .split-right {
        flex: 1;
        display: none; 
        background-color: #0d192b; 
        background-image: url('fotoindex.png'); 
        background-position: center center;
        background-repeat: no-repeat;
        background-size: cover; 
    }

    /* Tarjeta Principal */
    .verification-card {
        background-color: #1c2541;
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        width: 100%;
        max-width: 450px;
        color: #ffffff;
    }

    .header-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-subtitle {
        color: #94a3b8;
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
    }

    /* Alertas de Feedback */
    .custom-alert {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .alert-danger {
        background-color: rgba(239, 68, 68, 0.2);
        border: 1px solid #ef4444;
        color: #fca5a5;
    }
    .alert-success {
        background-color: rgba(16, 185, 129, 0.2);
        border: 1px solid #10b981;
        color: #a7f3d0;
    }

    /* Formulario e Inputs */
    .form-group-custom {
        margin-bottom: 1.25rem;
    }

    .form-group-custom label {
        display: block;
        font-size: 0.9rem;
        color: #cbd5e1;
        margin-bottom: 0.5rem;
    }

    .form-group-custom input {
        width: 100%;
        padding: 0.75rem 1rem;
        background-color: #0b1329;
        border: 1px solid #3a506b;
        border-radius: 8px;
        color: #ffffff;
        font-size: 0.95rem;
        box-sizing: border-box;
        transition: border-color 0.2s;
    }

    .form-group-custom input:focus {
        outline: none;
        border-color: #3b82f6;
    }

    /* Barras indicadoras */
    .steps-mini {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .step-pill {
        flex: 1;
        height: 6px;
        background-color: #3a506b;
        border-radius: 4px;
    }

    .step-pill.active {
        background-color: #3b82f6;
    }

    /* Botones */
    .btn-primary-custom {
        width: 100%;
        padding: 0.75rem;
        background-color: #3b82f6;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
        margin-top: 0.5rem;
    }

    .btn-primary-custom:hover {
        background-color: #2563eb;
    }

    .btn-secondary-custom {
        display: block;
        text-align: center;
        width: 100%;
        padding: 0.75rem;
        background-color: transparent;
        color: #94a3b8;
        border: 1px solid #3a506b;
        border-radius: 8px;
        font-size: 0.95rem;
        text-decoration: none;
        box-sizing: border-box;
        margin-top: 0.75rem;
        transition: all 0.2s;
    }

    .btn-secondary-custom:hover {
        background-color: #1e293b;
        color: #ffffff;
    }

    .info-box {
        background-color: rgba(59, 130, 246, 0.15);
        border-left: 4px solid #3b82f6;
        padding: 0.75rem 1rem;
        border-radius: 0 8px 8px 0;
        font-size: 0.85rem;
        color: #93c5fd;
        margin-top: 1.25rem;
        line-height: 1.4;
    }

    @media (min-width: 768px) {
        .split-right {
            display: block;
        }
    }
    </style>
</head>

<body>

<div class="split-container">

    <div class="split-left">
        <div class="verification-card">
            
            <div class="header-title">
                <i class="fas fa-university" style="color: #3b82f6;"></i> Registro Escuela
            </div>
            <div class="header-subtitle">Ingresá los datos asignados a tu institución</div>

            <div class="steps-mini">
                <div class="step-pill active"></div>
                <div class="step-pill <?= $mostrar_paso2 ? 'active' : '' ?>"></div>
            </div>

            <?php if (!empty($mensaje_error)): ?>
                <div class="custom-alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $mensaje_error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($mensaje_exito)): ?>
                <div class="custom-alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $mensaje_exito; ?>
                </div>
            <?php endif; ?>

            <?php if (!$mostrar_paso2): ?>
                <form method="POST" action="" id="formPaso1">
                    <input type="hidden" name="buscar_escuela" value="1">

                    <div class="form-group-custom">
                        <label for="cue"><i class="fas fa-id-card" style="margin-right: 0.3rem;"></i> Número de CUE</label>
                        <input type="text" id="cue" name="cue" placeholder="9 dígitos numéricos de tu escuela" required maxlength="9" minlength="9" value="<?= htmlspecialchars($_POST['cue'] ?? '') ?>">
                    </div>

                    <div class="form-group-custom">
                        <label for="mail_institucional"><i class="fas fa-envelope" style="margin-right: 0.3rem;"></i> Correo Institucional</label>
                        <input type="email" id="mail_institucional" name="mail_institucional" placeholder="ejemplo@abc.gob.ar" required value="<?= htmlspecialchars($_POST['mail_institucional'] ?? '') ?>">
                    </div>

                    <div class="info-box">
                        <strong>Verificación:</strong> Si la escuela existe en la base de datos, pasarás a crear la contraseña de acceso.
                    </div>

                    <button type="submit" class="btn-primary-custom">
                        <i class="fas fa-search" style="margin-right: 0.5rem;"></i> Verificar Escuela
                    </button>

                    <a href="index.php" class="btn-secondary-custom">
                        <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i> Cancelar y Volver
                    </a>
                </form>

            <?php else: ?>
                <div style="background-color: #0b1329; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #3a506b;">
                    <div style="font-size: 0.85rem; color: #94a3b8;">Escuela encontrada:</div>
                    <div style="font-size: 1.1rem; font-weight: 600; color: #3b82f6; margin-top: 0.2rem;"><?= htmlspecialchars($escuela_encontrada['nombre']); ?></div>
                    <div style="font-size: 0.85rem; color: #cbd5e1; margin-top: 0.2rem;">Localidad: <?= htmlspecialchars($escuela_encontrada['localidad']); ?></div>
                </div>

                <form method="POST" action="" id="formPassword">
                    <input type="hidden" name="id_escuela" value="<?= $escuela_encontrada['id']; ?>">
                    <input type="hidden" name="registrar_password" value="1">

                    <div class="form-group-custom">
                        <label for="password"><i class="fas fa-key" style="margin-right: 0.3rem;"></i> Crear Contraseña</label>
                        <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required minlength="8">
                    </div>

                    <div class="form-group-custom">
                        <label for="confirm_password"><i class="fas fa-key" style="margin-right: 0.3rem;"></i> Confirmar Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repetí la contraseña" required minlength="8">
                        <div id="password-match" style="font-size: 0.85rem; margin-top: 0.4rem; font-weight: 600;"></div>
                    </div>

                    <button type="submit" class="btn-primary-custom">
                        <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> Finalizar y Guardar
                    </button>
                </form>

                <form method="POST" action="">
                    <button type="submit" class="btn-secondary-custom">
                        <i class="fas fa-undo" style="margin-right: 0.5rem;"></i> Volver a Buscar
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </div>

    <div class="split-right"></div>

</div>

<script>
    // Validar en tiempo real que sólo se tipeen números en el CUE
    const cueInput = document.getElementById('cue');
    if (cueInput) {
        cueInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    // Comprobar longitud del CUE en el submit del Formulario
    const formPaso1 = document.getElementById('formPaso1');
    if (formPaso1) {
        formPaso1.addEventListener('submit', function(e) {
            if (cueInput.value.trim().length !== 9) {
                e.preventDefault();
                alert('El CUE ingresado debe contener exactamente 9 números.');
            }
        });
    }

    // Validación interactiva visual de contraseñas idénticas
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const matchText = document.getElementById('password-match');

    if (confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (this.value === password.value && this.value !== "") {
                matchText.textContent = "✓ Las contraseñas coinciden";
                matchText.style.color = "#10b981";
            } else if (this.value !== "") {
                matchText.textContent = "✗ Las contraseñas no coinciden";
                matchText.style.color = "#ef4444";
            } else {
                matchText.textContent = "";
            }
        });
    }
</script>

</body>
</html>