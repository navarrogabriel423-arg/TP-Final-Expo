<?php
// ============================================
// cargar_proyecto.php — Formulario de carga
// ============================================
require_once './conexion/conexion.php';

if (!isset($_SESSION['escuela_id'])) {
    header('Location: index.php'); exit;
}

$error = '';
if (isset($_SESSION['error_proyecto'])) {
    $error = $_SESSION['error_proyecto'];
    unset($_SESSION['error_proyecto']);
}
$old = $_SESSION['old_proyecto'] ?? [];
unset($_SESSION['old_proyecto']);

$sub_categorias = [
    'Construcciones' => ['Medioambiente', 'Proyectos de modelos constructivos'],
    'Tecnología'      => ['Medioambiente', 'Videojuegos', 'Programación', 'Automatización'],
];
?>
<?php include './public/header.php'; ?>
<title>Cargar Proyecto — Portal de Proyectos</title>

<main class="main-content">

    <div class="page-title">Cargar Proyecto</div>
    <div class="page-subtitle">Completá los datos del proyecto</div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width:760px;">
        <div class="card-header">
            <i class="fas fa-upload"></i> Nuevo Proyecto
        </div>
        <div class="card-body">

            <form action="procesar_proyecto.php"
                  method="POST"
                  enctype="multipart/form-data"
                  id="proyectoForm"
                  novalidate>

                <!-- ── DATOS DEL PROYECTO ── -->
                <h3 style="font-size:1rem; color:var(--text-muted); margin-bottom:1rem; text-transform:uppercase; letter-spacing:.05em;">
                    <i class="fas fa-project-diagram"></i> Datos del proyecto
                </h3>

                <div class="form-group">
                    <label class="form-label" for="nombre">
                        Nombre del proyecto <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="text"
                           id="nombre"
                           name="nombre"
                           class="form-control"
                           placeholder="Ej: Sistema de riego automático"
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
                              rows="4"
                              placeholder="Describí brevemente en qué consiste el proyecto, qué tecnologías usa, qué problema resuelve..."
                              required maxlength="2000"><?= htmlspecialchars($old['descripcion'] ?? '') ?></textarea>
                    <div class="form-hint">Máximo 2000 caracteres.</div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label" for="carrera">
                            Carrera <span style="color:var(--danger)">*</span>
                        </label>
                        <select id="carrera" name="carrera" class="form-control" required>
                            <option value="">— Seleccioná —</option>
                            <option value="Construcciones" <?= (($old['carrera'] ?? '') === 'Construcciones') ? 'selected' : '' ?>>🏗️ Construcciones</option>
                            <option value="Tecnología"      <?= (($old['carrera'] ?? '') === 'Tecnología')      ? 'selected' : '' ?>>💻 Tecnología</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sub_categoria">
                            Subcategoría <span style="color:var(--danger)">*</span>
                        </label>
                        <select id="sub_categoria" name="sub_categoria" class="form-control" required>
                            <option value="">— Elegí primero la carrera —</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div>
                        <div class="form-group">
                            <label class="form-label" for="modalidad_trabajo">
                                Modalidad de trabajo <span style="color:var(--danger)">*</span>
                            </label>
                            <select id="modalidad_trabajo" name="modalidad_trabajo" class="form-control" required>
                                <option value="">— Seleccioná —</option>
                                <option value="Presencial" <?= (($old['modalidad_trabajo'] ?? '') === 'Presencial') ? 'selected' : '' ?>>Presencial</option>
                                <option value="Virtual"    <?= (($old['modalidad_trabajo'] ?? '') === 'Virtual')    ? 'selected' : '' ?>>Virtual</option>
                                <option value="Híbrido"    <?= (($old['modalidad_trabajo'] ?? '') === 'Híbrido')    ? 'selected' : '' ?>>Híbrido</option>
                            </select>
                        </div>
                    </div>
                    <div>
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
                        <div class="form-group" id="plataforma_virtual_group" style="display:none;">
                            <label class="form-label" for="plataforma_virtual">
                                Plataforma virtual <span style="color:var(--danger)">*</span>
                            </label>
                            <select id="plataforma_virtual" name="plataforma_virtual" class="form-control">
                                <option value="">— Seleccioná —</option>
                                <option value="Webex" <?= (($old['plataforma_virtual'] ?? '') === 'Webex') ? 'selected' : '' ?>>Webex</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr style="border-color:var(--border); margin:1.5rem 0;">

                <!-- ── INFORME PDF ── -->
                <h3 style="font-size:1rem; color:var(--text-muted); margin-bottom:1rem; text-transform:uppercase; letter-spacing:.05em;">
                    <i class="fas fa-file-pdf"></i> Informe del proyecto
                </h3>

                <div class="form-group">
                    <label class="form-label" for="archivo">
                        Subir informe (PDF) <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="file" name="archivo" id="archivo" class="form-control" accept="application/pdf" required>
                    <div class="form-hint">Solo formato PDF, máximo <?= defined('MAX_SIZE_PDF') ? round(MAX_SIZE_PDF / 1024 / 1024) : 5 ?> MB.</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="propuesta">
                        Propuesta de actividades <span style="color:var(--text-muted); font-size:.8rem;">(opcional)</span>
                    </label>
                    <textarea id="propuesta"
                              name="propuesta"
                              class="form-control"
                              rows="4"
                              placeholder="Describí las actividades propuestas para el proyecto..."
                              maxlength="2000"><?= htmlspecialchars($old['propuesta'] ?? '') ?></textarea>
                    <div class="form-hint">Máximo 2000 caracteres.</div>
                </div>

                <hr style="border-color:var(--border); margin:1.5rem 0;">
            <div class="form-group">
            <label class="form-label" for="video_url">
                Video de YouTube <span style="color:var(--text-muted); font-size:.8rem;">(opcional)</span>
            </label>
                <div class="input-icon-wrap">
                    <i class="input-icon fab fa-youtube"></i>
                    <input type="url" 
                        id="video_url" 
                        name="video_url" 
                        class="form-control" 
                        placeholder="Ej: https://www.youtube.com/watch?v=..."
                        maxlength="255">
                    </div>
                </div>
                <!-- ── IMÁGENES ── -->
                <h3 style="font-size:1rem; color:var(--text-muted); margin-bottom:1rem; text-transform:uppercase; letter-spacing:.05em;">
                    <i class="fas fa-images"></i> Imágenes del proyecto
                </h3>

                <div class="form-group">
                    <label class="form-label" for="imagenes">
                        Subir imágenes
                        <span style="color:var(--text-muted); font-size:.8rem;">(hasta 10, máx. 3 MB c/u)</span>
                    </label>
                    <input type="file" name="imagenes[]" id="imagenes" class="form-control" accept="image/*" multiple>
                    <div class="form-hint">Formatos: JPG, PNG, GIF, WEBP</div>

                    <!-- Vista previa -->
                    <div class="preview-grid" id="previewGrid"></div>
                    <div class="form-hint" id="imgCount" style="margin-top:.5rem;"></div>
                </div>
                    <!-- ── DATOS DEL DOCENTE y ALUMNOS ── -->

                    <hr style="border-color:var(--border); margin:1.5rem 0;">

                    <h3 style="font-size:1rem; color:var(--text-muted); margin-bottom:1rem; text-transform:uppercase; letter-spacing:.05em;">
                        <i class="fas fa-chalkboard-teacher"></i> Datos del docente
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

                    <!-- Botones -->
                <div class="d-flex gap-2" style="margin-top:1.5rem; flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-cloud-upload-alt"></i> Publicar proyecto
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
const SUBCATEGORIAS = <?= json_encode($sub_categorias, JSON_UNESCAPED_UNICODE) ?>;
const OLD_SUBCAT = <?= json_encode($old['sub_categoria'] ?? '', JSON_UNESCAPED_UNICODE) ?>;
const OLD_PLATAFORMA = <?= json_encode($old['plataforma_virtual'] ?? '', JSON_UNESCAPED_UNICODE) ?>;

const carreraEl = document.getElementById('carrera');
const subCatEl  = document.getElementById('sub_categoria');
const modalidadEl = document.getElementById('modalidad_trabajo');
const plataformaGroupEl = document.getElementById('plataforma_virtual_group');
const plataformaEl = document.getElementById('plataforma_virtual');

function poblarSubcategorias() {
    const carrera = carreraEl.value;
    subCatEl.innerHTML = '';
    if (!carrera || !SUBCATEGORIAS[carrera]) {
        subCatEl.innerHTML = '<option value="">— Elegí primero la carrera —</option>';
        return;
    }
    subCatEl.innerHTML = '<option value="">— Seleccioná —</option>';
    SUBCATEGORIAS[carrera].forEach(sc => {
        const opt = document.createElement('option');
        opt.value = sc;
        opt.textContent = sc;
        if (sc === OLD_SUBCAT) opt.selected = true;
        subCatEl.appendChild(opt);
    });
}

function actualizarPlataformaVirtual() {
    const requierePlataforma = modalidadEl.value === 'Virtual' || modalidadEl.value === 'Híbrido';
    plataformaGroupEl.style.display = requierePlataforma ? 'block' : 'none';
    plataformaEl.required = requierePlataforma;
    if (!requierePlataforma) {
        plataformaEl.value = '';
    }
    if (requierePlataforma && OLD_PLATAFORMA) {
        plataformaEl.value = OLD_PLATAFORMA;
    }
}
carreraEl.addEventListener('change', poblarSubcategorias);
poblarSubcategorias(); // Repoblar si hay valores "old" tras un error

