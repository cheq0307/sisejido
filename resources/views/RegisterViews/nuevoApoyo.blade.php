@extends('layouts.app')

@section('title', 'Nuevo Apoyo Social')

@section('content')
<div class="container py-4">

    {{-- Encabezado --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-heart-pulse me-2 text-success"></i>Nuevo Apoyo Social
        </h4>
        <a href="{{ route('apoyos.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Regresar
        </a>
    </div>

    {{-- Errores de validación --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-triangle me-1"></i>Por favor corrige los siguientes errores:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('apoyos.store') }}" method="POST">
                @csrf

                <div class="row g-3">

                    {{-- ── Ejidatario ── --}}
                    <div class="col-md-6">
                        <label for="idEjidatario" class="form-label fw-semibold">
                            Ejidatario Beneficiado <span class="text-danger">*</span>
                        </label>
                        <select name="idEjidatario" id="idEjidatario"
                                class="form-select @error('idEjidatario') is-invalid @enderror" required>
                            <option value="">-- Selecciona --</option>
                            @foreach ($ejidatarios as $e)
                                <option value="{{ $e->idEjidatario }}"
                                    {{ old('idEjidatario') == $e->idEjidatario ? 'selected' : '' }}>
                                    {{ $e->idEjidatario }} — {{ $e->apellidoPaterno }} {{ $e->apellidoMaterno }} {{ $e->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('idEjidatario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Tipo de Apoyo ── --}}
                    <div class="col-md-6">
                        <label for="tipo_apoyo" class="form-label fw-semibold">
                            Tipo de Apoyo <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="tipo_apoyo" id="tipo_apoyo" maxlength="100"
                               class="form-control @error('tipo_apoyo') is-invalid @enderror"
                               value="{{ old('tipo_apoyo') }}" placeholder="Ej: Fertilizante, Semilla..." required>
                        @error('tipo_apoyo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Descripción (opcional) ── --}}
                    <div class="col-12">
                        <label for="descripcion" class="form-label fw-semibold">
                            Descripción
                            <span class="text-muted small">(opcional)</span>
                        </label>
                        <textarea name="descripcion" id="descripcion" rows="2" maxlength="500"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  placeholder="Detalle del apoyo">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Monto ── --}}
                    <div class="col-md-4">
                        <label for="monto" class="form-label fw-semibold">
                            Monto ($) <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="monto" id="monto" min="0" step="0.01"
                               class="form-control @error('monto') is-invalid @enderror"
                               value="{{ old('monto', 0) }}" required>
                        @error('monto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Cantidad ── --}}
                    <div class="col-md-4">
                        <label for="cantidad" class="form-label fw-semibold">
                            Cantidad <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="cantidad" id="cantidad" min="0"
                               class="form-control @error('cantidad') is-invalid @enderror"
                               value="{{ old('cantidad', 0) }}" required>
                        @error('cantidad')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Unidad de Medida (opcional) ── --}}
                    <div class="col-md-4">
                        <label for="unidad_medida" class="form-label fw-semibold">
                            Unidad de Medida
                            <span class="text-muted small">(opcional)</span>
                        </label>
                        <input type="text" name="unidad_medida" id="unidad_medida" maxlength="50"
                               class="form-control @error('unidad_medida') is-invalid @enderror"
                               value="{{ old('unidad_medida') }}" placeholder="pzas, kg, lt...">
                        @error('unidad_medida')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Fecha de Entrega ── --}}
                    <div class="col-md-4">
                        <label for="fecha_entrega" class="form-label fw-semibold">
                            Fecha de Entrega <span class="text-danger">*</span>
                        </label>
                        {{--
                            IMPORTANTE: type="date" envía siempre el formato Y-m-d al servidor,
                            que es exactamente lo que la validación espera. No se necesita JS extra.
                        --}}
                        <input type="date" name="fecha_entrega" id="fecha_entrega"
                               class="form-control @error('fecha_entrega') is-invalid @enderror"
                               value="{{ old('fecha_entrega') }}"
                               max="2100-12-31" required>
                        @error('fecha_entrega')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Ciclo (opcional) ── --}}
                    <div class="col-md-4">
                        <label for="ciclo" class="form-label fw-semibold">
                            Ciclo
                            <span class="text-muted small">(opcional)</span>
                        </label>
                        <input type="text" name="ciclo" id="ciclo" maxlength="20"
                               class="form-control @error('ciclo') is-invalid @enderror"
                               value="{{ old('ciclo') }}" placeholder="Ej: 2025-PV, 2025-OI">
                        @error('ciclo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Estatus ── --}}
                    <div class="col-md-4">
                        <label for="estatus" class="form-label fw-semibold">
                            Estatus <span class="text-danger">*</span>
                        </label>
                        <select name="estatus" id="estatus"
                                class="form-select @error('estatus') is-invalid @enderror" required>
                            <option value="">-- Selecciona --</option>
                            @foreach (['entregado' => 'Entregado', 'pendiente' => 'Pendiente',
                                       'aprobado'  => 'Aprobado',  'cancelado' => 'Cancelado'] as $val => $label)
                                <option value="{{ $val }}" {{ old('estatus') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('estatus')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Dependencia / Institución (opcional) ── --}}
                    <div class="col-md-6">
                        <label for="dependencia" class="form-label fw-semibold">
                            Dependencia / Institución
                            <span class="text-muted small">(opcional)</span>
                        </label>
                        <input type="text" name="dependencia" id="dependencia" maxlength="100"
                               class="form-control @error('dependencia') is-invalid @enderror"
                               value="{{ old('dependencia') }}" placeholder="Ej: SADER, SEDESOL, BIENESTAR...">
                        @error('dependencia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Nombre del Representante ── --}}
                    <div class="col-md-6">
                        <label for="nombre_representante" class="form-label fw-semibold">
                            Nombre del Representante <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre_representante" id="nombre_representante" maxlength="100"
                               class="form-control @error('nombre_representante') is-invalid @enderror"
                               value="{{ old('nombre_representante') }}" required>
                        @error('nombre_representante')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Núm. Beneficiarios ── --}}
                    <div class="col-md-4">
                        <label for="num_beneficiarios" class="form-label fw-semibold">
                            Núm. Beneficiarios <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="num_beneficiarios" id="num_beneficiarios" min="1"
                               class="form-control @error('num_beneficiarios') is-invalid @enderror"
                               value="{{ old('num_beneficiarios', 1) }}" required>
                        @error('num_beneficiarios')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Observaciones (opcional) ── --}}
                    <div class="col-12">
                        <label for="observaciones" class="form-label fw-semibold">
                            Observaciones
                            <span class="text-muted small">(opcional)</span>
                        </label>
                        <textarea name="observaciones" id="observaciones" rows="3" maxlength="1000"
                                  class="form-control @error('observaciones') is-invalid @enderror"
                                  placeholder="Notas adicionales...">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>{{-- /row --}}

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('apoyos.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Guardar Apoyo
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection