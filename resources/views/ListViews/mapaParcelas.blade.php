<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mapa de Parcelas - Ejido San Rafael Ixtapalucan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/estiloPrincipal.css') }}">

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <style>
        #mapa-leaflet {
            height: 580px;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            z-index: 0;
        }
        .badge-ocupada    { background-color: #d1fae5; color: #065f46; }
        .badge-disponible { background-color: #dbeafe; color: #1e40af; }
        .badge-litigio    { background-color: #fee2e2; color: #991b1b; }
        .badge-inactiva   { background-color: #f3f4f6; color: #374151; }
        .leyenda-dot {
            display: inline-block;
            width: 14px; height: 14px;
            border-radius: 3px;
            margin-right: 6px;
            vertical-align: middle;
        }
        .stat-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 14px;
        }
        .stat-card .num { font-size: 22px; font-weight: 600; }
        .stat-card .lbl { font-size: 11px; color: #6b7280; }
        .parcela-popup h6 { font-weight: 600; margin-bottom: 6px; }
        .parcela-popup table { font-size: 13px; }
        .parcela-popup td:first-child { color: #6b7280; padding-right: 8px; padding-bottom: 3px; }
        .btn-filtro { font-size: 12px; }
    </style>
</head>
<body>

@include('IncludeViews.cabeza')
@include('IncludeViews.menu')

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
<div class="container-fluid">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">Mapa de Parcelas</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('parcelas.create') }}" class="btn btn-ejidal btn-sm">
                <i class="fas fa-plus"></i> Nueva Parcela
            </a>
            <a href="{{ route('parcelas.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-list"></i> Listado
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <button class="btn btn-sm btn-ejidal btn-filtro" onclick="filtrar('todos', this)">Todas</button>
        <button class="btn btn-sm btn-outline-success btn-filtro" onclick="filtrar('ocupada', this)">Ocupadas</button>
        <button class="btn btn-sm btn-outline-primary btn-filtro" onclick="filtrar('disponible', this)">Disponibles</button>
        <button class="btn btn-sm btn-outline-danger btn-filtro" onclick="filtrar('litigio', this)">En litigio</button>
        <button class="btn btn-sm btn-outline-secondary btn-filtro" onclick="filtrar('inactiva', this)">Inactivas</button>
    </div>

    <div class="row g-3">

        {{-- Mapa --}}
        <div class="col-lg-8">
            <div id="mapa-leaflet"></div>
            <p class="text-muted mt-1" style="font-size:12px">
                <i class="fas fa-info-circle"></i>
                Haz clic en una parcela para ver su información.
            </p>
        </div>

        {{-- Panel derecho --}}
        <div class="col-lg-4">

            {{-- Resumen --}}
            <div class="card card-ejidal mb-3">
                <div class="card-header card-header-ejidal">Resumen del ejido</div>
                <div class="card-body">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <div class="stat-card">
                                <div class="lbl">Total parcelas</div>
                                <div class="num">{{ $resumen['total'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card" style="background:#d1fae5; border-color:#6ee7b7">
                                <div class="lbl" style="color:#065f46">Ocupadas</div>
                                <div class="num" style="color:#065f46">{{ $resumen['ocupadas'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card" style="background:#dbeafe; border-color:#93c5fd">
                                <div class="lbl" style="color:#1e40af">Disponibles</div>
                                <div class="num" style="color:#1e40af">{{ $resumen['disponibles'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card" style="background:#fee2e2; border-color:#fca5a5">
                                <div class="lbl" style="color:#991b1b">En litigio</div>
                                <div class="num" style="color:#991b1b">{{ $resumen['litigio'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Leyenda --}}
            <div class="card card-ejidal mb-3">
                <div class="card-header card-header-ejidal">Leyenda</div>
                <div class="card-body" style="font-size:13px">
                    <div class="mb-1"><span class="leyenda-dot" style="background:#1D9E75"></span>Ocupada</div>
                    <div class="mb-1"><span class="leyenda-dot" style="background:#378ADD"></span>Disponible</div>
                    <div class="mb-1"><span class="leyenda-dot" style="background:#E24B4A"></span>En litigio</div>
                    <div class="mb-1"><span class="leyenda-dot" style="background:#888780"></span>Inactiva</div>
                </div>
            </div>

            {{-- Detalle --}}
            <div class="card card-ejidal" id="card-detalle">
                <div class="card-header card-header-ejidal">Detalle de parcela</div>
                <div class="card-body">
                    <p class="text-muted mb-0" style="font-size:13px">
                        Haz clic en una parcela del mapa para ver su información.
                    </p>
                </div>
            </div>

        </div>
    </div>

</div>
</main>

@include('IncludeViews.pie')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const parcelasData = {!! $parcelasJson !!};

// Ajusta estas coordenadas al centro de tu ejido
const map = L.map('mapa-leaflet').setView([19.0, -98.2], 14);

// Capas de mapa
const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap',
    maxZoom: 19,
});
const sateliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: '© Esri',
    maxZoom: 19,
});

osmLayer.addTo(map);
L.control.layers({ 'Mapa': osmLayer, 'Satélite': sateliteLayer }).addTo(map);

const layerGroup = L.layerGroup().addTo(map);

function crearCapa(p) {
    let capa;
    if (p.coordenadas && p.coordenadas.length >= 3) {
        capa = L.polygon(p.coordenadas, {
            color: p.color, fillColor: p.color, fillOpacity: 0.4, weight: 2
        });
    } else if (p.lat && p.lng) {
        capa = L.circleMarker([p.lat, p.lng], {
            radius: 16, color: p.color, fillColor: p.color, fillOpacity: 0.5, weight: 2
        });
    } else {
        return null;
    }

    capa.bindPopup(`
        <div class="parcela-popup">
            <h6>Parcela ${p.clave}</h6>
            <table>
                <tr><td>Ejidatario</td><td><b>${p.ejidatario}</b></td></tr>
                <tr><td>Superficie</td><td>${p.superficie}</td></tr>
                <tr><td>Ubicación</td><td>${p.ubicacion}</td></tr>
                <tr><td>Estado</td><td>${p.estado}</td></tr>
                <tr><td>Cultivo</td><td>${p.cultivo}</td></tr>
            </table>
            <div class="mt-2 d-flex gap-1">
                <a href="/parcelas/${p.id}" class="btn btn-sm btn-outline-primary" style="font-size:12px;flex:1;text-align:center">Ver</a>
                <a href="/parcelas/${p.id}/editar" class="btn btn-sm btn-outline-secondary" style="font-size:12px;flex:1;text-align:center">Editar</a>
            </div>
        </div>
    `);

    capa.on('click', () => mostrarDetalle(p));
    return capa;
}

function renderizar(datos) {
    layerGroup.clearLayers();
    const bounds = [];
    datos.forEach(p => {
        const c = crearCapa(p);
        if (c) {
            layerGroup.addLayer(c);
            if (p.lat && p.lng) bounds.push([p.lat, p.lng]);
        }
    });
    if (bounds.length > 0) map.fitBounds(bounds, { padding: [40, 40] });
}

function filtrar(estado, btn) {
    document.querySelectorAll('.btn-filtro').forEach(b => {
        b.classList.remove('btn-ejidal');
        if (!b.classList.contains('btn-outline-success') &&
            !b.classList.contains('btn-outline-primary') &&
            !b.classList.contains('btn-outline-danger') &&
            !b.classList.contains('btn-outline-secondary')) {
            b.classList.add('btn-outline-secondary');
        }
    });
    btn.classList.add('btn-ejidal');

    const datos = estado === 'todos' ? parcelasData : parcelasData.filter(p => p.estado === estado);
    renderizar(datos);
}

function mostrarDetalle(p) {
    const badgeClass = {
        ocupada:'badge-ocupada', disponible:'badge-disponible',
        litigio:'badge-litigio', inactiva:'badge-inactiva'
    }[p.estado] || 'badge-inactiva';

    document.getElementById('card-detalle').innerHTML = `
        <div class="card-header card-header-ejidal">Detalle de parcela</div>
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span style="font-size:16px;font-weight:600">No. ${p.clave}</span>
                <span class="badge ${badgeClass} px-2 py-1" style="font-size:11px">${p.estado}</span>
            </div>
            <table style="width:100%;font-size:13px">
                <tr><td style="color:#6b7280;padding:3px 0;width:90px">Ejidatario</td><td><b>${p.ejidatario}</b></td></tr>
                <tr><td style="color:#6b7280;padding:3px 0">Superficie</td><td>${p.superficie}</td></tr>
                <tr><td style="color:#6b7280;padding:3px 0">Ubicación</td><td>${p.ubicacion}</td></tr>
                <tr><td style="color:#6b7280;padding:3px 0">Cultivo</td><td>${p.cultivo}</td></tr>
                <tr><td style="color:#6b7280;padding:3px 0">Agua</td><td>${p.tipo_agua}</td></tr>
            </table>
            <div class="mt-3 d-flex gap-2">
                <a href="/parcelas/${p.id}" class="btn btn-sm btn-ejidal" style="flex:1;text-align:center">Ver detalle</a>
                <a href="/parcelas/${p.id}/editar" class="btn btn-sm btn-outline-secondary" style="flex:1;text-align:center">Editar</a>
            </div>
        </div>
    `;
}

renderizar(parcelasData);
</script>

</body>
</html>
