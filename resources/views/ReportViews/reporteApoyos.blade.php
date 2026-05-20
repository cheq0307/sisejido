<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Apoyos Sociales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/estiloPrincipal.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            button { display: none; }
            .no-print { display: none; }
            body::after {
                content: "Sistema de Gestión Ejidal © 2025 | Versión 1.0.0";
                display: block;
                text-align: center;
                margin-top: 20px;
                font-weight: bold;
            }
        }
    </style>
</head>
<body>

@include('IncludeViews.cabeza')
@include('IncludeViews.menu')

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
<div class="container-fluid">
<div class="row">
<div class="col-md-10 p-4">

    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-hand-holding-heart me-2"></i>Reporte de Apoyos Sociales
        </h1>
        <button class="btn btn-ejidal no-print" onclick="imprimirReporte()">
            <i class="fas fa-file-pdf me-1"></i> Generar PDF
        </button>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('apoyos.reporte') }}" class="card card-ejidal mb-4 no-print">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Estatus</label>
                    <select name="estatus" class="form-select form-select-sm">
                        <option value="">-- Todos --</option>
                        <option value="entregado"  {{ request('estatus') == 'entregado'  ? 'selected' : '' }}>Entregado</option>
                        <option value="pendiente"  {{ request('estatus') == 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                        <option value="cancelado"  {{ request('estatus') == 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                        <option value="aprobado"   {{ request('estatus') == 'aprobado'   ? 'selected' : '' }}>Aprobado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tipo de Apoyo</label>
                    <input type="text" name="tipo_apoyo" class="form-control form-control-sm"
                           placeholder="Ej: PROCAMPO..." value="{{ request('tipo_apoyo') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Fecha desde</label>
                    <input type="date" name="fecha_desde" class="form-control form-control-sm"
                           value="{{ request('fecha_desde') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Fecha hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                           value="{{ request('fecha_hasta') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-ejidal btn-sm w-100">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('apoyos.reporte') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- Resumen --}}
    <div class="row g-3 mb-4 no-print">
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="h4 text-ejidal mb-0">{{ $apoyos->count() }}</div>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="h4 text-success mb-0">{{ $apoyos->where('estatus','entregado')->count() }}</div>
                    <small class="text-muted">Entregados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="h4 text-warning mb-0">{{ $apoyos->where('estatus','pendiente')->count() }}</div>
                    <small class="text-muted">Pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="h4 text-primary mb-0">{{ $apoyos->where('estatus','aprobado')->count() }}</div>
                    <small class="text-muted">Aprobados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="h4 text-danger mb-0">{{ $apoyos->where('estatus','cancelado')->count() }}</div>
                    <small class="text-muted">Cancelados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="h4 text-ejidal mb-0">${{ number_format($apoyos->sum('monto'), 2) }}</div>
                    <small class="text-muted">Monto total</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <table id="tablaApoyos" class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Ejidatario</th>
                <th>Tipo de Apoyo</th>
                <th>Descripción</th>
                <th>Dependencia</th>
                <th>Rep. Dependencia</th>
                <th>Monto</th>
                <th>Cantidad</th>
                <th>Fecha Entrega</th>
                <th>Ciclo</th>
                <th>Representante</th>
                <th>Beneficiarios</th>
                <th>Observaciones</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            @forelse($apoyos as $a)
            <tr>
                <td>{{ $a->idApoyo }}</td>
                <td>
                    @if($a->ejidatario)
                        {{ $a->ejidatario->apellidoPaterno }}
                        {{ $a->ejidatario->apellidoMaterno }}
                        {{ $a->ejidatario->nombre }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>{{ $a->tipo_apoyo }}</td>
                <td>{{ $a->descripcion ?? '—' }}</td>
                <td>{{ $a->dependencia ?? '—' }}</td>
                <td>{{ $a->representante_dependencia ?? '—' }}</td>
                <td>
                    @if($a->monto > 0)
                        ${{ number_format($a->monto, 2) }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($a->cantidad > 0)
                        {{ $a->cantidad }} {{ $a->unidad_medida }}
                    @else
                        —
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($a->fecha_entrega)->format('d/m/Y') }}</td>
                <td>{{ $a->ciclo ?? '—' }}</td>
                <td>{{ $a->nombre_representante ?? '—' }}</td>
                <td class="text-center">{{ $a->num_beneficiarios }}</td>
                <td>{{ $a->observaciones ?? '—' }}</td>
                <td class="text-center">
                    @if($a->estatus === 'entregado')
                        <span class="badge bg-success">Entregado</span>
                    @elseif($a->estatus === 'pendiente')
                        <span class="badge bg-warning text-dark">Pendiente</span>
                    @elseif($a->estatus === 'aprobado')
                        <span class="badge bg-primary">Aprobado</span>
                    @else
                        <span class="badge bg-danger">Cancelado</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="14" class="text-center text-muted py-4">
                    No hay apoyos registrados con los filtros seleccionados.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($apoyos->count() > 0)
        <tfoot class="table-secondary fw-bold">
            <tr>
                <td colspan="6" class="text-end">Totales:</td>
                <td>${{ number_format($apoyos->sum('monto'), 2) }}</td>
                <td>—</td>
                <td>—</td>
                <td>—</td>
                <td>—</td>
                <td class="text-center">{{ $apoyos->sum('num_beneficiarios') }}</td>
                <td>—</td>
                <td>—</td>
            </tr>
        </tfoot>
        @endif
    </table>

</div>
</div>
</div>
</main>

@include('IncludeViews.pie')

<script>
function imprimirReporte() {
    const original = document.body.innerHTML;
    const tabla = document.getElementById('tablaApoyos').outerHTML;
    document.body.innerHTML =
        '<h2 style="text-align:center;margin-bottom:10px">Reporte de Apoyos Sociales</h2>' +
        '<p style="text-align:center;font-size:12px;color:#666">Ejido San Rafael Ixtapalucan — ' +
        new Date().toLocaleDateString('es-MX') + '</p>' +
        tabla;
    window.print();
    document.body.innerHTML = original;
    location.reload();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>