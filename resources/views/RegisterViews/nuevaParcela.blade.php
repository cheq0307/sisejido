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
        /* ── Sección coordenadas profesional ── */
        .coord-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .coord-table thead th {
            background: #f1f5f9;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #64748b;
            padding: 8px 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        .coord-table tbody tr { transition: background .12s; }
        .coord-table tbody tr:hover { background: #f8fafc; }
        .coord-table td { padding: 5px 6px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

        .punto-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 28px; height: 28px; border-radius: 50%;
            background: #1a4c8b; color: #fff;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
        }
        .coord-input {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 13px;
            font-family: 'Courier New', monospace;
            width: 100%;
            transition: border-color .15s, box-shadow .15s;
        }
        .coord-input:focus {
            outline: none;
            border-color: #1a4c8b;
            box-shadow: 0 0 0 3px rgba(26,76,139,.12);
        }
        .coord-input.valido   { border-color: #16a34a; background: #f0fdf4; }
        .coord-input.invalido { border-color: #dc2626; background: #fef2f2; }
        .coord-input:disabled { background: #f8fafc; color: #94a3b8; }

        .btn-add-punto {
            border: 2px dashed #cbd5e1;
            background: transparent;
            color: #64748b;
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 13px;
            cursor: pointer;
            transition: all .15s;
            width: 100%;
        }
        .btn-add-punto:hover { border-color: #1a4c8b; color: #1a4c8b; background: #f0f4fb; }

        .btn-rm { 
            background: none; border: none; color: #cbd5e1; 
            cursor: pointer; font-size: 14px; padding: 4px 6px;
            border-radius: 4px; transition: color .12s;
        }
        .btn-rm:hover { color: #dc2626; }

        /* Mini mapa preview */
        #mapa-preview {
            height: 280px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            overflow: hidden;
            position: relative;
        }
        #mapa-preview-inner { width: 100%; height: 100%; }
        #preview-hint {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            text-align: center; color: #94a3b8;
            font-size: 13px; pointer-events: none;
            z-index: 1000;
        }
        #preview-hint i { font-size: 28px; display: block; margin-bottom: 6px; }

        /* Indicador de puntos */
        .puntos-counter {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; color: #64748b;
        }
        .puntos-counter .count {
            font-weight: 700; color: #1a4c8b; font-size: 15px;
        }
        .punto-valido-indicator {
            width: 8px; height: 8px; border-radius: 50%;
            background: #e2e8f0; display: inline-block;
            transition: background .2s;
        }
        .punto-valido-indicator.ok { background: #16a34a; }

        /* Ayuda coordenadas */
        .coord-ayuda {
            background: #f0f7ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12px;
            color: #1e40af;
            margin-bottom: 12px;
        }
        .coord-ayuda code {
            background: #dbeafe;
            border-radius: 3px;
            padding: 1px 5px;
            font-size: 11px;
        }

        /* Botón ejidal */
        .btn-ejidal { background: #2d6a2d; border-color: #2d6a2d; color: #fff; }
        .btn-ejidal:hover { background: #1e4d1e; border-color: #1e4d1e; color: #fff; }
        .card-ejidal { border: 1px solid #dee2e6; }
        .card-header-ejidal { background: #2d6a2d; color: #fff; font-weight: 600; }
        .text-ejidal { color: #2d6a2d; }

        /* Punto mínimo badge */
        .badge-puntos-min {
            font-size: 10px; padding: 2px 7px;
            background: #fef3c7; color: #92400e;
            border: 1px solid #fde68a; border-radius: 20px;
        }
        .badge-puntos-ok {
            font-size: 10px; padding: 2px 7px;
            background: #d1fae5; color: #065f46;
            border: 1px solid #a7f3d0; border-radius: 20px;
        }
    </style>
</head>
<body>

@include('IncludeViews.cabeza')

<div class="container-fluid">
<div class="row">

@include('IncludeViews.menu')

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 text-ejidal">Nueva Parcela</h1>
    <a href="{{ route('parcelas.index') }}" class="btn btn-ejidal">
        <i class="fas fa-list me-1"></i> Ir al listado
    </a>
</div>

{{-- Alertas --}}
@if($error)
<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</div>
@endif
@if(session('status') === 'success')
<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Información guardada correctamente.</div>
@endif

{{-- ── BUSCADOR PARCELA ── --}}
<form method="GET" action="{{ route('parcelas.ver') }}" class="mb-4">
    <div class="input-group">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="number" class="form-control" name="noParcela"
               placeholder="Buscar parcela por número..." value="{{ request('noParcela') }}" required>
        <button class="btn btn-ejidal">Buscar</button>
    </div>
</form>

{{-- ── BUSCADOR EJIDATARIO ── --}}
<div class="card card-ejidal mb-3">
    <div class="card-header card-header-ejidal">
        <i class="fas fa-user-tie me-2"></i>Buscar Ejidatario por Número
    </div>
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
        <div class="alert alert-success mt-3 mb-0">
            <i class="fas fa-check-circle me-2"></i>
            <strong>{{ $ejidatario->nombre }} {{ $ejidatario->apellidoPaterno }} {{ $ejidatario->apellidoMaterno }}</strong>
            <span class="text-muted ms-2">— Ejidatario #{{ $ejidatario->numeroEjidatario }}</span>
        </div>
        @endif
    </div>
</div>

{{-- ── FORMULARIO PRINCIPAL ── --}}
<form method="POST" action="{{ route('parcelas.store') }}" id="form-parcela">
@csrf
<input type="hidden" name="numeroEjidatario" value="{{ $ejidatario->numeroEjidatario ?? '' }}">
<input type="hidden" name="idEjidatario"     value="{{ $ejidatario->idEjidatario ?? '' }}">

@if(!$ejidatario)
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Debes buscar un ejidatario válido antes de guardar.
</div>
@endif

{{-- ── DATOS DE PARCELA ── --}}
<div class="card card-ejidal mb-3">
    <div class="card-header card-header-ejidal">
        <i class="fas fa-map-marker-alt me-2"></i>Datos de la Parcela
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">No. Parcela <span class="text-danger">*</span></label>
                <input type="number" name="noParcela" class="form-control" {{ $ejidatario ? '' : 'disabled' }}>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Superficie (ha)</label>
                <input type="text" name="superficie" class="form-control"
                       placeholder="ej: 3.5" {{ $ejidatario ? '' : 'disabled' }}>
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
                <input type="text" name="ubicacion" class="form-control"
                       placeholder="ej: Zona norte" {{ $ejidatario ? '' : 'disabled' }}>
            </div>
        </div>
    </div>
</div>

{{-- ── COLINDANCIAS ── --}}
<div class="card card-ejidal mb-3">
    <div class="card-header card-header-ejidal">
        <i class="fas fa-compass me-2"></i>Colindancias
    </div>
    <div class="card-body">
        <div class="row g-2">
            @foreach(['norte','sur','este','oeste','noreste','noroeste','sureste','suroeste'] as $c)
            <div class="col-md-3">
                <label class="form-label fw-semibold" style="font-size:13px">{{ ucfirst($c) }}</label>
                <input type="text" name="{{ $c }}" class="form-control form-control-sm"
                       {{ $ejidatario ? '' : 'disabled' }}>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── COORDENADAS (sección profesional) ── --}}
<div class="card card-ejidal mb-3">
    <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
        <span><i class="fas fa-map-pin me-2"></i>Coordenadas GPS del Polígono</span>
        <span id="badge-puntos" class="badge-puntos-min">Mín. 3 puntos para dibujar</span>
    </div>
    <div class="card-body">

        {{-- Ayuda --}}
        <div class="coord-ayuda">
            <i class="fas fa-info-circle me-2"></i>
            Ingresa las coordenadas en formato <strong>decimal</strong> (el que da tu celular o GPS).
            Latitud: <code>19.2933</code> &nbsp;|&nbsp; Longitud: <code>-98.5622</code> (negativo para México).
            Mínimo 3 puntos para generar el polígono en el mapa.
        </div>

        <div class="row g-3">
            {{-- Tabla de puntos --}}
            <div class="col-lg-7">
                <table class="coord-table">
                    <thead>
                        <tr>
                            <th style="width:50px">Punto</th>
                            <th>Latitud (X)</th>
                            <th>Longitud (Y)</th>
                            <th style="width:36px"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-coordenadas">
                        {{-- Filas generadas por JS --}}
                    </tbody>
                </table>

                <button type="button" class="btn-add-punto mt-2" onclick="agregarPunto()"
                        {{ $ejidatario ? '' : 'disabled' }}>
                    <i class="fas fa-plus me-2"></i>Agregar punto
                </button>

                {{-- Indicadores de puntos válidos --}}
                <div class="puntos-counter mt-3">
                    <span>Puntos capturados:</span>
                    <span class="count" id="count-puntos">0</span>
                    <div id="indicadores-puntos" class="d-flex gap-1 ms-1"></div>
                </div>
            </div>

            {{-- Mini mapa preview --}}
            <div class="col-lg-5">
                <div id="mapa-preview">
                    <div id="mapa-preview-inner"></div>
                    <div id="preview-hint">
                        <i class="fas fa-draw-polygon"></i>
                        Ingresa coordenadas<br>para previsualizar
                    </div>
                </div>
                <div class="text-center mt-2" style="font-size:11px;color:#94a3b8">
                    <i class="fas fa-eye me-1"></i>Vista previa del polígono
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── INFORMACIÓN ADMINISTRATIVA ── --}}
<div class="card card-ejidal mb-3">
    <div class="card-header card-header-ejidal">
        <i class="fas fa-file-alt me-2"></i>Información Administrativa
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Número de inscripción RAN</label>
                <input type="text" name="num_inscripcionRAN" class="form-control"
                       {{ $ejidatario ? '' : 'disabled' }}>
            </div>
            <div class="col-md-7">
                <label class="form-label fw-semibold">Clave núcleo agrario</label>
                <input type="text" name="claveNucleoAgrario" class="form-control"
                       {{ $ejidatario ? '' : 'disabled' }}>
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
                <input type="date" name="fechaExpedicion" class="form-control"
                       {{ $ejidatario ? '' : 'disabled' }}>
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

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// ── Estado ──────────────────────────────────────────────────
const HABILITADO = {{ $ejidatario ? 'true' : 'false' }};
let puntos = [];       // [{ letra, lat, lng }]
let mapaIniciado = false;
let mapaPreview  = null;
let polyPreview  = null;
let markersPreview = [];
let letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

// ── Inicializar con puntos A-G vacíos ───────────────────────
function init() {
    for (let i = 0; i < 7; i++) agregarPunto();
}

// ── Agregar fila de punto ────────────────────────────────────
function agregarPunto() {
    if (!HABILITADO) return;
    const idx    = puntos.length;
    const letra  = letras[idx] || (idx + 1).toString();
    const id     = 'punto_' + idx;

    puntos.push({ letra, lat: '', lng: '' });

    const tr = document.createElement('tr');
    tr.id = 'fila_' + idx;
    tr.innerHTML = `
        <td>
            <span class="punto-badge">${letra}</span>
            <input type="hidden" name="punto[]" value="${letra}">
        </td>
        <td>
            <input type="number" step="0.0000001" min="14" max="33"
                   name="coordenadaX[]"
                   class="coord-input" id="lat_${idx}"
                   placeholder="19.2933..."
                   oninput="actualizarPunto(${idx}, 'lat', this.value)"
                   ${HABILITADO ? '' : 'disabled'}>
        </td>
        <td>
            <input type="number" step="0.0000001" min="-120" max="-85"
                   name="coordenadaY[]"
                   class="coord-input" id="lng_${idx}"
                   placeholder="-98.5622..."
                   oninput="actualizarPunto(${idx}, 'lng', this.value)"
                   ${HABILITADO ? '' : 'disabled'}>
        </td>
        <td>
            ${idx >= 3 ? `<button type="button" class="btn-rm" onclick="quitarPunto(${idx})" title="Quitar punto">
                <i class="fas fa-times"></i>
            </button>` : ''}
        </td>`;
    document.getElementById('tbody-coordenadas').appendChild(tr);
    actualizarIndicadores();
}

// ── Quitar fila ──────────────────────────────────────────────
function quitarPunto(idx) {
    const fila = document.getElementById('fila_' + idx);
    if (fila) fila.remove();
    puntos[idx] = { letra: puntos[idx].letra, lat: '', lng: '' };
    actualizarMapa();
    actualizarIndicadores();
}

// ── Actualizar dato de punto ─────────────────────────────────
function actualizarPunto(idx, campo, valor) {
    if (!puntos[idx]) return;
    puntos[idx][campo] = valor;

    const inputLat = document.getElementById('lat_' + idx);
    const inputLng = document.getElementById('lng_' + idx);
    if (!inputLat || !inputLng) return;

    const lat = parseFloat(puntos[idx].lat);
    const lng = parseFloat(puntos[idx].lng);
    const latOk = !isNaN(lat) && lat >= 14 && lat <= 33;
    const lngOk = !isNaN(lng) && lng >= -120 && lng <= -85;

    inputLat.className = 'coord-input ' + (puntos[idx].lat === '' ? '' : latOk ? 'valido' : 'invalido');
    inputLng.className = 'coord-input ' + (puntos[idx].lng === '' ? '' : lngOk ? 'valido' : 'invalido');

    actualizarMapa();
    actualizarIndicadores();
}

// ── Obtener puntos válidos ───────────────────────────────────
function puntosValidos() {
    return puntos.filter(p => {
        const lat = parseFloat(p.lat);
        const lng = parseFloat(p.lng);
        return !isNaN(lat) && !isNaN(lng) && lat >= 14 && lat <= 33 && lng >= -120 && lng <= -85;
    }).map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
}

// ── Actualizar mini mapa ─────────────────────────────────────
function actualizarMapa() {
    const pts = puntosValidos();
    const hint = document.getElementById('preview-hint');

    if (pts.length < 2) {
        hint.style.display = 'block';
        if (polyPreview) { mapaPreview?.removeLayer(polyPreview); polyPreview = null; }
        markersPreview.forEach(m => mapaPreview?.removeLayer(m));
        markersPreview = [];
        return;
    }

    hint.style.display = 'none';

    // Inicializar mapa si aún no existe
    if (!mapaIniciado) {
        mapaPreview = L.map('mapa-preview-inner', {
            zoomControl: false,
            attributionControl: false,
            dragging: true,
            scrollWheelZoom: true,
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:20 }).addTo(mapaPreview);
        mapaIniciado = true;
    }

    // Limpiar markers anteriores
    markersPreview.forEach(m => mapaPreview.removeLayer(m));
    markersPreview = [];
    if (polyPreview) { mapaPreview.removeLayer(polyPreview); polyPreview = null; }

    // Dibujar polígono si hay 3+ puntos
    if (pts.length >= 3) {
        polyPreview = L.polygon(pts, {
            color: '#1a4c8b', weight: 2,
            fillColor: '#2563eb', fillOpacity: .25,
        }).addTo(mapaPreview);
        mapaPreview.fitBounds(polyPreview.getBounds(), { padding: [20, 20] });
    } else {
        // Solo línea con 2 puntos
        L.polyline(pts, { color: '#1a4c8b', weight: 2 }).addTo(mapaPreview);
        mapaPreview.fitBounds(L.polyline(pts).getBounds(), { padding: [30, 30] });
    }

    // Markers con letra
    pts.forEach((pt, i) => {
        const letra = puntos.filter(p => {
            const lat = parseFloat(p.lat), lng = parseFloat(p.lng);
            return !isNaN(lat) && !isNaN(lng);
        })[i]?.letra || String.fromCharCode(65 + i);

        const icon = L.divIcon({
            className: '',
            html: `<div style="background:#1a4c8b;color:#fff;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)">${letra}</div>`,
            iconAnchor: [11, 11],
        });
        markersPreview.push(L.marker(pt, { icon }).addTo(mapaPreview));
    });
}

// ── Indicadores de puntos ────────────────────────────────────
function actualizarIndicadores() {
    const pts = puntosValidos();
    const count = document.getElementById('count-puntos');
    const badge = document.getElementById('badge-puntos');
    const indicadores = document.getElementById('indicadores-puntos');

    count.textContent = pts.length;

    // Badge
    if (pts.length >= 3) {
        badge.className = 'badge-puntos-ok';
        badge.textContent = '✓ Polígono listo';
    } else {
        badge.className = 'badge-puntos-min';
        badge.textContent = `Mín. 3 puntos (faltan ${3 - pts.length})`;
    }

    // Indicadores visuales
    const total = puntos.filter(p => {
        const fila = document.getElementById('fila_' + puntos.indexOf(p));
        return fila !== null;
    }).length;

    indicadores.innerHTML = '';
    for (let i = 0; i < Math.min(total, 10); i++) {
        const dot = document.createElement('span');
        dot.className = 'punto-valido-indicator' + (i < pts.length ? ' ok' : '');
        indicadores.appendChild(dot);
    }
}

// ── Validar antes de enviar ──────────────────────────────────
document.getElementById('form-parcela')?.addEventListener('submit', function(e) {
    const pts = puntosValidos();
    if (pts.length > 0 && pts.length < 3) {
        e.preventDefault();
        alert('Si capturas coordenadas, necesitas mínimo 3 puntos válidos para formar un polígono.');
        return;
    }
});

// ── Iniciar ──────────────────────────────────────────────────
init();
</script>

</body>
</html>