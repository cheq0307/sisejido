<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Apoyo Social</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/estiloPrincipal.css') }}">
</head>
<body>

@include('IncludeViews.cabeza')

<div class="container-fluid">
<div class="row">
@include('IncludeViews.menu')

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="card card-ejidal shadow">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-hand-holding-heart me-2"></i>Apoyos Sociales</h4>
            <a href="{{ route('apoyos.create') }}" class="btn btn-light btn-sm">
                <i class="fas fa-plus me-1"></i> Nuevo Apoyo
            </a>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Ejidatario</th>
                            <th>Tipo de Apoyo</th>
                            <th>Dependencia</th>
                            <th>Monto</th>
                            <th>Cantidad</th>
                            <th>Fecha Entrega</th>
                            <th>Representante</th>
                            <th>Beneficiarios</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
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
                            <td>{{ $a->dependencia ?? '—' }}</td>
                            <td>
                                @if($a->monto > 0)
                                    ${{ number_format($a->monto, 2) }}
                                @else —
                                @endif
                            </td>
                            <td>
                                @if($a->cantidad > 0)
                                    {{ $a->cantidad }} {{ $a->unidad_medida }}
                                @else —
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($a->fecha_entrega)->format('d/m/Y') }}</td>
                            <td>{{ $a->nombre_representante }}</td>
                            <td class="text-center">{{ $a->num_beneficiarios }}</td>
                            <td class="text-center">
                                @if($a->estatus === 'entregado')
                                    <span class="badge bg-success">Entregado</span>
                                @elseif($a->estatus === 'pendiente')
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @else
                                    <span class="badge bg-danger">Cancelado</span>
                                @endif
                            </td>
                            <td>
                                @if($a->estatus === 'aprobado')
        <span class="badge bg-success">Aprobado</span>
@elseif($a->estatus === 'entregado')
    <span class="badge bg-success">Entregado</span>
@endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('apoyos.edit', $a->idApoyo) }}"
                                   class="btn btn-sm btn-ejidal">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('apoyos.destroy', $a->idApoyo) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar este apoyo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                No hay apoyos registrados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


@include('IncludeViews.pie')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>