const MAX_IMG = <?= MAX_IMAGENES ?>;
const MAX_SIZE = <?= MAX_SIZE_IMG ?>;

const inputImg   = document.getElementById('imagenes');
const previewGrid = document.getElementById('previewGrid');
const imgCount   = document.getElementById('imgCount');

modalidadEl.addEventListener('change', actualizarPlataformaVirtual);
actualizarPlataformaVirtual();

inputImg.addEventListener('change', function() {
    previewGrid.innerHTML = '';
    const files = Array.from(this.files);

    if (files.length > MAX_IMG) {
        alert('Podés subir como máximo ' + MAX_IMG + ' imágenes.');
        this.value = '';
        imgCount.textContent = '';
        return;
    }

    let valid = true;
    files.forEach(f => {
        if (f.size > MAX_SIZE) {
            alert(f.name + ' supera el límite de 3 MB.');
            valid = false;
        }
    });

    if (!valid) { this.value = ''; previewGrid.innerHTML = ''; imgCount.textContent = ''; return; }

    imgCount.textContent = files.length + ' imagen(es) seleccionada(s)';

    files.forEach(f => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = '<img src="' + e.target.result + '" alt="">';
            previewGrid.appendChild(div);
        };
        reader.readAsDataURL(f);
    });
});

// Validación formulario
document.getElementById('proyectoForm').addEventListener('submit', function(e) {
    const campos = ['nombre','descripcion','carrera','sub_categoria','modalidad_trabajo','cargado_por'];
    for (const id of campos) {
        const el = document.getElementById(id);
        if (!el || !el.value.trim()) {
            e.preventDefault();
            alert('Completá todos los campos obligatorios.');
            if (el) el.focus();
            return;
        }
    }
    if ((modalidadEl.value === 'Virtual' || modalidadEl.value === 'Híbrido') && !plataformaEl.value.trim()) {
        e.preventDefault();
        alert('Seleccioná la plataforma virtual.');
        plataformaEl.focus();
        return;
    }
    if (!document.getElementById('archivo').value) {
        e.preventDefault();
        alert('Subí el informe en PDF.');
        return;
    }
});

// ── Manejo Dinámico de Alumnos (máximo 2) ──
// Código tomado y adaptado desde cargar_club.php
function createEstudianteRow(idx) {
    const div = document.createElement('div');
    div.className = 'card';
    div.style.marginBottom = '.75rem';
    
    const header = document.createElement('button');
    header.type = 'button';
    header.className = 'card-header';
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
    header.style.background = 'linear-gradient(135deg, #2563eb 0%, #1e40af 100%)';
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
        const btn = body.querySelector('.remove-student');
        if (btn) {
            btn.addEventListener('click', function() {
                if (confirm('¿Eliminar este alumno?')) {
                    div.remove();
                    estudianteCount--;
                    updateAlumnoHeaders();
                    updateAddButtonState();
                }
            });
        }
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

// Validaciones adicionales para docente y alumnos antes del envío
document.getElementById('proyectoForm').addEventListener('submit', function(e) {
    // Docente requerido
    const dFields = ['docente_dni','docente_nombre','docente_apellido','docente_nacimiento','docente_celular','docente_mail','docente_curso'];
    for (const id of dFields) {
        const el = document.getElementById(id);
        if (!el || !el.value.trim()) {
            e.preventDefault();
            alert('Completá todos los campos obligatorios del docente.');
            if (el) el.focus();
            return;
        }
    }

    // Validar alumnos (si fueron agregados)
    const alumnoRows = Array.from(estudiantesContainer.querySelectorAll('.card'));
    for (let i = 0; i < alumnoRows.length; i++) {
        const row = alumnoRows[i];
        const dni = row.querySelector('input[name$="[dni]"]');
        const nombre_a = row.querySelector('input[name$="[nombre]"]');
        const apellido = row.querySelector('input[name$="[apellido]"]');
        const nacimiento = row.querySelector('input[name$="[nacimiento]"]');
        const curso = row.querySelector('input[name$="[curso]"]');

        if (!dni.value.trim() || !nombre_a.value.trim() || !apellido.value.trim() || !nacimiento.value || !curso.value.trim()) {
            e.preventDefault();
            alert(`Completá todos los campos obligatorios del Alumno ${i + 1}.`);
            dni.focus();
            return;
        }
    }
});
</script>

<?php include './public/footer.php'; ?>