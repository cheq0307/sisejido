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
        <div class="card-header card-header-ejidal">
            <h4 class="mb-0"><i class="fas fa-hand-holding-heart me-2"></i>Nuevo Apoyo Social</h4>
        </div>
        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('apoyos.store') }}" method="POST">
                @csrf
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ejidatario Beneficiado <span class="text-danger">*</span></label>
                        <select name="idEjidatario" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach($ejidatarios as $e)
                                <option value="{{ $e->idEjidatario }}"
                                    {{ old('idEjidatario') == $e->idEjidatario ? 'selected' : '' }}>
                                    {{ $e->numeroEjidatario }} — {{ $e->apellidoPaterno }} {{ $e->apellidoMaterno }} {{ $e->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tipo de Apoyo <span class="text-danger">*</span></label>
                        <input type="text" name="tipo_apoyo" class="form-control"
                               placeholder="Ej: PROCAMPO, Fertilizante, Sembrando Vida..."
                               value="{{ old('tipo_apoyo') }}" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">Descripción</label>
                        <input type="text" name="descripcion" class="form-control"
                               placeholder="Detalle del apoyo" value="{{ old('descripcion') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Monto ($)</label>
                        <input type="number" name="monto" class="form-control"
                               step="0.01" min="0" placeholder="0.00" value="{{ old('monto', 0) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cantidad</label>
                        <input type="number" name="cantidad" class="form-control"
                               min="0" placeholder="0" value="{{ old('cantidad', 0) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Unidad de Medida</label>
                        <input type="text" name="unidad_medida" class="form-control"
                               placeholder="kg, litros, pza, pesos..." value="{{ old('unidad_medida') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Fecha de Entrega <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_entrega" class="form-control"
                               value="{{ old('fecha_entrega') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Ciclo <small class="text-muted fw-normal">(opcional)</small></label>
                        <input type="text" name="ciclo" class="form-control"
                               placeholder="Ej: 2025-PV, 2025-OI" value="{{ old('ciclo') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Estatus <span class="text-danger">*</span></label>
                        <select name="estatus" class="form-select" required>
                            <option value="pendiente"  {{ old('estatus','pendiente') == 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                            <option value="entregado"  {{ old('estatus') == 'entregado'  ? 'selected' : '' }}>Entregado</option>
                            <option value="cancelado"  {{ old('estatus') == 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Dependencia / Institución</label>
                        <input type="text" name="dependencia" class="form-control"
                               placeholder="Ej: SADER, SEDESOL, BIENESTAR..." value="{{ old('dependencia') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nombre del Representante <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_representante" class="form-control"
                               placeholder="Quien recibe por parte del ejido"
                               value="{{ old('nombre_representante') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Núm. Beneficiarios</label>
                        <input type="number" name="num_beneficiarios" class="form-control"
                               min="1" value="{{ old('num_beneficiarios', 1) }}">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2"
                                  placeholder="Notas adicionales...">{{ old('observaciones') }}</textarea>
                    </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                    <a href="{{ route('apoyos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

</main>
</div>
</div>

@include('IncludeViews.pie')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>