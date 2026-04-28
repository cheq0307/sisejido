<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Parcelas - Ejido San Rafael Ixtapalucan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/estiloPrincipal.css') }}">
</head>
<body>

@include('IncludeViews.cabeza')
@include('IncludeViews.menu')

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
<div class="container-fluid">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">Listado de Parcelas</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('parcelas.mapa') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-map me-1"></i> Ver mapa
            </a>
            <a href="{{ route('parcelas.create') }}" class="btn btn-ejidal btn-sm">
                <i class="fas fa-plus me-1"></i> Nueva Parcela
            </a>
        </div>
    </div>

    {{-- Mensajes --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Tabla --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">No. Parcela</th>
                        <th>Ejidatario</th>
                        <th>Ubicación</th>
                        <th>Superficie</th>
                        <th>Estado</th>
                        <th class="text-center" style="width:140px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parcelas as $p)
                    <tr>
                        <td class="ps-3 fw-semibold">
                            P-{{ str_pad($p->noParcela, 3, '0', STR_PAD_LEFT) }}
                        </td>
                        <td>
                            {{ $p->ejidatario
                                ? $p->ejidatario->nombre . ' ' . $p->ejidatario->apellidoPaterno
                                : '<span class="text-muted fst-italic">Sin asignar</span>' }}
                        </td>
                        <td>{{ $p->ubicacion ?? '—' }}</td>
                        <td>{{ $p->superficie ? $p->superficie . ' ha' : '—' }}</td>
                        <td>
                            @php
                                $badges = [
                                    'certificada'     => ['success', 'Certificada'],
                                    'expediente'      => ['primary', 'Con expediente'],
                                    'litigio'         => ['danger',  'En litigio'],
                                    'sin_regularizar' => ['warning', 'Sin regularizar'],
                                ];
                                $b = $badges[$p->estado ?? ''] ?? ['secondary', 'Sin estado'];
                            @endphp
                            <span class="badge bg-{{ $b[0] }}">{{ $b[1] }}</span>
                        </td>
                        <td class="text-center">
                            {{-- Editar --}}
                            <a href="{{ route('parcelas.editar', $p->idParcela) }}"
                               class="btn btn-sm btn-outline-secondary"
                               title="Editar parcela">
                                <i class="fas fa-edit"></i>
                            </a>

                            {{-- Ver en mapa --}}
                            <a href="{{ route('parcelas.mapa') }}"
                               class="btn btn-sm btn-outline-primary"
                               title="Ver en mapa">
                                <i class="fas fa-map-marker-alt"></i>
                            </a>

                            {{-- Eliminar --}}
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Eliminar parcela"
                                    onclick="confirmarEliminar({{ $p->idParcela }}, 'P-{{ str_pad($p->noParcela, 3, '0', STR_PAD_LEFT) }}')">
                                <i class="fas fa-trash"></i>
                            </button>

                            {{-- Form oculto para eliminar --}}
                            <form id="form-eliminar-{{ $p->idParcela }}"
                                  action="{{ route('parcelas.destroy', $p->idParcela) }}"
                                  method="POST" style="display:none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-map-marked-alt fa-2x mb-2 d-block opacity-25"></i>
                            No hay parcelas registradas.
                            <a href="{{ route('parcelas.create') }}" class="d-block mt-2">
                                Registrar primera parcela
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($parcelas->count() > 0)
        <div class="card-footer text-muted" style="font-size:12px">
            {{ $parcelas->count() }} parcela{{ $parcelas->count() != 1 ? 's' : '' }} registrada{{ $parcelas->count() != 1 ? 's' : '' }}
        </div>
        @endif
    </div>

</div>
</main>

{{-- Modal confirmación eliminar --}}
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmar eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de eliminar la parcela <strong id="modal-folio"></strong>?</p>
                <p class="text-danger small mb-0">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Esta acción eliminará también sus coordenadas, colindancias e información administrativa. No se puede deshacer.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-eliminar">
                    <i class="fas fa-trash me-1"></i>Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>

@include('IncludeViews.pie')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let idEliminar = null;
const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));

function confirmarEliminar(id, folio) {
    idEliminar = id;
    document.getElementById('modal-folio').textContent = folio;
    modal.show();
}

document.getElementById('btn-confirmar-eliminar').addEventListener('click', function() {
    if (idEliminar) {
        document.getElementById('form-eliminar-' + idEliminar).submit();
    }
});
</script>
</body>
</html>