<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Parcela</title>
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

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">Editar Parcela</h1>
        <a href="{{ route('parcelas.index') }}" class="btn btn-ejidal">
            <i class="fas fa-list me-1"></i> Ir al listado
        </a>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Errores de validación:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Info del ejidatario --}}
    @if($ejidatario)
    <div class="alert alert-info py-2 mb-3">
        <i class="fas fa-user-tie me-2"></i>
        <strong>{{ $ejidatario->nombre }} {{ $ejidatario->apellidoPaterno }} {{ $ejidatario->apellidoMaterno }}</strong>
        <span class="text-muted ms-2">— Ejidatario #{{ $ejidatario->numeroEjidatario }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('parcelas.actualizar') }}" id="form-editar">
        @csrf
        <input type="hidden" name="idParcela" value="{{ $parcela->idParcela }}">
        <input type="hidden" name="numeroEjidatario" value="{{ $ejidatario->numeroEjidatario ?? '' }}">
        {{-- Guardamos el noParcela original para detectar cambios en JS --}}
        <input type="hidden" id="noParcela-original" value="{{ $parcela->noParcela }}">

        {{-- PARCELA --}}
        <div class="card card-ejidal mb-3">
            <div class="card-header card-header-ejidal">
                <i class="fas fa-draw-polygon me-2"></i>Datos de la Parcela
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="label-ejidal">No. Parcela <span class="text-danger">*</span></label>
                        <input type="number" name="noParcela" id="noParcela"
                               class="form-control form-control-ejidal @error('noParcela') is-invalid @enderror"
                               value="{{ old('noParcela', $parcela->noParcela) }}"
                               min="1" required>
                        @error('noParcela')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="label-ejidal">Superficie (ha)</label>
                        <input type="text" name="superficie"
                               class="form-control form-control-ejidal"
                               value="{{ old('superficie', $parcela->superficie) }}"
                               placeholder="ej: 3.5">
                    </div>
                    <div class="col-md-4">
                        <label class="label-ejidal">Uso de Suelo</label>
                        <select name="usoSuelo" class="form-select form-select-ejidal">
                            @foreach($usos as $uso)
                                <option value="{{ $uso->idUso }}"
                                    {{ $parcela->idUso == $uso->idUso ? 'selected' : '' }}>
                                    {{ $uso->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="label-ejidal">Ubicación</label>
                        <input type="text" name="ubicacion"
                               class="form-control form-control-ejidal"
                               value="{{ old('ubicacion', $parcela->ubicacion) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- COLINDANCIAS --}}
        <div class="card card-ejidal mb-3">
            <div class="card-header card-header-ejidal">
                <i class="fas fa-compass me-2"></i>Colindancias
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach(['norte','sur','este','oeste','noreste','noroeste','sureste','suroeste'] as $c)
                        <div class="col-md-3 mb-2">
                            <label class="label-ejidal">{{ ucfirst($c) }}</label>
                            <input type="text" name="{{ $c }}"
                                   class="form-control form-control-ejidal"
                                   value="{{ old($c, $colindancia[$c] ?? '') }}">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- COORDENADAS --}}
        @if($coordenadas->count() > 0)
        <div class="card card-ejidal mb-3">
            <div class="card-header card-header-ejidal">
                <i class="fas fa-map-pin me-2"></i>Coordenadas GPS
                <span class="badge bg-light text-dark ms-2">{{ $coordenadas->count() }} puntos</span>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2" style="font-size:12px">
                    <i class="fas fa-info-circle me-1"></i>
                    Para redibujar el polígono desde el mapa usa el botón <strong>Dibujar</strong> en el mapa catastral.
                </div>
                @foreach($coordenadas as $coor)
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label class="label-ejidal">Punto</label>
                            <input type="text"
                                   name="coordenadas[{{ $loop->index }}][punto]"
                                   class="form-control form-control-ejidal"
                                   value="{{ $coor->punto }}">
                        </div>
                        <div class="col-md-4">
                            <label class="label-ejidal">Latitud (X)</label>
                            <input type="number" step="0.000001"
                                   name="coordenadas[{{ $loop->index }}][coordenadaX]"
                                   class="form-control form-control-ejidal"
                                   value="{{ $coor->coordenadaX }}">
                        </div>
                        <div class="col-md-4">
                            <label class="label-ejidal">Longitud (Y)</label>
                            <input type="number" step="0.000001"
                                   name="coordenadas[{{ $loop->index }}][coordenadaY]"
                                   class="form-control form-control-ejidal"
                                   value="{{ $coor->coordenadaY }}">
                            <input type="hidden"
                                   name="coordenadas[{{ $loop->index }}][idCoordenada]"
                                   value="{{ $coor->idCoordenada }}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- INFORMACIÓN ADMINISTRATIVA --}}
        <div class="card card-ejidal mb-3">
            <div class="card-header card-header-ejidal">
                <i class="fas fa-file-alt me-2"></i>Información Administrativa
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label class="label-ejidal">Número de inscripción RAN</label>
                        <input type="text" name="num_inscripcionRAN"
                               class="form-control form-control-ejidal"
                               value="{{ old('num_inscripcionRAN', $infAdmin->num_inscripcionRAN ?? '') }}">
                    </div>
                    <div class="col-md-7">
                        <label class="label-ejidal">Clave núcleo agrario</label>
                        <input type="text" name="claveNucleoAgrario"
                               class="form-control form-control-ejidal"
                               value="{{ old('claveNucleoAgrario', $infAdmin->claveNucleoAgrario ?? '') }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label class="label-ejidal">Comunidad</label>
                        <input type="text" name="comunidad"
                               class="form-control form-control-ejidal"
                               value="{{ old('comunidad', $infAdmin->comunidad ?? '') }}">
                    </div>
                    <div class="col-md-7">
                        <label class="label-ejidal">Fecha de expedición</label>
                        <input type="date" name="fechaExpedicion" id="fechaExpedicion"
                               class="form-control form-control-ejidal @error('fechaExpedicion') is-invalid @enderror"
                               value="{{ old('fechaExpedicion', isset($infAdmin->fechaExpedicion) ? \Carbon\Carbon::parse($infAdmin->fechaExpedicion)->format('Y-m-d') : '') }}"
                               min="1900-01-01" max="2100-12-31">
                        @error('fechaExpedicion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted" style="font-size:11px">
                            Año entre 1900 y 2100
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTONES --}}
        <div class="text-end mt-4 mb-5">
            <a href="{{ route('parcelas.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-times me-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-ejidal px-4">
                <i class="fas fa-save me-2"></i>Actualizar Información
            </button>
        </div>

    </form>

