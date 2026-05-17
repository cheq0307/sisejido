<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Models\Ejidatario;
use App\Models\Uso;
use App\Models\Colindancia;
use App\Models\Coordenada;
use App\Models\InfAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParcelaController extends Controller
{
    // ── LISTADO ─────────────────────────────────────────────
    public function index()
    {
        $parcelas = Parcela::with('ejidatario')->get();
        return view('ListViews.listadoParcelas', compact('parcelas'));
    }

    // ── MAPA CATASTRAL ──────────────────────────────────────
    public function mapa()
    {
        $parcelas = Parcela::with(['ejidatario', 'coordenadas', 'uso'])->get();

        $geojson = $this->buildGeoJSON($parcelas);

        $stats = [
            'total'        => $parcelas->count(),
            'con_poligono' => $parcelas->filter->tienePoligono()->count(),
            'sin_poligono' => $parcelas->reject->tienePoligono()->count(),
            'certificadas' => $parcelas->where('estado', 'certificada')->count(),
            'en_litigio'   => $parcelas->where('estado', 'litigio')->count(),
        ];

        $parcelasJs = $parcelas->map(function ($p) {
            return [
                'id'            => $p->idParcela,
                'folio'         => 'P-' . str_pad($p->noParcela, 3, '0', STR_PAD_LEFT),
                'noParcela'     => $p->noParcela,
                'ejidatario'    => $p->ejidatario
                                    ? $p->ejidatario->nombre . ' ' . $p->ejidatario->apellidoPaterno
                                    : 'Sin asignar',
                'superficie'    => $p->superficie,
                'uso'           => optional($p->uso)->nombre ?? '—',
                'estado'        => $p->estado ?? 'sin_regularizar',
                'ubicacion'     => $p->ubicacion,
                'tienePoligono' => $p->tienePoligono(),
                'lat'           => $p->lat,
                'lng'           => $p->lng,
            ];
        })->values()->toArray();

        // Pasar lista de ejidatarios para el panel lateral
        $ejidatarios = Ejidatario::orderBy('apellidoPaterno')->get();

        return view('ListViews.mapaParcelas', compact('geojson', 'stats', 'parcelas', 'parcelasJs', 'ejidatarios'));
    }

    // ── API: GeoJSON ────────────────────────────────────────
    public function apiGeoJSON()
    {
        $parcelas = Parcela::with(['ejidatario', 'coordenadas', 'uso'])->get();
        return response()->json($this->buildGeoJSON($parcelas));
    }

    // ── API: GUARDAR VÉRTICES ───────────────────────────────
    public function guardarPoligono(Request $request, $id)
    {
        $request->validate([
            'vertices'   => 'required|array|min:3',
            'vertices.*' => 'array|size:2',
        ]);

        $parcela = Parcela::where('idParcela', $id)->firstOrFail();

        DB::transaction(function () use ($parcela, $request) {
            Coordenada::where('idParcela', $parcela->idParcela)->delete();

            $lats = array_column($request->vertices, 0);
            $lngs = array_column($request->vertices, 1);

            foreach ($request->vertices as $orden => $punto) {
                Coordenada::create([
                    'idParcela'   => $parcela->idParcela,
                    'orden'       => $orden + 1,
                    'punto'       => 'P' . ($orden + 1),
                    'coordenadaX' => $punto[0],
                    'coordenadaY' => $punto[1],
                ]);
            }

            $parcela->update([
                'lat' => array_sum($lats) / count($lats),
                'lng' => array_sum($lngs) / count($lngs),
            ]);
        });

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Polígono guardado correctamente.',
            'total'   => count($request->vertices),
        ]);
    }

    // ── API: BORRAR POLÍGONO ────────────────────────────────
    public function borrarPoligono($id)
    {
        $parcela = Parcela::where('idParcela', $id)->firstOrFail();
        Coordenada::where('idParcela', $parcela->idParcela)->delete();
        $parcela->update(['lat' => null, 'lng' => null]);

        return response()->json(['ok' => true, 'mensaje' => 'Polígono eliminado.']);
    }

    // ── FORMULARIO NUEVA PARCELA ─────────────────────────────
    public function create(Request $request)
    {
        $ejidatarios = Ejidatario::orderBy('nombre')->get();
        $usos        = Uso::all();
        $error       = null;
        $ejidatario  = null;

        if ($request->filled('numeroEjidatario')) {
            $ejidatario = Ejidatario::where('numeroEjidatario', $request->numeroEjidatario)->first();
            if (!$ejidatario) {
                $error = 'No se encontró un ejidatario con ese número.';
            }
        }

        return view('RegisterViews.nuevaParcela', compact('ejidatarios', 'usos', 'error', 'ejidatario'));
    }

    // ── GUARDAR NUEVA PARCELA ────────────────────────────────
    public function store(Request $request)
    {
        // 1. Validar campos obligatorios
        $request->validate([
            'numeroEjidatario' => 'required|numeric',
            'noParcela'        => 'required|numeric|min:1',
            'fechaExpedicion' => [
                'nullable',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {
                    if (!$value) return;
                    $year = (int) substr($value, 0, 4);
                    if ($year < 1900 || $year > 2100) {
                        $fail('El año de la fecha debe estar entre 1900 y 2100.');
                    }
                },
            ],
        ], [
            'numeroEjidatario.required' => 'Debes buscar un ejidatario antes de guardar.',
            'noParcela.required'        => 'El número de parcela es obligatorio.',
            'noParcela.numeric'         => 'El número de parcela debe ser un número.',
            'fechaExpedicion.after_or_equal'  => 'La fecha debe ser posterior a 1900.',
            'fechaExpedicion.before_or_equal' => 'La fecha no puede ser posterior al año 2100.',
        ]);

        // 2. Verificar que el ejidatario exista
        $ejidatario = Ejidatario::where('numeroEjidatario', $request->numeroEjidatario)->first();
        if (!$ejidatario) {
            return back()->withInput()
                         ->with('status', 'error')
                         ->with('mensaje', 'Ejidatario no encontrado.');
        }

        // 3. Verificar duplicidad: mismo noParcela para el mismo ejidatario
        $existe = Parcela::where('idEjidatario', $ejidatario->idEjidatario)
                         ->where('noParcela', $request->noParcela)
                         ->exists();

        if ($existe) {
            return back()->withInput()
                         ->with('status', 'error')
                         ->with('mensaje', "El ejidatario ya tiene registrada la parcela número {$request->noParcela}. Elige un número diferente.");
        }

        DB::transaction(function () use ($request, $ejidatario) {

            $lats = []; $lngs = [];
            if ($request->coordenadaX) {
                foreach ($request->coordenadaX as $i => $x) {
                    $y = $request->coordenadaY[$i] ?? null;
                    if ($x !== null && $x !== '' && $y !== null && $y !== '') {
                        $lat = (float) $x; $lng = (float) $y;
                        if ($lat >= 14 && $lat <= 33 && $lng >= -120 && $lng <= -85) {
                            $lats[] = $lat; $lngs[] = $lng;
                        }
                    }
                }
            }

            $parcela = Parcela::create([
                'noParcela'    => $request->noParcela,
                'superficie'   => $request->superficie,
                'ubicacion'    => $request->ubicacion,
                'idEjidatario' => $ejidatario->idEjidatario,
                'idUso'        => $request->usoSuelo,
                'lat'          => count($lats) ? array_sum($lats) / count($lats) : null,
                'lng'          => count($lngs) ? array_sum($lngs) / count($lngs) : null,
            ]);

            if (count($lats) >= 3) {
                $orden = 1;
                foreach ($request->coordenadaX as $i => $x) {
                    $y = $request->coordenadaY[$i] ?? null;
                    if ($x === null || $x === '' || $y === null || $y === '') continue;
                    $lat = (float) $x; $lng = (float) $y;
                    if ($lat < 14 || $lat > 33 || $lng < -120 || $lng > -85) continue;
                    Coordenada::create([
                        'idParcela'   => $parcela->idParcela,
                        'orden'       => $orden++,
                        'punto'       => $request->punto[$i] ?? ('P' . $orden),
                        'coordenadaX' => $lat,
                        'coordenadaY' => $lng,
                    ]);
                }
            }

            Colindancia::create([
                'idParcela' => $parcela->idParcela,
                'norte'     => $request->norte    ?: null,
                'sur'       => $request->sur      ?: null,
                'este'      => $request->este     ?: null,
                'oeste'     => $request->oeste    ?: null,
                'noreste'   => $request->noreste  ?: null,
                'noroeste'  => $request->noroeste ?: null,
                'sureste'   => $request->sureste  ?: null,
                'suroeste'  => $request->suroeste ?: null,
            ]);

            if ($request->filled('num_inscripcionRAN')) {
                InfAdmin::create([
                    'idParcela'          => $parcela->idParcela,
                    'num_inscripcionRAN' => $request->num_inscripcionRAN,
                    'claveNucleoAgrario' => $request->claveNucleoAgrario,
                    'comunidad'          => $request->comunidad,
                    'fechaExpedicion'    => $request->fechaExpedicion ?: null,
                ]);
            }
        });

        return redirect()->route('parcelas.mapa')
                         ->with('success', 'Parcela registrada. El polígono ya aparece en el mapa.');
    }

    // ── VER PARCELAS (ruta legacy) ───────────────────────────
    public function verParcela()
    {
        $parcelas = Parcela::with('ejidatario')->get();
        return view('ListViews.listadoParcelas', compact('parcelas'));
    }

    // ── FORMULARIO EDITAR ────────────────────────────────────
    public function editarParcela($id)
    {
        // Usar find() + comprobación manual para dar mensaje claro en vez de 500
        $parcela = Parcela::with(['ejidatario', 'coordenadas', 'infAdmin'])->where('idParcela', $id)->first();

        if (!$parcela) {
            return redirect()->route('parcelas.index')
                             ->with('error', "La parcela #{$id} no existe o ya fue eliminada.");
        }

        $ejidatarios = Ejidatario::orderBy('nombre')->get();
        $usos        = Uso::all();
        $ejidatario  = $parcela->ejidatario;
        $coordenadas = $parcela->coordenadas ?? collect();
        $col         = Colindancia::where('idParcela', $parcela->idParcela)->first();
        $colindancia = $col ? $col->toArray() : [];
        $infAdmin    = $parcela->infAdmin;

        return view('EditViews.editarParcela', compact(
            'parcela', 'ejidatario', 'ejidatarios', 'usos',
            'coordenadas', 'colindancia', 'infAdmin'
        ));
    }

    // ── ACTUALIZAR PARCELA ───────────────────────────────────
    public function actualizarParcela(Request $request)
    {
        // 1. Validar
        $request->validate([
            'idParcela'       => 'required|numeric',
            'noParcela'       => 'required|numeric|min:1',
            'fechaExpedicion' => [
                'nullable',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {
                    if (!$value) return;
                    $year = (int) substr($value, 0, 4);
                    if ($year < 1900 || $year > 2100) {
                        $fail('El año de la fecha debe estar entre 1900 y 2100.');
                    }
                },
            ],
        ], [
            'noParcela.required'              => 'El número de parcela es obligatorio.',
            'fechaExpedicion.after_or_equal'  => 'La fecha debe ser posterior a 1900.',
            'fechaExpedicion.before_or_equal' => 'La fecha no puede ser posterior al año 2100.',
        ]);

        $parcela = Parcela::where('idParcela', $request->idParcela)->first();

        if (!$parcela) {
            return redirect()->route('parcelas.index')
                             ->with('error', 'La parcela que intentas editar ya no existe.');
        }

        // 2. Verificar duplicidad (excluir la parcela actual)
        $existe = Parcela::where('idEjidatario', $parcela->idEjidatario)
                         ->where('noParcela', $request->noParcela)
                         ->where('idParcela', '!=', $parcela->idParcela)
                         ->exists();

        if ($existe) {
            return back()->withInput()
                         ->with('error', "El ejidatario ya tiene registrada la parcela número {$request->noParcela}.");
        }

        DB::transaction(function () use ($request, $parcela) {

            $parcela->update([
                'noParcela'  => $request->noParcela,
                'superficie' => $request->superficie,
                'ubicacion'  => $request->ubicacion,
                'idUso'      => $request->usoSuelo,
            ]);

            if ($request->coordenadas) {
                foreach ($request->coordenadas as $c) {
                    Coordenada::where('idCoordenada', $c['idCoordenada'])->update([
                        'punto'       => $c['punto'],
                        'coordenadaX' => $c['coordenadaX'],
                        'coordenadaY' => $c['coordenadaY'],
                    ]);
                }
                $coords = Coordenada::where('idParcela', $parcela->idParcela)->get();
                if ($coords->count() >= 3) {
                    $parcela->update([
                        'lat' => $coords->avg('coordenadaX'),
                        'lng' => $coords->avg('coordenadaY'),
                    ]);
                }
            }

            Colindancia::where('idParcela', $parcela->idParcela)->update([
                'norte'    => $request->norte,
                'sur'      => $request->sur,
                'este'     => $request->este,
                'oeste'    => $request->oeste,
                'noreste'  => $request->noreste,
                'noroeste' => $request->noroeste,
                'sureste'  => $request->sureste,
                'suroeste' => $request->suroeste,
            ]);

            // Info administrativa: actualizar o crear si no existe
            InfAdmin::updateOrCreate(
                ['idParcela' => $parcela->idParcela],
                [
                    'num_inscripcionRAN' => $request->num_inscripcionRAN,
                    'claveNucleoAgrario' => $request->claveNucleoAgrario,
                    'comunidad'          => $request->comunidad,
                    'fechaExpedicion'    => $request->fechaExpedicion ?: null,
                ]
            );
        });

        return redirect()->route('parcelas.index')
                         ->with('success', 'Parcela actualizada correctamente.');
    }

    // ── ELIMINAR PARCELA ─────────────────────────────────────
    public function destroy($id)
    {
        $parcela = Parcela::where('idParcela', $id)->first();

        if (!$parcela) {
            return redirect()->route('parcelas.index')
                             ->with('error', 'La parcela ya no existe.');
        }

        DB::transaction(function () use ($parcela) {
            Coordenada::where('idParcela', $parcela->idParcela)->delete();
            Colindancia::where('idParcela', $parcela->idParcela)->delete();
            InfAdmin::where('idParcela', $parcela->idParcela)->delete();
            $parcela->delete();
        });

        return redirect()->route('parcelas.index')
                         ->with('success', 'Parcela eliminada correctamente.');
    }

    // ── SHOW ─────────────────────────────────────────────────
    public function show($id)
    {
        $parcela = Parcela::with(['ejidatario', 'coordenadas', 'uso', 'infAdmin'])->where('idParcela', $id)->first();

        if (!$parcela) {
            return redirect()->route('parcelas.index')
                             ->with('error', "La parcela #{$id} no existe.");
        }

        return view('ListViews.verParcela', compact('parcela'));
    }

    // ── EDIT (ruta PUT /parcelas/{id}) ───────────────────────
    public function edit($id)
    {
        return $this->editarParcela($id);
    }

    public function update(Request $request, $id)
    {
        $request->merge(['idParcela' => $id]);
        return $this->actualizarParcela($request);
    }

    // ── ACTUALIZAR COORDENADAS (PATCH) ───────────────────────
    public function actualizarCoordenadas(Request $request, $parcela)
    {
        $p = Parcela::where('idParcela', $parcela)->firstOrFail();
        $p->update([
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);
        return response()->json(['ok' => true]);
    }

    // ── HELPER: GeoJSON ──────────────────────────────────────
    private function buildGeoJSON($parcelas): array
    {
        $features = [];

        foreach ($parcelas as $p) {
            $vertices = $p->vertices ?? [];

            if (count($vertices) < 3) continue;

            $geoCoords   = array_map(fn($v) => [$v[1], $v[0]], $vertices);
            $geoCoords[] = $geoCoords[0];

            $features[] = [
                'type'       => 'Feature',
                'geometry'   => [
                    'type'        => 'Polygon',
                    'coordinates' => [$geoCoords],
                ],
                'properties' => [
                    'id'         => $p->idParcela,
                    'folio'      => 'P-' . str_pad($p->noParcela, 3, '0', STR_PAD_LEFT),
                    'noParcela'  => $p->noParcela,
                    'ejidatario' => $p->ejidatario
                                    ? $p->ejidatario->nombre . ' ' . $p->ejidatario->apellidoPaterno
                                    : 'Sin asignar',
                    'superficie' => $p->superficie,
                    'uso'        => $p->uso->nombre ?? '—',
                    'estado'     => $p->estado ?? 'sin_regularizar',
                    'ubicacion'  => $p->ubicacion,
                ],
            ];
        }

        return ['type' => 'FeatureCollection', 'features' => $features];
    }
}