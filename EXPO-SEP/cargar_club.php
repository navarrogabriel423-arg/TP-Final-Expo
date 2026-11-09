<?php
// ============================================
// cargar_club.php — Formulario de carga de Club de Ciencia
// ============================================
require_once './conexion/conexion.php';

if (!isset($_SESSION['escuela_id'])) {
    header('Location: index.php'); exit;
}

$error = '';
if (isset($_SESSION['error_club'])) {
    $error = $_SESSION['error_club'];
    unset($_SESSION['error_club']);
}
$old = $_SESSION['old_club'] ?? [];
unset($_SESSION['old_club']);
?>
<?php include './public/header.php'; ?>
<title>Cargar Club de Ciencias — Portal de Proyectos</title>

<main class="main-content">

    <div class="page-title">Cargar Club de Ciencias</div>
    <div class="page-subtitle">Completá los datos del club</div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width:900px;">
        <div class="card-header">
            <i class="fas fa-flask"></i> Nuevo Club de Ciencia
        </div>
        <div class="card-body">

            <form action="procesar_club.php"
                  method="POST"
                  enctype="multipart/form-data"
                  id="clubForm"
                  novalidate>

                <h3 style="font-size:1rem; color:var(--text-muted); margin-bottom:1rem; text-transform:uppercase; letter-spacing:.05em;">
                    <i class="fas fa-atom"></i> Datos del club
                </h3>

                <div class="form-group">
                    <label class="form-label" for="nombre">
                        Nombre del club <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="text"
                           id="nombre"
                           name="nombre"
                           class="form-control"
                           placeholder="Ej: Club de Robótica"
                           value="<?= htmlspecialchars($old['nombre'] ?? '') ?>"
                           required maxlength="120">
                </div>

                <div class="form-group">
                    <label class="form-label" for="descripcion">
                        Descripción <span style="color:var(--danger)">*</span>
                    </label>
                    <textarea id="descripcion"
                              name="descripcion"
                              class="form-control"
                              rows="5"
                              placeholder="Contanos brevemente sobre el club: objetivos, actividades, integrantes..."
                              required maxlength="2000"><?= htmlspecialchars($old['descripcion'] ?? '') ?></textarea>
                    <div class="form-hint">Máximo 2000 caracteres.</div>
                </div>

                <hr style="border-color:var(--border); margin:1.5rem 0;">
                <div class="form-group">
                    <label class="form-label" for="cargado_por">
                        Cargado por <span style="color:var(--danger)">*</span>
                    </label>
                    <select id="cargado_por" name="cargado_por" class="form-control" required>
                        <option value="">— Seleccioná —</option>
                        <option value="Docente" <?= (($old['cargado_por'] ?? '') === 'Docente') ? 'selected' : '' ?>>Docente</option>
                        <option value="Alumno"  <?= (($old['cargado_por'] ?? '') === 'Alumno')  ? 'selected' : '' ?>>Alumno</option>
                    </select>
                </div>
                <h3 style="font-size:1rem; color:var(--text-muted); margin-bottom:1rem; text-transform:uppercase; letter-spacing:.05em;">
                    <i class="fas fa-chalkboard-user"></i> Datos del docente
                </h3>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label" for="docente_dni">DNI <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="docente_dni" name="docente_dni" class="form-control" placeholder="Ej: 12345678" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="docente_nombre">Nombre <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="docente_nombre" name="docente_nombre" class="form-control" placeholder="Ej: Juan" required>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label" for="docente_apellido">Apellido <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="docente_apellido" name="docente_apellido" class="form-control" placeholder="Ej: García" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="docente_dieta">Dieta especial <span style="color:var(--text-muted); font-size:.8rem;">(opcional)</span></label>
                        <input type="text" id="docente_dieta" name="docente_dieta" class="form-control" placeholder="Ej: Vegetariana">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label" for="docente_nacimiento">Fecha de nacimiento <span style="color:var(--danger)">*</span></label>
                        <input type="date" id="docente_nacimiento" name="docente_nacimiento" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="docente_celular">Celular <span style="color:var(--danger)">*</span></label>
                        <input type="tel" id="docente_celular" name="docente_celular" class="form-control" placeholder="Ej: 1123456789" required>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label" for="docente_mail">Email <span style="color:var(--danger)">*</span></label>
                        <input type="email" id="docente_mail" name="docente_mail" class="form-control" placeholder="Ej: docente@email.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="docente_curso">Curso <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="docente_curso" name="docente_curso" class="form-control" placeholder="Ej: 5° Año" required>
                    </div>
                </div>

                <hr style="border-color:var(--border); margin:1.5rem 0;">

                <h3 style="font-size:1rem; color:var(--text-muted); margin-bottom:1rem; text-transform:uppercase; letter-spacing:.05em;">
                    <i class="fas fa-users"></i> Datos de alumnos
                </h3>

                <div id="estudiantesContainer"></div>

                <div style="margin-top:.75rem; margin-bottom:1.5rem;">
                    <button type="button" id="addEstudiante" class="btn btn-outline">Añadir alumno</button>
                </div>

                <div class="form-group">
                    <label class="form-label" for="logo">
                        Logo del club <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="file"
                           id="logo"
                           name="logo"
                           class="form-control"
                           accept="image/*"
                           required>
                    <div class="form-hint">Subí un logo en JPG, PNG, GIF o WEBP. Máximo 5 MB.</div>
                </div>

                <div class="preview-grid" id="logoPreview"></div>

                <div class="d-flex gap-2" style="margin-top:2rem; flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-cloud-upload-alt"></i> Enviar club
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                </div>

            </form>

        </div>
    </div>
</main>

<script>
const logoInput = document.getElementById('logo');
const logoPreview = document.getElementById('logoPreview');
const clubForm = document.getElementById('clubForm');
const MAX_SIZE = <?= MAX_SIZE_IMG ?>;

logoInput.addEventListener('change', function() {
    logoPreview.innerHTML = '';
    const file = this.files[0];

    if (!file) {
        return;
    }

    if (file.size > MAX_SIZE) {
        alert('El archivo supera el límite de 5 MB. Elegí otro logo.');
        this.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(event) {
        const wrapper = document.createElement('div');
        wrapper.className = 'preview-item';
        wrapper.innerHTML = '<img src="' + event.target.result + '" alt="Vista previa del logo">';
        logoPreview.appendChild(wrapper);
    };
    reader.readAsDataURL(file);
});

// Función para crear campo de alumno (card desplegable)
function createEstudianteRow(idx) {
    const div = document.createElement('div');
    div.className = 'card';
    div.style.marginBottom = '.75rem';
    
    const header = document.createElement('button');
    header.type = 'button';
    header.className = 'card-header';
    header.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
    header.style.display = 'flex';
    header.style.justifyContent = 'space-between';
    header.style.alignItems = 'center';
    header.style.width = '100%';
    header.style.padding = '.9rem 1rem';
    header.style.cursor = 'pointer';
    header.style.border = 'none';
    header.style.borderRadius = '0.625rem 0.625rem 0 0';
    header.style.color = '#fff';
    header.setAttribute('aria-expanded', idx === 0 ? 'true' : 'false');
    header.innerHTML = `<span style="font-weight:700; color:#fff; flex:1;">Alumno ${idx + 1}</span><i class="fas fa-chevron-down" style="color:#fff; transition: transform .18s ease; flex-shrink:0;"></i>`;
    
    const body = document.createElement('div');
    body.className = 'card-body';
    body.style.display = idx === 0 ? 'block' : 'none';
    body.innerHTML = `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">DNI <span style="color:var(--danger)">*</span></label>
                <input type="text" name="alumnos[${idx}][dni]" class="form-control" placeholder="Ej: 12345678" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nombre <span style="color:var(--danger)">*</span></label>
                <input type="text" name="alumnos[${idx}][nombre]" class="form-control" placeholder="Ej: María" required>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Apellido <span style="color:var(--danger)">*</span></label>
                <input type="text" name="alumnos[${idx}][apellido]" class="form-control" placeholder="Ej: López" required>
            </div>
            <div class="form-group">
                <label class="form-label">Dieta especial <span style="color:var(--text-muted); font-size:.8rem;">(opcional)</span></label>
                <input type="text" name="alumnos[${idx}][dieta]" class="form-control" placeholder="Ej: Sin TACC">
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Fecha de nacimiento <span style="color:var(--danger)">*</span></label>
                <input type="date" name="alumnos[${idx}][nacimiento]" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Celular <span style="color:var(--text-muted); font-size:.8rem;">(opcional)</span></label>
                <input type="tel" name="alumnos[${idx}][celular]" class="form-control" placeholder="Ej: 1198765432">
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="form-group">
                <label class="form-label">Email <span style="color:var(--text-muted); font-size:.8rem;">(opcional)</span></label>
                <input type="email" name="alumnos[${idx}][mail]" class="form-control" placeholder="Ej: alumno@email.com">
            </div>
            <div class="form-group">
                <label class="form-label">Curso <span style="color:var(--danger)">*</span></label>
                <input type="text" name="alumnos[${idx}][curso]" class="form-control" placeholder="Ej: 5° Año" required>
            </div>
        </div>
        ${idx > 0 ? '<button type="button" class="btn btn-danger remove-student" style="margin-top:.5rem;">Eliminar alumno</button>' : ''}
    `;
    
    div.appendChild(header);
    div.appendChild(body);
    
    // Accordion behavior
    header.addEventListener('click', function(e) {
        e.preventDefault();
        const allCards = Array.from(estudiantesContainer.querySelectorAll('.card'));
        allCards.forEach(card => {
            const cardBody = card.querySelector('.card-body');
            const cardHeader = card.querySelector('.card-header');
            const icon = cardHeader.querySelector('i');
            if (card === div) {
                const isOpen = cardBody.style.display !== 'none';
                cardBody.style.display = isOpen ? 'none' : 'block';
                cardHeader.setAttribute('aria-expanded', String(!isOpen));
                icon.style.transform = isOpen ? 'rotate(-90deg)' : 'rotate(0deg)';
            } else {
                cardBody.style.display = 'none';
                cardHeader.setAttribute('aria-expanded', 'false');
                cardHeader.querySelector('i').style.transform = 'rotate(-90deg)';
            }
        });
    });
    
    // Remove button
    if (idx > 0) {
        body.querySelector('.remove-student').addEventListener('click', function() {
            if (confirm('¿Eliminar este alumno?')) {
                div.remove();
                estudianteCount--;
                updateAlumnoHeaders();
                updateAddButtonState();
            }
        });
    }
    
    return div;
}

const estudiantesContainer = document.getElementById('estudiantesContainer');
const addEstudianteBtn = document.getElementById('addEstudiante');
let estudianteCount = 0;

function addEstudiante() {
    if (estudianteCount < 2) {
        const row = createEstudianteRow(estudianteCount);
        estudiantesContainer.appendChild(row);
        estudianteCount++;
        updateAddButtonState();
    }
}

function updateAlumnoHeaders() {
    const cards = Array.from(estudiantesContainer.querySelectorAll('.card'));
    cards.forEach((card, i) => {
        const header = card.querySelector('.card-header span');
        header.textContent = `Alumno ${i + 1}`;
    });
    estudianteCount = cards.length || 0;
}

function updateAddButtonState() {
    addEstudianteBtn.disabled = estudianteCount >= 2;
    addEstudianteBtn.style.opacity = estudianteCount >= 2 ? '0.6' : '1';
    addEstudianteBtn.style.cursor = estudianteCount >= 2 ? 'not-allowed' : 'pointer';
}

addEstudianteBtn.addEventListener('click', addEstudiante);
addEstudiante(); // Agregar primer alumno por defecto

clubForm.addEventListener('submit', function(e) {
    const nombre = document.getElementById('nombre');
    const descripcion = document.getElementById('descripcion');
    const logo = document.getElementById('logo');
    const docenteDni = document.getElementById('docente_dni');
    const docenteNombre = document.getElementById('docente_nombre');
    const docenteApellido = document.getElementById('docente_apellido');
    const docenteNacimiento = document.getElementById('docente_nacimiento');
    const docenteCelular = document.getElementById('docente_celular');
    const docenteMail = document.getElementById('docente_mail');
    const docenteCurso = document.getElementById('docente_curso');

    // Validar club
    if (!nombre.value.trim() || !descripcion.value.trim() || !logo.value) {
        e.preventDefault();
        alert('Completá todos los campos obligatorios del club.');
        if (!nombre.value.trim()) nombre.focus();
        else if (!descripcion.value.trim()) descripcion.focus();
        else logo.focus();
        return;
    }

    // Validar docente
    if (!docenteDni.value.trim() || !docenteNombre.value.trim() || !docenteApellido.value.trim() ||
        !docenteNacimiento.value || !docenteCelular.value.trim() || !docenteMail.value.trim() || !docenteCurso.value.trim()) {
        e.preventDefault();
        alert('Completá todos los campos obligatorios del docente.');
        docenteDni.focus();
        return;
    }

    // Validar alumnos
    const alumnoRows = Array.from(estudiantesContainer.querySelectorAll('.card'));
    for (let i = 0; i < alumnoRows.length; i++) {
        const row = alumnoRows[i];
        const dni = row.querySelector('input[name$="[dni]"]');
        const nombre_a = row.querySelector('input[name$="[nombre]"]');
        const apellido = row.querySelector('input[name$="[apellido]"]');
        const nacimiento = row.querySelector('input[name$="[nacimiento]"]');
        const curso = row.querySelector('input[name$="[curso]"]');

        if (!dni.value.trim() || !nombre_a.value.trim() || !apellido.value.trim() ||
            !nacimiento.value || !curso.value.trim()) {
            e.preventDefault();
            alert(`Completá todos los campos obligatorios del Alumno ${i + 1}.`);
            dni.focus();
            return;
        }
    }

    if (logo.files[0] && logo.files[0].size > MAX_SIZE) {
        e.preventDefault();
        alert('El logo seleccionado supera los 5 MB. Subí un archivo más liviano.');
        logo.focus();
    }
});
</script>

<?php include './public/footer.php'; ?>
