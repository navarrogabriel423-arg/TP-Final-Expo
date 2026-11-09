<?php
// ============================================
// cargar_robot.php — Formulario de carga de Robótica
// ============================================
require_once './conexion/conexion.php';

if (!isset($_SESSION['escuela_id'])) {
    header('Location: index.php'); exit;
}

$error = '';
if (isset($_SESSION['error_robot'])) {
    $error = $_SESSION['error_robot'];
    unset($_SESSION['error_robot']);
}
$old = $_SESSION['old_robot'] ?? [];
unset($_SESSION['old_robot']);
?>
<?php include './public/header.php'; ?>
<title>Cargar Robótica — Portal de Proyectos</title>

<style>
.robot-panel-header {
    width: 100%;
    border: none;
    padding: .9rem 1rem;
    cursor: pointer;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: #fff;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.robot-panel-header .robot-title { font-weight: 700; }
.robot-panel-header i { transition: transform .18s ease; }
.robot-panel-header[aria-expanded="false"] i { transform: rotate(-90deg); }
</style>

<main class="main-content">

    <div class="page-title">Cargar Robótica</div>
    <div class="page-subtitle">Completá los datos del/los robots</div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width:860px;">
        <div class="card-header">
            <i class="fas fa-robot"></i> Nuevo formulario de Robótica
        </div>
        <div class="card-body">

            <form action="procesar_robot.php" method="POST" enctype="multipart/form-data" id="robotForm" novalidate>

                <h3 style="font-size:1rem; color:var(--text-muted); margin-bottom:1rem; text-transform:uppercase; letter-spacing:.05em;">
                    <i class="fas fa-user-robot"></i> Robots
                </h3>
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

                <div id="robotsContainer"></div>

                <div style="margin-top:.75rem;">
                    <button type="button" id="addRobotBtn" class="btn btn-outline">Agregar otro robot</button>
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

                <hr style="border-color:var(--border); margin:1.5rem 0;">

                <h3 style="font-size:1rem; color:var(--text-muted); margin-bottom:1rem; text-transform:uppercase; letter-spacing:.05em;">
                    <i class="fas fa-image"></i> Foto grupal
                </h3>

                <div class="form-group">
                    <label class="form-label" for="foto_grupal">Foto grupal <span style="color:var(--danger)">*</span></label>
                    <input type="file" id="foto_grupal" name="foto_grupal" class="form-control" accept="image/*" required>
                    <div class="form-hint">Subí una foto grupal representativa. Máximo 5 MB.</div>
                    <div class="preview-grid" id="groupPreview"></div>
                </div>

                <div class="d-flex gap-2" style="margin-top:1.5rem; flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-cloud-upload-alt"></i> Enviar Robótica
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
const MAX_SIZE = <?= MAX_SIZE_IMG ?>;

const robotsContainer = document.getElementById('robotsContainer');
const addRobotBtn = document.getElementById('addRobotBtn');
let robotCount = 0;

function createRobotPanel(data = {}) {
    robotCount += 1;
    const idx = robotCount - 1;

    const panel = document.createElement('div');
    panel.className = 'card';
    panel.style.marginBottom = '.75rem';

    const header = document.createElement('button');
    header.type = 'button';
    header.className = 'card-header robot-panel-header';
    header.setAttribute('aria-expanded', robotCount === 1 ? 'true' : 'false');
    header.innerHTML = `<span class="robot-title">Robot ${robotCount}</span><i class="fas fa-chevron-down"></i>`;

    const body = document.createElement('div');
    body.className = 'card-body';
    body.style.display = 'block';

    // Form fields
    body.innerHTML = `
        <div class="form-group">
            <label class="form-label" for="robots_${idx}_nombre">Nombre del robot <span style="color:var(--danger)">*</span></label>
            <input type="text" id="robots_${idx}_nombre" name="robots[${idx}][nombre]" class="form-control" value="${escapeHtml(data.nombre||'')}" required maxlength="120">
        </div>

        <div class="form-group">
            <label class="form-label" for="robots_${idx}_modalidad">Modalidad <span style="color:var(--danger)">*</span></label>
            <select id="robots_${idx}_modalidad" name="robots[${idx}][modalidad]" class="form-control" required>
                <option value="">— Seleccioná —</option>
                <option value="Futbol">Futbol</option>
                <option value="Velocista">Velocista</option>
                <option value="Dragster">Dragster</option>
                <option value="Triatlon">Triatlon</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="robots_${idx}_frase">Frase / lema <span style="color:var(--danger)">*</span></label>
            <input type="text" id="robots_${idx}_frase" name="robots[${idx}][frase]" class="form-control" value="${escapeHtml(data.frase||'')}" required maxlength="255">
        </div>


        <div class="form-group">
            <label class="form-label" for="robots_${idx}_foto">Foto individual <span style="color:var(--danger)">*</span></label>
            <input type="file" id="robots_${idx}_foto" name="robots[${idx}][foto]" class="form-control" accept="image/*" required>
            <div class="form-hint">Máximo 5 MB por imagen.</div>
            <div class="preview-grid" id="preview_${idx}"></div>
        </div>

        <div style="display:flex; gap:.5rem;">
            <button type="button" class="btn btn-danger remove-btn">Eliminar</button>
        </div>
    `;

    panel.appendChild(header);
    panel.appendChild(body);

    // Behavior: accordion — collapse others when header clicked
    header.addEventListener('click', function() {
        const panels = Array.from(robotsContainer.querySelectorAll('.card'));
        panels.forEach(p => {
            const b = p.querySelector('.card-body');
            const hdr = p.querySelector('.card-header');
            if (p === panel) {
                const isOpen = b.style.display !== 'none';
                b.style.display = isOpen ? 'none' : 'block';
                hdr.setAttribute('aria-expanded', String(!isOpen));
            } else {
                b.style.display = 'none';
                hdr.setAttribute('aria-expanded', 'false');
            }
        });
    });

    // Note: duplication handled by the global "Agregar otro robot" button

    // Remove button
    body.querySelector('.remove-btn').addEventListener('click', function() {
        if (confirm('Eliminar este robot?')) {
            panel.remove();
            updateRobotHeaders();
        }
    });

    // Photo preview
    const fotoInput = body.querySelector(`#robots_${idx}_foto`);
    const previewEl = body.querySelector(`#preview_${idx}`);
    fotoInput.addEventListener('change', function() {
        previewEl.innerHTML = '';
        const f = this.files[0];
        if (!f) return;
        if (f.size > MAX_SIZE) { alert('La imagen supera el límite de 5 MB.'); this.value = ''; return; }
        const r = new FileReader();
        r.onload = e => { const d = document.createElement('div'); d.className='preview-item'; d.innerHTML = `<img src="${e.target.result}" alt="">`; previewEl.appendChild(d); };
        r.readAsDataURL(f);
    });

    robotsContainer.appendChild(panel);
    // By default, collapse newly added panels except the very first
    if (robotCount > 1) {
        panel.querySelector('.card-body').style.display = 'none';
        header.setAttribute('aria-expanded', 'false');
    }

    updateRobotHeaders();
}

function updateRobotHeaders(){
    const panels = Array.from(robotsContainer.querySelectorAll('.card'));
    panels.forEach((p, i) => {
        p.querySelector('.card-header span').textContent = `Robot ${i+1}`;
    });
    robotCount = panels.length || 0;
}

function escapeHtml(s){ return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

// Initialize with one robot panel
createRobotPanel();

addRobotBtn.addEventListener('click', function(){ createRobotPanel(); });

// --------------------------
// Sección: Alumnos dinámica
// --------------------------
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
    if (estudianteCount < 4) {
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
    addEstudianteBtn.disabled = estudianteCount >= 4;
    addEstudianteBtn.style.opacity = estudianteCount >= 4 ? '0.6' : '1';
    addEstudianteBtn.style.cursor = estudianteCount >= 4 ? 'not-allowed' : 'pointer';
}

addEstudianteBtn.addEventListener('click', addEstudiante);
addEstudiante(); // Agregar primer alumno por defecto

// Group photo preview
const groupInput = document.getElementById('foto_grupal');
const groupPreview = document.getElementById('groupPreview');
groupInput.addEventListener('change', function(){
    groupPreview.innerHTML = '';
    const f = this.files[0];
    if (!f) return;
    if (f.size > MAX_SIZE) { alert('La imagen grupal supera el límite de 5 MB.'); this.value = ''; return; }
    const r = new FileReader(); r.onload = e => { const d = document.createElement('div'); d.className='preview-item'; d.innerHTML = `<img src="${e.target.result}" alt="">`; groupPreview.appendChild(d); };
    r.readAsDataURL(f);
});

// Form validation before submit
document.getElementById('robotForm').addEventListener('submit', function(e){
    // Validar docente
    const docenteDni = document.getElementById('docente_dni');
    const docenteNombre = document.getElementById('docente_nombre');
    const docenteApellido = document.getElementById('docente_apellido');
    const docenteNacimiento = document.getElementById('docente_nacimiento');
    const docenteCelular = document.getElementById('docente_celular');
    const docenteMail = document.getElementById('docente_mail');
    const docenteCurso = document.getElementById('docente_curso');

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

    // Validar robots y fotos
    const panels = Array.from(robotsContainer.querySelectorAll('.card'));
    if (panels.length === 0) { e.preventDefault(); alert('Agregá al menos un robot.'); return; }
    for (let i=0;i<panels.length;i++){
        const nombre = panels[i].querySelector(`[name^="robots"][name$="[nombre]"]`) || panels[i].querySelector(`#robots_${i}_nombre`);
        const modalidad = panels[i].querySelector(`#robots_${i}_modalidad`);
        const frase = panels[i].querySelector(`#robots_${i}_frase`);
        const foto = panels[i].querySelector(`#robots_${i}_foto`);
        if (!nombre.value.trim()){ e.preventDefault(); alert('Completá el nombre de cada robot.'); nombre.focus(); return; }
        if (!modalidad.value.trim()){ e.preventDefault(); alert('Seleccioná la modalidad de cada robot.'); modalidad.focus(); return; }
        if (!frase.value.trim()){ e.preventDefault(); alert('Completá la frase de cada robot.'); frase.focus(); return; }
        if (!foto.value){ e.preventDefault(); alert('Subí la foto individual de cada robot.'); foto.focus(); return; }
        if (foto.files[0] && foto.files[0].size > MAX_SIZE) { e.preventDefault(); alert('Una foto individual supera los 5 MB.'); foto.focus(); return; }
    }
    if (!groupInput.value){ e.preventDefault(); alert('Subí la foto grupal.'); groupInput.focus(); return; }
});

</script>

<?php include './public/footer.php'; ?>
