<!DOCTYPE html>
<html lang="es">
@include('IncludeViews.cabeza')
<body>
@include('IncludeViews.menu')

<div class="mt-4">
    <div class="card card-ejidal shadow">
        <div class="card-header card-header-ejidal">
            <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Editar Apoyo Social #{{ $apoyo->idApoyo }}</h4>
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

            <form action="{{ route('apoyos.update', $apoyo->idApoyo) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ejidatario Beneficiado <span class="text-danger">*</span></label>
                        <select name="idEjidatario" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach($ejidatarios as $e)
                                <option value="{{ $e->idEjidatario }}"
                                    {{ $apoyo->idEjidatario == $e->idEjidatario ? 'selected' : '' }}>
                                    {{ $e->numeroEjidatario }} — {{ $e->apellidoPaterno }} {{ $e->apellidoMaterno }} {{ $e->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tipo de Apoyo <span class="text-danger">*</span></label>
                        <input type="text" name="tipo_apoyo" class="form-control"
                               value="{{ $apoyo->tipo_apoyo }}" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">Descripción</label>
                        <input type="text" name="descripcion" class="form-control"
                               value="{{ $apoyo->descripcion }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Monto ($)</label>
                        <input type="number" name="monto" class="form-control"
                               step="0.01" min="0" value="{{ $apoyo->monto }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cantidad</label>
                        <input type="number" name="cantidad" class="form-control"
                               min="0" value="{{ $apoyo->cantidad }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Unidad de Medida</label>
                        <input type="text" name="unidad_medida" class="form-control"
                               value="{{ $apoyo->unidad_medida }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Fecha de Entrega <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_entrega" class="form-control"
                               value="{{ $apoyo->fecha_entrega }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Ciclo</label>
                        <input type="text" name="ciclo" class="form-control"
                               value="{{ $apoyo->ciclo }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Estatus <span class="text-danger">*</span></label>
                        <select name="estatus" class="form-select" required>
                            <option value="pendiente"  {{ $apoyo->estatus == 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                            <option value="entregado"  {{ $apoyo->estatus == 'entregado'  ? 'selected' : '' }}>Entregado</option>
                            <option value="cancelado"  {{ $apoyo->estatus == 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Dependencia / Institución</label>
                        <input type="text" name="dependencia" class="form-control"
                               value="{{ $apoyo->dependencia }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nombre del Representante <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_representante" class="form-control"
                               value="{{ $apoyo->nombre_representante }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Núm. Beneficiarios</label>
                        <input type="number" name="num_beneficiarios" class="form-control"
                               min="1" value="{{ $apoyo->num_beneficiarios }}">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea name="observaciones" class="form-control"
                                  rows="2">{{ $apoyo->observaciones }}</textarea>
                    </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Actualizar
                    </button>
                    <a href="{{ route('apoyos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

@include('IncludeViews.pie')
</body>
</html>