</main>
</div>
</div>

@include('IncludeViews.pie')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Validación cliente: fecha entre 1900 y 2100 ──────────────
document.getElementById('fechaExpedicion')?.addEventListener('change', function () {
    const val = this.value;
    if (!val) return;
    const año = parseInt(val.split('-')[0]);
    const msgEl = this.nextElementSibling?.nextElementSibling ?? null;
    if (año < 1900 || año > 2100) {
        this.classList.add('is-invalid');
        this.setCustomValidity('El año debe estar entre 1900 y 2100.');
        if (msgEl && msgEl.classList.contains('form-text')) {
            msgEl.textContent = '⚠ El año debe estar entre 1900 y 2100.';
            msgEl.classList.add('text-danger');
        }
    } else {
        this.classList.remove('is-invalid');
        this.setCustomValidity('');
        if (msgEl && msgEl.classList.contains('form-text')) {
            msgEl.textContent = 'Año entre 1900 y 2100';
            msgEl.classList.remove('text-danger');
        }
    }
});

// ── Evitar submit con Enter en campos individuales ───────────
document.getElementById('form-editar')?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.type !== 'submit') {
        e.preventDefault();
    }
});

// ── Validación antes de enviar ────────────────────────────────
document.getElementById('form-editar')?.addEventListener('submit', function (e) {
    const fecha = document.getElementById('fechaExpedicion');
    if (fecha?.value) {
        const año = parseInt(fecha.value.split('-')[0]);
        if (año < 1900 || año > 2100) {
            e.preventDefault();
            fecha.classList.add('is-invalid');
            fecha.focus();
            return;
        }
    }
    const noParcela = document.getElementById('noParcela');
    if (!noParcela?.value || parseInt(noParcela.value) < 1) {
        e.preventDefault();
        noParcela?.focus();
        alert('El número de parcela es obligatorio y debe ser mayor a 0.');
    }
});
</script>

</body>
</html>