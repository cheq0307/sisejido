@include('IncludeViews.cabeza')

<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .sidebar { z-index: 200 !important; }

    #mapa-wrapper {
        position: fixed;
        top: 56px;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        z-index: 10;
        background: #e8e8e8;
    }

    @media (min-width: 768px) {
        #mapa-wrapper { left: 25%; }
    }
    @media (min-width: 992px) {
        #mapa-wrapper { left: 16.666667%; }
    }

    /* Panel parcelas */
    #panel-mapa {
        width: 280px;
        min-width: 280px;
        height: 100%;
        background: #fff;
        border-right: 1px solid #dee2e6;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 2px 0 8px rgba(0,0,0,.08);
        z-index: 11;
    }
    #panel-header {
        background: #1a4c8b;
        color: #fff;
        padding: 12px 15px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 9px;
        flex-shrink: 0;
    }
    #panel-body { flex: 1; overflow-y: auto; padding: 12px 14px; }
    #panel-body::-webkit-scrollbar { width: 5px; }
    #panel-body::-webkit-scrollbar-thumb { background: #ccd; border-radius: 3px; }

    .panel-titulo {
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .7px; color: #6c757d; margin-bottom: 8px;
        border-bottom: 1px solid #f0f0f0; padding-bottom: 4px;
    }
    .panel-seccion { margin-bottom: 16px; }

    .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 7px; }
    .stat-card { background: #f8f9fa; border-radius: 8px; padding: 9px 10px; text-align: center; border: 1px solid #eee; }
    .stat-num { font-size: 22px; font-weight: 700; color: #1a4c8b; line-height: 1; }
    .stat-lbl { font-size: 11px; color: #6c757d; margin-top: 3px; }

    .leyenda-item { display:flex; align-items:center; gap:8px; font-size:12px; color:#333; margin-bottom:5px; }
    .leyenda-color { width:15px; height:10px; border-radius:3px; border:1px solid rgba(0,0,0,.18); flex-shrink:0; }

    .parcela-item {
        display: flex; align-items: center; gap: 8px;
        padding: 7px 6px; border-radius: 6px; cursor: pointer;
        font-size: 12px; border-bottom: 1px solid #f5f5f5; transition: background .12s;
    }
    .parcela-item:hover  { background: #f0f4fc; }
    .parcela-item.activa { background: #dde8f8; }
    .parcela-dot   { width:10px; height:10px; border-radius:50%; flex-shrink:0; border:1.5px solid rgba(0,0,0,.12); }
    .parcela-folio { font-weight:700; color:#1a4c8b; font-size:11px; }
    .parcela-nombre{ color:#555; font-size:10px; }
    .parcela-sin   { font-size:9px; color:#bbb; font-style:italic; white-space:nowrap; }

    /* Mapa */
    #mapa-area { flex:1; position:relative; overflow:hidden; }
    #mapa      { width:100%; height:100%; }

    /* Tabs */
    #tabs-capa {
        position:absolute; top:10px; left:50%; transform:translateX(-50%);
        z-index:1000; background:#fff; border:1px solid #ccc; border-radius:7px;
        display:flex; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.18);
    }
    .tab-capa {
        padding:6px 18px; font-size:12px; font-weight:600; cursor:pointer;
        border-right:1px solid #ddd; color:#555; background:#fff;
        user-select:none; transition:background .12s;
    }
    .tab-capa:last-child { border-right:none; }
    .tab-capa.activo { background:#1a4c8b; color:#fff; }
    .tab-capa:hover:not(.activo) { background:#eef3fb; }

    /* Info parcela — más grande */
    #info-parcela {
        position: absolute; bottom:30px; right:14px;
        z-index: 1000; width: 310px;
        background: #fff; border: 1px solid #dee2e6;
        border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,.18);
        display: none; overflow: hidden;
    }
    #info-header {
        background: #1a4c8b; color: #fff;
        padding: 10px 14px; font-weight: 700; font-size: 13px;
        display: flex; justify-content: space-between; align-items: center;
    }
    #info-header .cerrar { cursor:pointer; font-size:16px; opacity:.8; }
    #info-header .cerrar:hover { opacity:1; }
    #info-body { padding: 14px 16px; }
    .info-fila { display:flex; margin-bottom:9px; align-items:flex-start; }
    .info-etiqueta {
        width: 105px; font-weight:600; color:#888; flex-shrink:0;
        font-size:11px; text-transform:uppercase; letter-spacing:.4px; padding-top:2px;
    }
    .info-valor { font-size:13px; color:#111; font-weight:500; }
    .info-divider { border:none; border-top:1px solid #f0f0f0; margin:10px 0; }
    .info-acciones { display:flex; gap:8px; }

    /* Barra dibujo */
    #barra-dibujo {
        display:none; position:absolute; top:52px; left:50%; transform:translateX(-50%);
        z-index:1100; background:#fff; border:1px solid #dee2e6; border-radius:7px;
        padding:7px 14px; box-shadow:0 2px 10px rgba(0,0,0,.15); gap:10px; align-items:center;
    }
    #barra-dibujo.visible { display:flex; }

    /* Coords */
    #coords {
        position:absolute; bottom:6px; left:50%; transform:translateX(-50%);
        z-index:900; background:rgba(255,255,255,.9); border:1px solid #ddd;
        border-radius:4px; padding:3px 12px; font-size:11px; color:#555;
        pointer-events:none; white-space:nowrap;
    }

    .btn-ejidal       { background:#1a4c8b; border-color:#1a4c8b; color:#fff; }
    .btn-ejidal:hover { background:#153d70; border-color:#153d70; color:#fff; }
</style>

{{-- Placeholder para cerrar el .row de cabeza.blade.php --}}
<div class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="min-height:1px;pointer-events:none"></div>

<div id="mapa-wrapper">

    {{-- Panel parcelas --}}
    <div id="panel-mapa">
        <div id="panel-header">
            <i class="fas fa-map-marked-alt"></i> Mapa Catastral
        </div>
        <div id="panel-body">

            <div class="panel-seccion">
                <div class="panel-titulo">Resumen</div>
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-num">{{ $stats['total'] }}</div>
                        <div class="stat-lbl">Parcelas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-num" style="color:#198754">{{ $stats['con_poligono'] }}</div>
                        <div class="stat-lbl">Con polígono</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-num" style="color:#dc3545">{{ $stats['sin_poligono'] }}</div>
                        <div class="stat-lbl">Sin dibujar</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-num" style="color:#fd7e14">{{ $stats['en_litigio'] }}</div>
                        <div class="stat-lbl">En litigio</div>
                    </div>
                </div>
            </div>

            <div class="panel-seccion">
                <div class="panel-titulo">Capas</div>
                <label class="d-flex align-items-center gap-2 mb-2" style="font-size:13px;cursor:pointer">
                    <input type="checkbox" id="chk-parcelas" checked> Parcelas
                </label>
                <label class="d-flex align-items-center gap-2 mb-2" style="font-size:13px;cursor:pointer">
                    <input type="checkbox" id="chk-etiquetas"> Etiquetas
                </label>
                <label class="d-flex align-items-center gap-2 mb-2" style="font-size:13px;cursor:pointer">
                    <input type="checkbox" id="chk-perimetro" checked> Perímetro ejidal
                </label>
            </div>

            <div class="panel-seccion">
                <div class="panel-titulo">Leyenda</div>
                <div class="leyenda-item"><div class="leyenda-color" style="background:#2563eb55;border-color:#1d4ed8"></div>Con expediente</div>
                <div class="leyenda-item"><div class="leyenda-color" style="background:#16a34a55;border-color:#15803d"></div>Certificada</div>
                <div class="leyenda-item"><div class="leyenda-color" style="background:#dc262655;border-color:#b91c1c"></div>En litigio</div>
                <div class="leyenda-item"><div class="leyenda-color" style="background:#d9770655;border-color:#b45309"></div>Sin regularizar</div>
                <div class="leyenda-item">
                    <div style="width:15px;height:3px;border-top:2px dashed #b91c1c;flex-shrink:0"></div>
                    Perímetro ejidal
                </div>
            </div>

            <div class="panel-seccion">
                <div class="panel-titulo">Parcelas ({{ $parcelas->count() }})</div>
                @foreach($parcelas as $p)
                <div class="parcela-item" data-id="{{ $p->idParcela }}"
                     onclick="seleccionarParcela({{ $p->idParcela }})">
                    <div class="parcela-dot" style="background:{{ match($p->estado ?? '') {
                        'certificada' => '#16a34a',
                        'expediente'  => '#2563eb',
                        'litigio'     => '#dc2626',
                        default       => '#d97706'
                    } }}"></div>
                    <div style="flex:1;min-width:0">
                        <div class="parcela-folio">P-{{ str_pad($p->noParcela, 3, '0', STR_PAD_LEFT) }}</div>
                        <div class="parcela-nombre">
                            {{ $p->ejidatario
                                ? $p->ejidatario->nombre . ' ' . $p->ejidatario->apellidoPaterno
                                : 'Sin asignar' }}
                        </div>
                    </div>
                    @if(!$p->tienePoligono())
                        <span class="parcela-sin">sin dibujo</span>
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

        <div id="barra-dibujo">
            <i class="fas fa-draw-polygon" style="color:#1a4c8b"></i>
            <span style="font-size:13px;font-weight:600;color:#1a4c8b">Dibujando parcela…</span>
            <button class="btn btn-sm btn-outline-danger" onclick="cancelarDibujo()">
                <i class="fas fa-times me-1"></i>Cancelar
            </button>
        </div>

        <div id="info-parcela">
            <div id="info-header">
                <span><i class="fas fa-map-pin me-1"></i><span id="info-titulo">Parcela</span></span>
                <span class="cerrar" onclick="cerrarInfo()">✕</span>
            </div>
            <div id="info-body">
                <div class="info-fila">
                    <span class="info-etiqueta">Folio</span>
                    <span class="info-valor" id="inf-folio">—</span>
                </div>
                <div class="info-fila">
                    <span class="info-etiqueta">Ejidatario</span>
                    <span class="info-valor" id="inf-ejidatario">—</span>
                </div>
                <div class="info-fila">
                    <span class="info-etiqueta">Superficie</span>
                    <span class="info-valor" id="inf-superficie">—</span>
                </div>
                <div class="info-fila">
                    <span class="info-etiqueta">Uso de suelo</span>
                    <span class="info-valor" id="inf-uso">—</span>
                </div>
                <div class="info-fila">
                    <span class="info-etiqueta">Estado</span>
                    <span class="info-valor" id="inf-estado">—</span>
                </div>
                <hr class="info-divider">
                <div class="info-acciones">
                    <button class="btn btn-ejidal btn-sm flex-fill" onclick="iniciarDibujo()">
                        <i class="fas fa-draw-polygon me-1"></i>Dibujar polígono
                    </button>
                    <button class="btn btn-outline-danger btn-sm px-3"
                            onclick="confirmarBorrar()" title="Borrar polígono">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
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

const CAPAS = {
    osm:        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:19, attribution:'© OpenStreetMap' }),
    sat_google: L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',  { maxZoom:20, attribution:'© Google' }),
    sat_esri:   L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom:19, attribution:'© ESRI' }),
};

const ESTILOS = {
    certificada:     { color:'#15803d', fillColor:'#16a34a', fillOpacity:.35 },
    expediente:      { color:'#1d4ed8', fillColor:'#2563eb', fillOpacity:.35 },
    litigio:         { color:'#b91c1c', fillColor:'#dc2626', fillOpacity:.35 },
    sin_regularizar: { color:'#b45309', fillColor:'#d97706', fillOpacity:.35 },
};

const map = L.map('mapa', {
    center: [19.2933, -98.5620], zoom: 16,
    zoomControl: false, zoomSnap:.5, zoomDelta:.5,
    wheelPxPerZoomLevel: 60, maxZoom:19, layers:[CAPAS.osm],
});
L.control.zoom({ position:'bottomleft' }).addTo(map);
L.control.scale({ metric:true, imperial:false, position:'bottomleft' }).addTo(map);

const PERIMETRO_EJIDO = [
    [19.3058,-98.5482],[19.3055,-98.5468],[19.3048,-98.5455],
    [19.3020,-98.5448],[19.2998,-98.5450],[19.2975,-98.5455],
    [19.2958,-98.5462],[19.2932,-98.5468],[19.2910,-98.5480],
    [19.2890,-98.5510],[19.2878,-98.5538],[19.2868,-98.5562],
    [19.2862,-98.5588],[19.2860,-98.5618],[19.2865,-98.5648],
    [19.2872,-98.5672],[19.2885,-98.5695],[19.2902,-98.5710],
    [19.2928,-98.5725],[19.2950,-98.5730],[19.2968,-98.5728],
    [19.2988,-98.5720],[19.3005,-98.5705],[19.3022,-98.5688],
    [19.3038,-98.5665],[19.3048,-98.5640],[19.3058,-98.5610],
    [19.3062,-98.5578],[19.3062,-98.5545],[19.3060,-98.5512],[19.3058,-98.5482],
];
const layerPerimetro = L.polygon(PERIMETRO_EJIDO, {
    color:'#b91c1c', weight:2.5, dashArray:'8 5', fill:false, opacity:.85,
}).addTo(map);
layerPerimetro.bindTooltip('Ejido San Rafael Ixtapalucan', { permanent:false, direction:'center' });

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
            layer.on('mouseover', function() { this.setStyle({ weight:3, fillOpacity:.6 }); });
            layer.on('mouseout',  function() {
                if (polyActual !== this) { const e = ESTILOS[p.estado]||ESTILOS.sin_regularizar; this.setStyle({ weight:1.8, fillOpacity:e.fillOpacity }); }
            });
            layer.bindTooltip(`<strong>${p.folio}</strong><br>${p.ejidatario}`, { sticky:true });
            layerPoligonos.addLayer(layer);
            const c = layer.getBounds().getCenter();
            layerEtiquetas.addLayer(L.marker(c, {
                icon: L.divIcon({ className:'', html:`<div style="background:rgba(255,255,255,.88);border:1px solid #aaa;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:700;white-space:nowrap">${p.folio}</div>`, iconAnchor:[18,8] }),
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
        if (prev) { const e = ESTILOS[prev.properties.estado]||ESTILOS.sin_regularizar; polyActual.setStyle({ weight:1.8, fillOpacity:e.fillOpacity }); }
    }
    parcelaActual = p; polyActual = layer;
    if (layer) layer.setStyle({ weight:3, fillOpacity:.65 });
    document.querySelectorAll('.parcela-item').forEach(el => el.classList.remove('activa'));
    const li = document.querySelector(`.parcela-item[data-id="${p.id}"]`);
    if (li) { li.classList.add('activa'); li.scrollIntoView({ block:'nearest', behavior:'smooth' }); }
    document.getElementById('info-titulo').textContent    = p.folio;
    document.getElementById('inf-folio').textContent      = p.folio;
    document.getElementById('inf-ejidatario').textContent = p.ejidatario;
    document.getElementById('inf-superficie').textContent = p.superficie ? p.superficie + ' ha' : '—';
    document.getElementById('inf-uso').textContent        = p.uso || '—';
    document.getElementById('inf-estado').textContent     = ESTADOS_LBL[p.estado] || p.estado || '—';
    document.getElementById('info-parcela').style.display = 'block';
}

function cerrarInfo() {
    document.getElementById('info-parcela').style.display = 'none';
    if (polyActual) {
        const prev = GEOJSON.features?.find(f => f.properties.id === parcelaActual?.id);
        if (prev) { const e = ESTILOS[prev.properties.estado]||ESTILOS.sin_regularizar; polyActual.setStyle({ weight:1.8, fillOpacity:e.fillOpacity }); }
    }
    parcelaActual = null; polyActual = null;
    document.querySelectorAll('.parcela-item').forEach(el => el.classList.remove('activa'));
}

function seleccionarParcela(id) {
    const bd = PARCELAS_BD.find(p => p.id === id);
    if (!bd) return;
    const layer = polyMap[id];
    if (layer) { map.fitBounds(layer.getBounds(), { padding:[60,60] }); mostrarInfo(bd, layer); }
    else { if (bd.lat && bd.lng) map.setView([bd.lat, bd.lng], 16); mostrarInfo(bd, null); }
}

let drawLayer = new L.FeatureGroup().addTo(map);
let drawControl = null;

function iniciarDibujo() {
    if (!parcelaActual) return;
    if (drawControl) { map.removeControl(drawControl); drawLayer.clearLayers(); }
    drawControl = new L.Control.Draw({
        draw: { polygon:{ allowIntersection:false, showArea:true, shapeOptions:{ color:'#e67e22', weight:2, fillOpacity:.25 } }, polyline:false, rectangle:false, circle:false, circlemarker:false, marker:false },
        edit: { featureGroup:drawLayer, remove:false },
    });
    map.addControl(drawControl);
    document.getElementById('barra-dibujo').classList.add('visible');
    document.getElementById('info-parcela').style.display = 'none';
    setTimeout(() => document.querySelector('.leaflet-draw-draw-polygon')?.click(), 200);
}

map.on(L.Draw.Event.CREATED, async (e) => {
    drawLayer.clearLayers(); drawLayer.addLayer(e.layer);
    await guardarVertices(e.layer.getLatLngs()[0].map(ll => [ll.lat, ll.lng]));
});

async function guardarVertices(vertices) {
    if (!parcelaActual) return;
    try {
        const r = await fetch(`/api/parcelas/${parcelaActual.id}/poligono`, {
            method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF },
            body: JSON.stringify({ vertices }),
        });
        const d = await r.json();
        if (d.ok) { toast('Polígono guardado ✓','success'); setTimeout(() => location.reload(), 800); }
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
        const r = await fetch(`/api/parcelas/${parcelaActual.id}/poligono`, { method:'DELETE', headers:{ 'X-CSRF-TOKEN':CSRF } });
        const d = await r.json();
        if (d.ok) { toast('Polígono eliminado','warning'); setTimeout(() => location.reload(), 700); }
    } catch(e) { toast('Error al borrar','danger'); }
}

document.querySelectorAll('.tab-capa').forEach(tab => {
    tab.addEventListener('click', () => {
        const c = tab.dataset.capa;
        if (c === capaActual) return;
        map.removeLayer(CAPAS[capaActual]); map.addLayer(CAPAS[c]); capaActual = c;
        document.querySelectorAll('.tab-capa').forEach(t => t.classList.toggle('activo', t.dataset.capa === c));
    });
});

document.getElementById('chk-parcelas')?.addEventListener('change', function() { this.checked ? map.addLayer(layerPoligonos) : map.removeLayer(layerPoligonos); });
document.getElementById('chk-etiquetas')?.addEventListener('change', function() { this.checked ? map.addLayer(layerEtiquetas) : map.removeLayer(layerEtiquetas); });
document.getElementById('chk-perimetro')?.addEventListener('change', function() { this.checked ? map.addLayer(layerPerimetro) : map.removeLayer(layerPerimetro); });

map.on('mousemove', e => {
    document.getElementById('coords').textContent = `Lat: ${e.latlng.lat.toFixed(6)}  |  Lng: ${e.latlng.lng.toFixed(6)}`;
});

function centrarEjido() { map.fitBounds(layerPerimetro.getBounds(), { padding:[30,30] }); }

function toast(msg, tipo='success') {
    const colores = { success:'#198754', danger:'#dc3545', warning:'#fd7e14' };
    const el = document.createElement('div');
    el.style.cssText = `position:fixed;bottom:40px;right:20px;z-index:9999;background:${colores[tipo]};color:#fff;padding:10px 18px;border-radius:7px;font-size:13px;font-weight:500;box-shadow:0 3px 14px rgba(0,0,0,.22)`;
    el.textContent = msg; document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

document.addEventListener('DOMContentLoaded', () => map.invalidateSize());
window.addEventListener('load', () => map.invalidateSize());
</script>

@include('IncludeViews.pie')