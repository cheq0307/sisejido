<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ejido San Rafael Ixtapalucan - Nueva Parcela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('css/estiloNuevoE.css') }}">
    <style>
        .coord-table { width:100%; border-collapse:separate; border-spacing:0; }
        .coord-table thead th {
            background:#f1f5f9; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:.5px; color:#64748b;
            padding:8px 10px; border-bottom:2px solid #e2e8f0;
        }
        .coord-table tbody tr:hover { background:#f8fafc; }
        .coord-table td { padding:4px 6px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }

        .punto-badge {
            display:inline-flex; align-items:center; justify-content:center;
            width:30px; height:30px; border-radius:50%;
            background:#1a4c8b; color:#fff;
            font-size:11px; font-weight:700; flex-shrink:0;
        }
        .coord-input {
            border:1px solid #e2e8f0; border-radius:6px;
            padding:5px 8px; font-size:12px;
            font-family:'Courier New',monospace; width:100%;
            transition:border-color .15s, box-shadow .15s;
        }
        .coord-input:focus { outline:none; border-color:#1a4c8b; box-shadow:0 0 0 3px rgba(26,76,139,.12); }
        .coord-input.valido   { border-color:#16a34a; background:#f0fdf4; }
        .coord-input.invalido { border-color:#dc2626; background:#fef2f2; }
        .coord-input.duplicado{ border-color:#f59e0b; background:#fffbeb; }
        .coord-input:disabled { background:#f8fafc; color:#94a3b8; }

        .btn-add-punto {
            border:2px dashed #cbd5e1; background:transparent; color:#64748b;
            border-radius:8px; padding:5px 14px; font-size:12px; cursor:pointer;
            transition:all .15s; width:100%;
        }
        .btn-add-punto:hover { border-color:#1a4c8b; color:#1a4c8b; background:#f0f4fb; }
        .btn-rm { background:none; border:none; color:#cbd5e1; cursor:pointer; font-size:13px; padding:3px 5px; border-radius:4px; transition:color .12s; }
        .btn-rm:hover { color:#dc2626; }

        #mapa-preview { height:270px; border-radius:10px; border:2px solid #e2e8f0; overflow:hidden; position:relative; }
        #mapa-preview-inner { width:100%; height:100%; }
        #preview-hint { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; color:#94a3b8; font-size:12px; pointer-events:none; z-index:1000; }
        #preview-hint i { font-size:26px; display:block; margin-bottom:5px; }

        .coord-ayuda { background:#f0f7ff; border:1px solid #bfdbfe; border-radius:8px; padding:9px 13px; font-size:12px; color:#1e40af; margin-bottom:10px; }
        .coord-ayuda code { background:#dbeafe; border-radius:3px; padding:1px 5px; font-size:11px; }

        /* Zona de carga de archivo */
        #zona-archivo {
            border:2px dashed #cbd5e1; border-radius:10px; padding:18px;
            text-align:center; cursor:pointer; transition:all .2s;
            background:#fafafa; margin-bottom:12px;
        }
        #zona-archivo:hover, #zona-archivo.dragover { border-color:#1a4c8b; background:#f0f4fb; }
        #zona-archivo i { font-size:28px; color:#94a3b8; display:block; margin-bottom:6px; }
        #zona-archivo .titulo { font-size:13px; font-weight:600; color:#374151; }
        #zona-archivo .subtitulo { font-size:11px; color:#9ca3af; margin-top:3px; }
        #archivo-input { display:none; }

        /* Alerta de errores de importación */
        #errores-importacion { display:none; }

        /* Badge puntos */
        .badge-puntos-min { font-size:10px; padding:2px 7px; background:#fef3c7; color:#92400e; border:1px solid #fde68a; border-radius:20px; }
        .badge-puntos-ok  { font-size:10px; padding:2px 7px; background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; border-radius:20px; }

        .punto-ind { width:8px; height:8px; border-radius:50%; background:#e2e8f0; display:inline-block; transition:background .2s; }
        .punto-ind.ok { background:#16a34a; }
        .punto-ind.dup { background:#f59e0b; }
        .punto-ind.err { background:#dc2626; }

        .btn-ejidal { background:#2d6a2d; border-color:#2d6a2d; color:#fff; }
        .btn-ejidal:hover { background:#1e4d1e; border-color:#1e4d1e; color:#fff; }
        .card-ejidal { border:1px solid #dee2e6; }
        .card-header-ejidal { background:#2d6a2d; color:#fff; font-weight:600; }
        .text-ejidal { color:#2d6a2d; }

        /* Tooltip error */
        .coord-error-msg { font-size:10px; color:#dc2626; margin-top:2px; display:none; }
    </style>
</head>
<body>

@include('IncludeViews.cabeza')

<div class="container-fluid">
<div class="row">
@include('IncludeViews.menu')

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 text-ejidal">Nueva Parcela</h1>
    <a href="{{ route('parcelas.index') }}" class="btn btn-ejidal btn-sm">
        <i class="fas fa-list me-1"></i> Ir al listado
    </a>
</div>

@if($error)
<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</div>
@endif
@if(session('status') === 'success')
<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Información guardada correctamente.</div>
@endif

{{-- Buscador parcela --}}
<form method="GET" action="{{ route('parcelas.ver') }}" class="mb-4">
    <div class="input-group">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="number" class="form-control" name="noParcela"
               placeholder="Buscar parcela por número..." value="{{ request('noParcela') }}" required>
        <button class="btn btn-ejidal">Buscar</button>
    </div>
</form>

{{-- Buscador ejidatario --}}
<div class="card card-ejidal mb-3">
    <div class="card-header card-header-ejidal"><i class="fas fa-user-tie me-2"></i>Buscar Ejidatario</div>
    <div class="card-body">
        <form method="GET">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                <input type="number" class="form-control" name="numeroEjidatario"
                       placeholder="Número de ejidatario" value="{{ request('numeroEjidatario') }}" required>
                <button class="btn btn-ejidal">Buscar</button>
            </div>
        </form>
        @if($ejidatario)
        <div class="alert alert-success mt-2 mb-0">
            <i class="fas fa-check-circle me-2"></i>
            <strong>{{ $ejidatario->nombre }} {{ $ejidatario->apellidoPaterno }} {{ $ejidatario->apellidoMaterno }}</strong>
            <span class="text-muted ms-2">— Ejidatario #{{ $ejidatario->numeroEjidatario }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Formulario --}}
<form method="POST" action="{{ route('parcelas.store') }}" id="form-parcela">
@csrf
<input type="hidden" name="numeroEjidatario" value="{{ $ejidatario->numeroEjidatario ?? '' }}">
<input type="hidden" name="idEjidatario"     value="{{ $ejidatario->idEjidatario ?? '' }}">

@if(!$ejidatario)
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Debes buscar un ejidatario válido antes de guardar.</div>
@endif

{{-- Datos parcela --}}
<div class="card card-ejidal mb-3">
    <div class="card-header card-header-ejidal"><i class="fas fa-map-marker-alt me-2"></i>Datos de la Parcela</div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">No. Parcela <span class="text-danger">*</span></label>
                <input type="number" name="noParcela" class="form-control" {{ $ejidatario ? '' : 'disabled' }}>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Superficie (ha)</label>
                <input type="text" name="superficie" class="form-control" placeholder="ej: 3.5" {{ $ejidatario ? '' : 'disabled' }}>
                  placeholder="ej: 3.5" required {{ $ejidatario ? '' : 'disabled' }}>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Uso de Suelo</label>
                <select name="usoSuelo" class="form-select" {{ $ejidatario ? '' : 'disabled' }}>
                    @foreach($usos as $uso)
                    <option value="{{ $uso->idUso }}">{{ $uso->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" {{ $ejidatario ? '' : 'disabled' }}>
            </div>
        </div>
    </div>
</div>

{{-- Colindancias --}}
<div class="card card-ejidal mb-3">
    <div class="card-header card-header-ejidal"><i class="fas fa-compass me-2"></i>Colindancias</div>
    <div class="card-body">
        <div class="row g-2">
            @foreach(['norte','sur','este','oeste','noreste','noroeste','sureste','suroeste'] as $c)
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:13px">{{ ucfirst($c) }}</label>
                <input type="text" name="{{ $c }}" class="form-control form-control-sm" {{ $ejidatario ? '' : 'disabled' }}>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Coordenadas --}}
<div class="card card-ejidal mb-3">
    <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
        <span><i class="fas fa-map-pin me-2"></i>Coordenadas GPS del Polígono</span>
        <span id="badge-puntos" class="badge-puntos-min">Mín. 3 puntos</span>
    </div>
    <div class="card-body">

        {{-- Ayuda --}}
        <div class="coord-ayuda">
            <i class="fas fa-info-circle me-2"></i>
            Formato <strong>decimal</strong>. Latitud: <code>19.307519...</code> &nbsp;|&nbsp;
            Longitud: <code>-98.415076...</code> (negativo para México — se corrige automáticamente si pones positivo).
            Soporta hasta <strong>15 decimales</strong>. Mín. 3 puntos para el polígono.
        </div>

        {{-- Zona carga de archivo --}}
        <div id="zona-archivo" onclick="document.getElementById('archivo-input').click()"
             ondragover="event.preventDefault();this.classList.add('dragover')"
             ondragleave="this.classList.remove('dragover')"
             ondrop="manejarDrop(event)">
            <i class="fas fa-file-upload"></i>
            <div class="titulo">Cargar coordenadas desde archivo</div>
            <div class="subtitulo">Arrastra un .TXT o .CSV aquí, o haz clic para seleccionar</div>
            <div class="subtitulo mt-1" style="color:#6366f1;font-weight:600">
                Formato: una coordenada por línea &nbsp;|&nbsp; lat,lng &nbsp;o&nbsp; lat lng
            </div>
        </div>
        <input type="file" id="archivo-input" accept=".txt,.csv" onchange="cargarArchivo(this)">

        {{-- Errores de importación --}}
        <div id="errores-importacion" class="alert alert-warning py-2 mb-2" style="font-size:12px"></div>

        <div class="row g-3">
            {{-- Tabla de puntos --}}
            <div class="col-lg-7">
                <table class="coord-table">
                    <thead>
                        <tr>
                            <th style="width:55px">Punto</th>
                            <th>Latitud</th>
                            <th>Longitud</th>
                            <th style="width:36px"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-coordenadas"></tbody>
                </table>

                <button type="button" class="btn-add-punto mt-2" onclick="agregarPunto()"
                        {{ $ejidatario ? '' : 'disabled' }}>
                    <i class="fas fa-plus me-2"></i>Agregar punto
                </button>

                <div class="d-flex align-items-center gap-2 mt-2" style="font-size:12px;color:#64748b">
                    Puntos: <strong id="count-puntos">0</strong>
                    <div id="indicadores" class="d-flex gap-1"></div>
                    <span id="msg-duplicados" class="text-warning" style="display:none;font-size:11px">
                        <i class="fas fa-exclamation-triangle"></i> Hay puntos duplicados
                    </span>
                </div>
            </div>

            {{-- Mini mapa --}}
            <div class="col-lg-5">
                <div id="mapa-preview">
                    <div id="mapa-preview-inner"></div>
                    <div id="preview-hint">
                        <i class="fas fa-draw-polygon"></i>
                        Ingresa coordenadas<br>para previsualizar
                    </div>
                </div>
                <div class="text-center mt-1" style="font-size:11px;color:#94a3b8">
                    <i class="fas fa-eye me-1"></i>Vista previa del polígono
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Info administrativa --}}
<div class="card card-ejidal mb-3">
    <div class="card-header card-header-ejidal"><i class="fas fa-file-alt me-2"></i>Información Administrativa</div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Número de inscripción RAN</label>
                <input type="text" name="num_inscripcionRAN" class="form-control" {{ $ejidatario ? '' : 'disabled' }}>
            </div>
            <div class="col-md-7">
                <label class="form-label fw-semibold">Clave núcleo agrario</label>
                <input type="text" name="claveNucleoAgrario" class="form-control" {{ $ejidatario ? '' : 'disabled' }}>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Comunidad</label>
                <input type="text" name="comunidad" class="form-control"
                       placeholder="San Rafael Ixtapalucan" {{ $ejidatario ? '' : 'disabled' }}>
            </div>
            <div class="col-md-7">
                <label class="form-label fw-semibold">Fecha de expedición</label>
                <input type="date" name="fechaExpedicion" class="form-control" {{ $ejidatario ? '' : 'disabled' }}>
            </div>
        </div>
    </div>
</div>

<div class="text-end mt-4 mb-5">
    <a href="{{ route('parcelas.index') }}" class="btn btn-outline-secondary me-2">
        <i class="fas fa-times me-1"></i>Cancelar
    </a>
    <button type="submit" class="btn btn-ejidal px-4" {{ $ejidatario ? '' : 'disabled' }}>
        <i class="fas fa-save me-2"></i>Guardar Parcela
    </button>
</div>

</form>
</main>
</div>
</div>

@include('IncludeViews.pie')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const HABILITADO = {{ $ejidatario ? 'true' : 'false' }};

// ── Numeración profesional tipo RAN ─────────────────────────
// P1, P2, P3... sin límite
function etiquetaPunto(n) {
    return 'P' + n;
}

// ── Estado ───────────────────────────────────────────────────
let puntos = []; // [{ num, lat, lng }]
let mapaIniciado = false;
let mapaPreview  = null;
let polyPreview  = null;
let markersPreview = [];

// ── Inicializar con 4 puntos vacíos ─────────────────────────
function init() {
    for (let i = 0; i < 4; i++) agregarPunto(false);
}

// ── Agregar fila ─────────────────────────────────────────────
function agregarPunto(actualizar = true) {
    if (!HABILITADO) return;
    const num = puntos.length + 1;
    const idx = puntos.length;
    puntos.push({ num, lat: '', lng: '' });

    const tr = document.createElement('tr');
    tr.id = 'fila_' + idx;
    tr.innerHTML = `
        <td>
            <span class="punto-badge" id="badge_${idx}">${etiquetaPunto(num)}</span>
            <input type="hidden" name="punto[]" id="nombre_${idx}" value="${etiquetaPunto(num)}">
        </td>
        <td>
            <input type="text" inputmode="decimal"
                   name="coordenadaX[]" id="lat_${idx}"
                   class="coord-input" placeholder="19.307519..."
                   oninput="actualizarPunto(${idx}, 'lat', this.value)"
                   ${HABILITADO ? '' : 'disabled'}>
            <div class="coord-error-msg" id="err_lat_${idx}"></div>
        </td>
        <td>
            <input type="text" inputmode="decimal"
                   name="coordenadaY[]" id="lng_${idx}"
                   class="coord-input" placeholder="-98.415076..."
                   oninput="actualizarPunto(${idx}, 'lng', this.value)"
                   ${HABILITADO ? '' : 'disabled'}>
            <div class="coord-error-msg" id="err_lng_${idx}"></div>
        </td>
        <td>
            ${num > 3 ? `<button type="button" class="btn-rm" onclick="quitarPunto(${idx})" title="Quitar punto">
                <i class="fas fa-times"></i>
            </button>` : ''}
        </td>`;
    document.getElementById('tbody-coordenadas').appendChild(tr);
    if (actualizar) { actualizarMapa(); actualizarIndicadores(); }
}

// ── Quitar fila y renumerar ───────────────────────────────────
function quitarPunto(idx) {
    const fila = document.getElementById('fila_' + idx);
    if (fila) fila.remove();
    puntos.splice(idx, 1);
    renumerarTodos();
    actualizarMapa();
    actualizarIndicadores();
}

// ── Renumerar todos los puntos después de quitar uno ─────────
function renumerarTodos() {
    const filas = document.querySelectorAll('#tbody-coordenadas tr');
    puntos.forEach((p, i) => {
        p.num = i + 1;
        const badge = document.getElementById('badge_' + i);
        const input = document.getElementById('nombre_' + i);
        if (badge) badge.textContent = etiquetaPunto(i + 1);
        if (input) input.value = etiquetaPunto(i + 1);
    });
}

// ── Actualizar valor de punto ─────────────────────────────────
function actualizarPunto(idx, campo, valor) {
    if (!puntos[idx]) return;

    // Limpiar y validar
    const num = parsearCoordenada(valor, campo);
    const errEl = document.getElementById('err_' + campo + '_' + idx);
    const input = document.getElementById(campo + '_' + idx);

    if (valor === '') {
        puntos[idx][campo] = '';
        if (input) input.className = 'coord-input';
        if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
    } else if (num === null) {
        puntos[idx][campo] = '';
        if (input) input.className = 'coord-input invalido';
        if (errEl) { errEl.style.display = 'block'; errEl.textContent = 'Valor inválido — solo números decimales'; }
    } else {
        // Corrección automática: longitud México debe ser negativa
        let val = num;
        if (campo === 'lng' && val > 0 && val <= 120) {
            val = -val;
            if (input) input.value = val;
        }
        puntos[idx][campo] = val;
        if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }
        validarRango(idx, campo, val);
    }

    verificarDuplicados();
    actualizarMapa();
    actualizarIndicadores();
}

// ── Parsear coordenada flexible ───────────────────────────────
function parsearCoordenada(str, campo) {
    if (typeof str !== 'string') str = String(str);
    // Quitar espacios, comas al final
    str = str.trim().replace(/,+$/, '');
    // Solo permitir dígitos, punto, signo negativo
    if (!/^-?\d*\.?\d*$/.test(str)) return null;
    const n = parseFloat(str);
    if (isNaN(n)) return null;
    return n;
}

// ── Validar rango geográfico México ──────────────────────────
function validarRango(idx, campo, val) {
    const input = document.getElementById(campo + '_' + idx);
    const errEl = document.getElementById('err_' + campo + '_' + idx);
    let ok = true;
    let msg = '';

    if (campo === 'lat' && (val < 14 || val > 33)) {
        ok = false; msg = 'Latitud fuera de México (14°–33°)';
    }
    if (campo === 'lng' && (val < -120 || val > -85)) {
        ok = false; msg = 'Longitud fuera de México (-85° a -120°)';
    }

    if (input) input.className = 'coord-input ' + (ok ? 'valido' : 'invalido');
    if (errEl) { errEl.style.display = ok ? 'none' : 'block'; errEl.textContent = msg; }
}

// ── Detectar duplicados ───────────────────────────────────────
function verificarDuplicados() {
    const claves = {};
    let hayDup = false;

    puntos.forEach((p, i) => {
        if (p.lat === '' || p.lng === '') return;
        const clave = `${p.lat},${p.lng}`;
        const latEl = document.getElementById('lat_' + i);
        const lngEl = document.getElementById('lng_' + i);

        if (claves[clave] !== undefined) {
            hayDup = true;
            if (latEl) latEl.classList.add('duplicado');
            if (lngEl) lngEl.classList.add('duplicado');
            // Marcar el primero también
            const primerIdx = claves[clave];
            const latP = document.getElementById('lat_' + primerIdx);
            const lngP = document.getElementById('lng_' + primerIdx);
            if (latP) latP.classList.add('duplicado');
            if (lngP) lngP.classList.add('duplicado');
        } else {
            claves[clave] = i;
            if (latEl && latEl.classList.contains('duplicado')) latEl.classList.remove('duplicado');
            if (lngEl && lngEl.classList.contains('duplicado')) lngEl.classList.remove('duplicado');
        }
    });

    const msgDup = document.getElementById('msg-duplicados');
    if (msgDup) msgDup.style.display = hayDup ? 'inline' : 'none';
}

// ── Puntos válidos para el mapa ───────────────────────────────
function puntosValidos() {
    return puntos
        .filter(p => p.lat !== '' && p.lng !== '' &&
                     !isNaN(p.lat) && !isNaN(p.lng) &&
                     p.lat >= 14 && p.lat <= 33 &&
                     p.lng >= -120 && p.lng <= -85)
        .map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
}

// ── Actualizar mini mapa ──────────────────────────────────────
function actualizarMapa() {
    const pts = puntosValidos();
    const hint = document.getElementById('preview-hint');

    if (pts.length < 2) {
        if (hint) hint.style.display = 'block';
        if (polyPreview && mapaPreview) { mapaPreview.removeLayer(polyPreview); polyPreview = null; }
        markersPreview.forEach(m => mapaPreview?.removeLayer(m));
        markersPreview = [];
        return;
    }

    if (hint) hint.style.display = 'none';

    if (!mapaIniciado) {
        mapaPreview = L.map('mapa-preview-inner', {
            zoomControl: false, attributionControl: false,
            dragging: true, scrollWheelZoom: true,
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:20 }).addTo(mapaPreview);
        mapaIniciado = true;
    }

    markersPreview.forEach(m => mapaPreview.removeLayer(m));
    markersPreview = [];
    if (polyPreview) { mapaPreview.removeLayer(polyPreview); polyPreview = null; }

    if (pts.length >= 3) {
        polyPreview = L.polygon(pts, { color:'#1a4c8b', weight:2, fillColor:'#2563eb', fillOpacity:.22 }).addTo(mapaPreview);
        mapaPreview.fitBounds(polyPreview.getBounds(), { padding:[18,18] });
    } else {
        const linea = L.polyline(pts, { color:'#1a4c8b', weight:2 }).addTo(mapaPreview);
        mapaPreview.fitBounds(linea.getBounds(), { padding:[30,30] });
    }

    // Markers numerados P1, P2...
    let pNum = 0;
    puntos.forEach((p, i) => {
        if (p.lat === '' || p.lng === '') return;
        const lat = parseFloat(p.lat), lng = parseFloat(p.lng);
        if (isNaN(lat) || isNaN(lng) || lat < 14 || lat > 33 || lng < -120 || lng > -85) return;
        pNum++;
        const icon = L.divIcon({
            className:'',
            html:`<div style="background:#1a4c8b;color:#fff;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)">${etiquetaPunto(pNum)}</div>`,
            iconAnchor:[11,11],
        });
        markersPreview.push(L.marker([lat,lng],{icon}).addTo(mapaPreview));
    });
}

// ── Indicadores de estado ─────────────────────────────────────
function actualizarIndicadores() {
    const pts = puntosValidos();
    const badge = document.getElementById('badge-puntos');
    const count = document.getElementById('count-puntos');
    const indDiv = document.getElementById('indicadores');

    if (count) count.textContent = pts.length;

    if (badge) {
        if (pts.length >= 3) { badge.className = 'badge-puntos-ok'; badge.textContent = '✓ Polígono listo'; }
        else { badge.className = 'badge-puntos-min'; badge.textContent = `Faltan ${3 - pts.length} punto(s)`; }
    }

    if (indDiv) {
        indDiv.innerHTML = '';
        puntos.forEach((p, i) => {
            const dot = document.createElement('span');
            const lat = parseFloat(p.lat), lng = parseFloat(p.lng);
            const esValido = !isNaN(lat) && !isNaN(lng) && lat>=14 && lat<=33 && lng>=-120 && lng<=-85;
            const esVacio  = p.lat === '' && p.lng === '';
            dot.className = 'punto-ind ' + (esVacio ? '' : esValido ? 'ok' : 'err');
            dot.title = etiquetaPunto(i+1);
            indDiv.appendChild(dot);
        });
    }
}

// ══════════════════════════════════════════════════════════════
//  CARGA DESDE ARCHIVO TXT / CSV
// ══════════════════════════════════════════════════════════════

function manejarDrop(event) {
    event.preventDefault();
    document.getElementById('zona-archivo').classList.remove('dragover');
    const file = event.dataTransfer.files[0];
    if (file) procesarArchivo(file);
}

function cargarArchivo(input) {
    const file = input.files[0];
    if (file) procesarArchivo(file);
}

function procesarArchivo(file) {
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['txt','csv'].includes(ext)) {
        mostrarErroresImportacion(['Solo se aceptan archivos .TXT o .CSV']);
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const texto = e.target.result;
        importarCoordenadas(texto, file.name);
    };
    reader.readAsText(file);
}

function importarCoordenadas(texto, nombreArchivo) {
    const lineas = texto.split(/\r?\n/).filter(l => l.trim() !== '');
    const errores = [];
    const nuevasPuntos = [];

    lineas.forEach((linea, i) => {
        linea = linea.trim();

        // Saltar líneas de encabezado comunes
        if (/^(lat|lng|x|y|punto|coord|#)/i.test(linea)) return;

        // Separadores: coma, punto y coma, tab, espacio
        const partes = linea.split(/[,;\t ]+/).filter(p => p !== '');

        if (partes.length < 2) {
            errores.push(`Línea ${i+1}: "${linea}" — formato inválido (necesita lat y lng)`);
            return;
        }

        const lat = parseFloat(partes[0].replace(',', '.'));
        const lng = parseFloat(partes[1].replace(',', '.'));

        if (isNaN(lat) || isNaN(lng)) {
            errores.push(`Línea ${i+1}: "${linea}" — no son números válidos`);
            return;
        }

        // Corrección automática de longitud positiva
        let lngFinal = lng;
        if (lng > 0 && lng <= 120) lngFinal = -lng;

        // Validar rango México
        if (lat < 14 || lat > 33) {
            errores.push(`Línea ${i+1}: Latitud ${lat} fuera del rango de México (14°-33°)`);
            return;
        }
        if (lngFinal < -120 || lngFinal > -85) {
            errores.push(`Línea ${i+1}: Longitud ${lngFinal} fuera del rango de México`);
            return;
        }

        // Verificar duplicado con los ya cargados
        const dup = nuevasPuntos.find(p => p.lat === lat && p.lng === lngFinal);
        if (dup) {
            errores.push(`Línea ${i+1}: Punto duplicado (${lat}, ${lngFinal})`);
            return;
        }

        nuevasPuntos.push({ lat, lng: lngFinal });
    });

    // Mostrar errores si los hay (pero continuar con los válidos)
    if (errores.length > 0) {
        mostrarErroresImportacion(errores);
    } else {
        ocultarErroresImportacion();
    }

    if (nuevasPuntos.length === 0) {
        mostrarErroresImportacion([...errores, 'No se encontraron coordenadas válidas en el archivo.']);
        return;
    }

    // Limpiar tabla actual
    document.getElementById('tbody-coordenadas').innerHTML = '';
    puntos = [];

    // Cargar puntos nuevos
    nuevasPuntos.forEach(p => {
        const num = puntos.length + 1;
        const idx = puntos.length;
        puntos.push({ num, lat: p.lat, lng: p.lng });

        const tr = document.createElement('tr');
        tr.id = 'fila_' + idx;
        tr.innerHTML = `
            <td>
                <span class="punto-badge" id="badge_${idx}">${etiquetaPunto(num)}</span>
                <input type="hidden" name="punto[]" id="nombre_${idx}" value="${etiquetaPunto(num)}">
            </td>
            <td>
                <input type="text" inputmode="decimal" name="coordenadaX[]" id="lat_${idx}"
                       class="coord-input valido" value="${p.lat}"
                       oninput="actualizarPunto(${idx}, 'lat', this.value)"
                       ${HABILITADO ? '' : 'disabled'}>
            </td>
            <td>
                <input type="text" inputmode="decimal" name="coordenadaY[]" id="lng_${idx}"
                       class="coord-input valido" value="${p.lng}"
                       oninput="actualizarPunto(${idx}, 'lng', this.value)"
                       ${HABILITADO ? '' : 'disabled'}>
            </td>
            <td>
                ${num > 3 ? `<button type="button" class="btn-rm" onclick="quitarPunto(${idx})" title="Quitar punto">
                    <i class="fas fa-times"></i>
                </button>` : ''}
            </td>`;
        document.getElementById('tbody-coordenadas').appendChild(tr);
    });

    // Actualizar zona de archivo
    const zona = document.getElementById('zona-archivo');
    zona.innerHTML = `
        <i class="fas fa-check-circle" style="color:#16a34a"></i>
        <div class="titulo" style="color:#16a34a">${nombreArchivo}</div>
        <div class="subtitulo">${nuevasPuntos.length} coordenadas cargadas correctamente</div>
        <div class="subtitulo mt-1" style="color:#6366f1;cursor:pointer" onclick="document.getElementById('archivo-input').click()">
            Cambiar archivo
        </div>`;

    actualizarMapa();
    actualizarIndicadores();
}

function mostrarErroresImportacion(errores) {
    const div = document.getElementById('errores-importacion');
    div.style.display = 'block';
    div.innerHTML = '<strong><i class="fas fa-exclamation-triangle me-1"></i>Advertencias al importar:</strong><ul class="mb-0 mt-1">'
        + errores.map(e => `<li>${e}</li>`).join('')
        + '</ul>';
}

function ocultarErroresImportacion() {
    document.getElementById('errores-importacion').style.display = 'none';
}

// ── Validar antes de enviar ───────────────────────────────────
document.getElementById('form-parcela')?.addEventListener('submit', function(e) {
    const pts = puntosValidos();
    if (pts.length > 0 && pts.length < 3) {
        e.preventDefault();
        alert('Si capturas coordenadas, necesitas mínimo 3 puntos válidos para el polígono.');
        return;
    }
    // Verificar duplicados
    const claves = new Set();
    for (const p of pts) {
        const c = `${p[0]},${p[1]}`;
        if (claves.has(c)) {
            e.preventDefault();
            alert('Hay coordenadas duplicadas. Revisa los puntos marcados en amarillo.');
            return;
        }
        claves.add(c);
    }
});

// ── Iniciar ───────────────────────────────────────────────────
init();
</script>

</body>
</html>