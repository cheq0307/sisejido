@include('IncludeViews.cabeza')

<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


<style>
    /* ── Layout mapa dentro del col-md-9 ── */
    #mapa-wrapper {
        display: flex;
        height: calc(100vh - 56px);
        overflow: hidden;
    }

    /* ── Panel lateral del mapa ── */
    #panel-mapa {
        width: 220px;
        min-width: 220px;
        height: 100%;
        background: #fff;
        border-right: 1px solid #dee2e6;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    #panel-header {
        background: #1a4c8b;
        color: #fff;
        padding: 10px 13px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    #panel-body { flex: 1; overflow-y: auto; padding: 10px 12px; }
    .panel-titulo { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#6c757d; margin-bottom:6px; border-bottom:1px solid #f0f0f0; padding-bottom:3px; }
    .panel-seccion { margin-bottom: 12px; }
    .stat-grid { display:grid; grid-template-columns:1fr 1fr; gap:6px; }
    .stat-card { background:#f8f9fa; border-radius:6px; padding:7px 9px; text-align:center; }
    .stat-num { font-size:18px; font-weight:700; color:#1a4c8b; line-height:1; }
    .stat-lbl { font-size:10px; color:#6c757d; margin-top:2px; }
    .leyenda-item { display:flex; align-items:center; gap:7px; font-size:11px; color:#333; margin-bottom:4px; }
    .leyenda-color { width:14px; height:9px; border-radius:3px; border:1px solid rgba(0,0,0,.18); flex-shrink:0; }
    .parcela-item { display:flex; align-items:center; gap:6px; padding:4px 4px; border-radius:5px; cursor:pointer; font-size:11px; border-bottom:1px solid #f5f5f5; transition:background .12s; }
    .parcela-item:hover { background:#f0f4fc; }
    .parcela-item.activa { background:#dde8f8; }
    .parcela-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
    .parcela-folio { font-weight:600; color:#1a4c8b; font-size:10px; }
    .parcela-nombre { color:#555; font-size:9px; }

    /* ── Área del mapa ── */
    #mapa-area { flex:1; position:relative; overflow:hidden; min-height:0; display:flex; flex-direction:column; }
    #mapa { width:100%; height:100%; min-height:400px; flex:1; }

    /* Tabs capas */
    #tabs-capa { position:absolute; top:10px; left:50%; transform:translateX(-50%); z-index:1000; background:#fff; border:1px solid #ccc; border-radius:6px; display:flex; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    .tab-capa { padding:5px 14px; font-size:12px; font-weight:500; cursor:pointer; border-right:1px solid #ddd; color:#444; background:#fff; user-select:none; transition:background .12s; }
    .tab-capa:last-child { border-right:none; }
    .tab-capa.activo { background:#1a4c8b; color:#fff; }
    .tab-capa:hover:not(.activo) { background:#eef3fb; }

    /* Info parcela */
    #info-parcela { position:absolute; bottom:24px; right:10px; z-index:1000; width:250px; background:#fff; border:1px solid #dee2e6; border-radius:8px; box-shadow:0 3px 14px rgba(0,0,0,.15); display:none; font-size:12px; }
    #info-header { background:#1a4c8b; color:#fff; padding:7px 12px; border-radius:8px 8px 0 0; font-weight:600; display:flex; justify-content:space-between; align-items:center; }
    #info-body { padding:9px 12px; }
    .info-fila { display:flex; margin-bottom:4px; }
    .info-etiqueta { width:90px; font-weight:600; color:#777; flex-shrink:0; font-size:11px; }
    .info-valor { font-size:11px; color:#111; }

    /* Coordenadas */
    #coords { position:absolute; bottom:4px; left:50%; transform:translateX(-50%); z-index:900; background:rgba(255,255,255,.88); border:1px solid #ddd; border-radius:4px; padding:2px 10px; font-size:11px; color:#444; pointer-events:none; }

    .btn-ejidal { background:#1a4c8b; border-color:#1a4c8b; color:#fff; }
    .btn-ejidal:hover { background:#153d70; border-color:#153d70; color:#fff; }
    .navbar-ejidal { background:#2d6a2d !important; }
</style>

<div class="col-md-9 ms-sm-auto col-lg-10 px-0" style="height:calc(100vh - 56px);overflow:hidden;display:flex;position:relative;">

    {{-- Panel lateral del mapa --}}
    <div id="panel-mapa">
        <div id="panel-header">
            <i class="fas fa-map-marked-alt"></i> Mapa Catastral
        </div>
        <div id="panel-body">

            <div class="panel-seccion">
                <div class="panel-titulo">Resumen</div>
                <div class="stat-grid">
                    <div class="stat-card"><div class="stat-num">{{ $stats['total'] }}</div><div class="stat-lbl">Parcelas</div></div>
                    <div class="stat-card"><div class="stat-num" style="color:#198754">{{ $stats['con_poligono'] }}</div><div class="stat-lbl">Con polígono</div></div>
                    <div class="stat-card"><div class="stat-num" style="color:#dc3545">{{ $stats['sin_poligono'] }}</div><div class="stat-lbl">Sin dibujar</div></div>
                    <div class="stat-card"><div class="stat-num" style="color:#fd7e14">{{ $stats['en_litigio'] }}</div><div class="stat-lbl">En litigio</div></div>
                </div>
            </div>

            <div class="panel-seccion">
                <div class="panel-titulo">Capas</div>
                <label class="d-flex align-items-center gap-2 mb-1" style="font-size:12px;cursor:pointer">
                    <input type="checkbox" id="chk-parcelas" checked> Parcelas
                </label>
                <label class="d-flex align-items-center gap-2 mb-1" style="font-size:12px;cursor:pointer">
                    <input type="checkbox" id="chk-etiquetas"> Etiquetas
                </label>
                <label class="d-flex align-items-center gap-2 mb-1" style="font-size:12px;cursor:pointer">
                    <input type="checkbox" id="chk-perimetro" checked> Perímetro ejidal
                </label>
            </div>

            <div class="panel-seccion">
                <div class="panel-titulo">Leyenda</div>
                <div class="leyenda-item"><div class="leyenda-color" style="background:#2563eb55;border-color:#1d4ed8"></div>Con expediente</div>
                <div class="leyenda-item"><div class="leyenda-color" style="background:#16a34a55;border-color:#15803d"></div>Certificada</div>
                <div class="leyenda-item"><div class="leyenda-color" style="background:#dc262655;border-color:#b91c1c"></div>En litigio</div>
                <div class="leyenda-item"><div class="leyenda-color" style="background:#d9770655;border-color:#b45309"></div>Sin regularizar</div>
                <div class="leyenda-item"><div style="width:14px;height:3px;border-top:2px dashed #b91c1c;flex-shrink:0"></div>Perímetro ejidal</div>
            </div>

            <div class="panel-seccion">
                <div class="panel-titulo">Parcelas ({{ $parcelas->count() }})</div>
                @foreach($parcelas as $p)
                <div class="parcela-item" data-id="{{ $p->idParcela }}" onclick="seleccionarParcela({{ $p->idParcela }})">
                    <div class="parcela-dot" style="background:{{ match($p->estado ?? '') { 'certificada' => '#16a34a', 'expediente' => '#2563eb', 'litigio' => '#dc2626', default => '#d97706' } }}"></div>
                    <div>
                        <div class="parcela-folio">P-{{ str_pad($p->noParcela, 3, '0', STR_PAD_LEFT) }}</div>
                        <div class="parcela-nombre">{{ $p->ejidatario ? $p->ejidatario->nombre . ' ' . $p->ejidatario->apellidoPaterno : 'Sin asignar' }}</div>
                    </div>
                    @if(!$p->tienePoligono())
                        <span class="ms-auto" style="font-size:9px;color:#bbb;font-style:italic">sin dibujo</span>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="panel-seccion">
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('parcelas.create') }}" class="btn btn-ejidal btn-sm">
                        <i class="fas fa-plus me-1"></i> Nueva parcela
                    </a>
                    <button class="btn btn-outline-secondary btn-sm" onclick="centrarEjido()">
                        <i class="fas fa-compress-arrows-alt me-1"></i> Centrar vista
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Área del mapa --}}
    <div id="mapa-area">
        <div id="tabs-capa">
            <div class="tab-capa activo" data-capa="osm">OSM</div>
            <div class="tab-capa" data-capa="sat_google">Satélite</div>
            <div class="tab-capa" data-capa="sat_esri">ESRI</div>
        </div>

        <div id="mapa"></div>

        <div id="info-parcela">
            <div id="info-header">
                <span id="info-titulo">Parcela</span>
                <span style="cursor:pointer" onclick="cerrarInfo()">✕</span>
            </div>
            <div id="info-body">
                <div class="info-fila"><span class="info-etiqueta">Folio:</span><span class="info-valor" id="inf-folio">—</span></div>
                <div class="info-fila"><span class="info-etiqueta">Ejidatario:</span><span class="info-valor" id="inf-ejidatario">—</span></div>
                <div class="info-fila"><span class="info-etiqueta">Superficie:</span><span class="info-valor" id="inf-superficie">—</span></div>
                <div class="info-fila"><span class="info-etiqueta">Uso suelo:</span><span class="info-valor" id="inf-uso">—</span></div>
                <div class="info-fila"><span class="info-etiqueta">Estado:</span><span class="info-valor" id="inf-estado">—</span></div>
            </div>
        </div>

        <div id="coords">Lat: — | Lng: —</div>
    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
<script>
const GEOJSON     = @json($geojson);
const PARCELAS_BD = @json($parcelasJs);
const CSRF        = document.querySelector('meta[name="csrf-token"]').content;

const GEOJSON     = @json($geojson);
const PARCELAS_BD = @json($parcelasJs);
const CSRF        = document.querySelector('meta[name="csrf-token"]').content;

const CAPAS = {
    osm:        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:20, attribution:'© OpenStreetMap' }),
    sat_google: L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',  { maxZoom:20, attribution:'© Google' }),
    sat_esri:   L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom:19, attribution:'© ESRI' }),
};

const ESTILOS = {
    certificada:    { color:'#15803d', fillColor:'#16a34a', fillOpacity:.35 },
    expediente:     { color:'#1d4ed8', fillColor:'#2563eb', fillOpacity:.35 },
    litigio:        { color:'#b91c1c', fillColor:'#dc2626', fillOpacity:.35 },
    sin_regularizar:{ color:'#b45309', fillColor:'#d97706', fillOpacity:.35 },
};

const map = L.map('mapa', {
    center: [19.2933, -98.5620],
    zoom: 16,
    zoomControl: false,
    zoomSnap: 0.5,
    zoomDelta: 0.5,
    wheelPxPerZoomLevel: 60,
    maxZoom: 19,
    layers: [CAPAS.osm],
});
L.control.zoom({ position: 'bottomleft' }).addTo(map);
L.control.scale({ metric: true, imperial: false, position: 'bottomleft' }).addTo(map);

// ══════════════════════════════════════════════════════════════
//  PERÍMETRO DEL EJIDO — San Rafael Ixtapalucan
//  Trazado sobre imagen Google Maps (contorno línea roja)
//  Zona: Santa Rita Tlahuapan, Puebla
// ══════════════════════════════════════════════════════════════
const PERIMETRO_EJIDO = [
    // Trazado sobre imagen Google Maps oficial — San Rafael Ixtapalucan
    // Esquina norte-este (arriba derecha)
    [19.3058, -98.5482],
    [19.3055, -98.5468],
    [19.3048, -98.5455],
    // Borde este (derecha) bajando
    [19.3020, -98.5448],
    [19.2998, -98.5450],
    [19.2975, -98.5455],
    [19.2958, -98.5462],
    // Esquina sur-este
    [19.2932, -98.5468],
    [19.2910, -98.5480],
    // Borde sur bajando
    [19.2890, -98.5510],
    [19.2878, -98.5538],
    [19.2868, -98.5562],
    [19.2862, -98.5588],
    // Esquina sur-oeste
    [19.2860, -98.5618],
    [19.2865, -98.5648],
    [19.2872, -98.5672],
    // Borde sur-oeste subiendo
    [19.2885, -98.5695],
    [19.2902, -98.5710],
    // Borde oeste (izquierda) subiendo
    [19.2928, -98.5725],
    [19.2950, -98.5730],
    [19.2968, -98.5728],
    // Borde norte-oeste
    [19.2988, -98.5720],
    [19.3005, -98.5705],
    [19.3022, -98.5688],
    [19.3038, -98.5665],
    [19.3048, -98.5640],
    // Borde norte
    [19.3058, -98.5610],
    [19.3062, -98.5578],
    [19.3062, -98.5545],
    [19.3060, -98.5512],
    [19.3058, -98.5482],
];

const layerPerimetro = L.polygon(PERIMETRO_EJIDO, {
    color: '#b91c1c',
    weight: 2.5,
    dashArray: '8 5',
    fill: false,
    opacity: 0.85,
}).addTo(map);

layerPerimetro.bindTooltip('Ejido San Rafael Ixtapalucan', {
    permanent: false,
    direction: 'center',
    className: 'leaflet-tooltip',
});



let capaActual     = 'osm';
let layerPoligonos = L.layerGroup().addTo(map);
let layerEtiquetas = L.layerGroup();
let polyMap        = {};
let parcelaActual  = null;
let polyActual     = null;

function renderizar() {
    layerPoligonos.clearLayers(); layerEtiquetas.clearLayers(); polyMap = {};
    if (!GEOJSON.features?.length) return;
    L.geoJSON(GEOJSON, {
        style: f => ({ ...(ESTILOS[f.properties.estado] || ESTILOS.sin_regularizar), weight:1.8 }),
        onEachFeature: (f, layer) => {
            const p = f.properties;
            polyMap[p.id] = layer;
            layer.on('click', () => { const bd = PARCELAS_BD.find(x => x.id === p.id); if (bd) mostrarInfo(bd, layer); });
            layer.on('mouseover', function() { this.setStyle({weight:3,fillOpacity:.6}); });
            layer.on('mouseout',  function() {
                if (polyActual !== this) { const e = ESTILOS[p.estado]||ESTILOS.sin_regularizar; this.setStyle({weight:1.8,fillOpacity:e.fillOpacity}); }
            });
            layer.bindTooltip(`<strong>${p.folio}</strong><br>${p.ejidatario}`, {sticky:true});
            layerPoligonos.addLayer(layer);
            const c = layer.getBounds().getCenter();
            layerEtiquetas.addLayer(L.marker(c, {
                icon: L.divIcon({ className:'', html:`<div style="background:rgba(255,255,255,.85);border:1px solid #aaa;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:700;white-space:nowrap">${p.folio}</div>`, iconAnchor:[18,8] }),
                interactive:false,
            }));
        },
    });
}
renderizar();

const ESTADOS_LBL = { certificada:'✅ Certificada', expediente:'📄 Con expediente', litigio:'⚠️ En litigio', sin_regularizar:'❌ Sin regularizar' };

function mostrarInfo(p, layer) {
    if (polyActual && polyActual !== layer) {
        const prev = GEOJSON.features?.find(f => f.properties.id === parcelaActual?.id);
        if (prev) { const e = ESTILOS[prev.properties.estado]||ESTILOS.sin_regularizar; polyActual.setStyle({weight:1.8,fillOpacity:e.fillOpacity}); }
    }
    parcelaActual = p; polyActual = layer;
    if (layer) layer.setStyle({weight:3,fillOpacity:.65});
    document.querySelectorAll('.parcela-item').forEach(el => el.classList.remove('activa'));
    const li = document.querySelector(`.parcela-item[data-id="${p.id}"]`);
    if (li) { li.classList.add('activa'); li.scrollIntoView({block:'nearest',behavior:'smooth'}); }
    document.getElementById('info-titulo').textContent    = p.folio;
    document.getElementById('inf-folio').textContent      = p.folio;
    document.getElementById('inf-ejidatario').textContent = p.ejidatario;
    document.getElementById('inf-superficie').textContent = p.superficie ? p.superficie + ' ha' : '—';
    document.getElementById('inf-uso').textContent        = p.uso;
    document.getElementById('inf-estado').textContent     = ESTADOS_LBL[p.estado] || p.estado;

    document.getElementById('info-parcela').style.display = 'block';
}

function cerrarInfo() {
    document.getElementById('info-parcela').style.display = 'none';
    if (polyActual) {
        const prev = GEOJSON.features?.find(f => f.properties.id === parcelaActual?.id);
        if (prev) { const e = ESTILOS[prev.properties.estado]||ESTILOS.sin_regularizar; polyActual.setStyle({weight:1.8,fillOpacity:e.fillOpacity}); }
    }
    parcelaActual = null; polyActual = null;
    document.querySelectorAll('.parcela-item').forEach(el => el.classList.remove('activa'));
}

function seleccionarParcela(id) {
    const bd = PARCELAS_BD.find(p => p.id === id);
    if (!bd) return;
    const layer = polyMap[id];
    if (layer) { map.fitBounds(layer.getBounds(),{padding:[60,60]}); mostrarInfo(bd,layer); }
    else { if (bd.lat && bd.lng) map.setView([bd.lat,bd.lng],16); mostrarInfo(bd,null); }
}

let drawLayer = new L.FeatureGroup().addTo(map);
let drawControl = null;

function iniciarDibujo() {
    if (!parcelaActual) return;
    if (drawControl) { map.removeControl(drawControl); drawLayer.clearLayers(); }
    drawControl = new L.Control.Draw({
        draw: { polygon:{allowIntersection:false,showArea:true,shapeOptions:{color:'#e67e22',weight:2,fillOpacity:.25}}, polyline:false,rectangle:false,circle:false,circlemarker:false,marker:false },
        edit: { featureGroup:drawLayer, remove:false },
    });
    map.addControl(drawControl);
    document.getElementById('barra-dibujo').classList.add('visible');
    document.getElementById('info-parcela').style.display = 'none';
    setTimeout(() => document.querySelector('.leaflet-draw-draw-polygon')?.click(), 200);
}

map.on(L.Draw.Event.CREATED, async (e) => {
    drawLayer.clearLayers(); drawLayer.addLayer(e.layer);
    const vertices = e.layer.getLatLngs()[0].map(ll => [ll.lat, ll.lng]);
    await guardarVertices(vertices);
});

async function guardarVertices(vertices) {
    if (!parcelaActual) return;
    try {
        const r = await fetch(`/api/parcelas/${parcelaActual.id}/poligono`, {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify({vertices}),
        });
        const d = await r.json();
        if (d.ok) { toast('Polígono guardado ✓','success'); setTimeout(()=>location.reload(),800); }
        else toast('Error: '+(d.message||''),'danger');
    } catch(err) { toast('Error de conexión','danger'); }
    finalizarDibujo();
}

function cancelarDibujo() {
    if (drawControl) { map.removeControl(drawControl); drawControl = null; }
    drawLayer.clearLayers(); finalizarDibujo();
}
function finalizarDibujo() {
    document.getElementById('barra-dibujo').classList.remove('visible');
    if (parcelaActual) document.getElementById('info-parcela').style.display = 'block';
}

async function confirmarBorrar() {
    if (!parcelaActual || !confirm(`¿Borrar el polígono de ${parcelaActual.folio}?`)) return;
    try {
        const r = await fetch(`/api/parcelas/${parcelaActual.id}/poligono`, { method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF} });
        const d = await r.json();
        if (d.ok) { toast('Polígono eliminado','warning'); setTimeout(()=>location.reload(),700); }
    } catch(e) { toast('Error al borrar','danger'); }
}

document.querySelectorAll('.tab-capa').forEach(tab => {
    tab.addEventListener('click', () => {
        const c = tab.dataset.capa;
        if (c === capaActual) return;
        map.removeLayer(CAPAS[capaActual]); map.addLayer(CAPAS[c]); capaActual = c;
        document.querySelectorAll('.tab-capa').forEach(t => t.classList.toggle('activo', t.dataset.capa===c));
        document.querySelector(`input[name="capa"][value="${c}"]`).checked = true;
    });
});
document.querySelectorAll('input[name="capa"]').forEach(r => {
    r.addEventListener('change', function() {
        const c = this.value;
        map.removeLayer(CAPAS[capaActual]); map.addLayer(CAPAS[c]); capaActual = c;
        document.querySelectorAll('.tab-capa').forEach(t => t.classList.toggle('activo', t.dataset.capa===c));
    });
});

document.getElementById('chk-parcelas')?.addEventListener('change', function() {
    this.checked ? map.addLayer(layerPoligonos) : map.removeLayer(layerPoligonos);
});
document.getElementById('chk-etiquetas')?.addEventListener('change', function() {
    this.checked ? map.addLayer(layerEtiquetas) : map.removeLayer(layerEtiquetas);
});
document.getElementById('chk-perimetro')?.addEventListener('change', function() {
    this.checked ? map.addLayer(layerPerimetro) : map.removeLayer(layerPerimetro);
});

map.on('mousemove', e => {
    document.getElementById('coords').textContent = `Lat: ${e.latlng.lat.toFixed(6)}  |  Lng: ${e.latlng.lng.toFixed(6)}`;
});



function centrarEjido() {
    map.fitBounds(layerPerimetro.getBounds(), { padding: [30, 30] });
}

function toast(msg, tipo='success') {
    const colores = {success:'#198754',danger:'#dc3545',warning:'#fd7e14'};
    const el = document.createElement('div');
    el.style.cssText = `position:fixed;bottom:40px;right:20px;z-index:9999;background:${colores[tipo]};color:#fff;padding:9px 16px;border-radius:6px;font-size:13px;font-weight:500;box-shadow:0 3px 12px rgba(0,0,0,.2)`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

// Invalidar tamaño del mapa al cargar completamente
window.addEventListener('load', () => map.invalidateSize());
setTimeout(() => map.invalidateSize(), 200);

// Forzar render del mapa al cargar
window.addEventListener('load', () => { setTimeout(() => map.invalidateSize(), 100); });
setTimeout(() => map.invalidateSize(), 300);
setTimeout(() => map.invalidateSize(), 800);
</script>

@include('IncludeViews.pie')