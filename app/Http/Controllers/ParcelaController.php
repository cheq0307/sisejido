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

        // Array ya procesado para JS — evita closures dentro de @json en Blade
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

        return view('ListViews.mapaParcelas', compact('geojson', 'stats', 'parcelas', 'parcelasJs'));
    }

    // ── API: GeoJSON para AJAX ──────────────────────────────
    public function apiGeoJSON()
    {
        $parcelas = Parcela::with(['ejidatario', 'coordenadas', 'uso'])->get();
        return response()->json($this->buildGeoJSON($parcelas));
    }

    // ── API: GUARDAR VÉRTICES DEL POLÍGONO ──────────────────
    /**
     * Recibe los vértices dibujados en el mapa y los persiste.
     * Body JSON: { vertices: [[lat, lng], [lat, lng], ...] }
     */
    public function guardarPoligono(Request $request, $id)
    {
        $request->validate([
            'vertices'   => 'required|array|min:3',
            'vertices.*' => 'array|size:2',
        ]);

        $parcela = Parcela::where('idParcela', $id)->firstOrFail();

        DB::transaction(function () use ($parcela, $request) {
            // Borrar vértices previos
            Coordenada::where('idParcela', $parcela->idParcela)->delete();

            // Calcular centroide para lat/lng de la parcela
            $lats = array_column($request->vertices, 0);
            $lngs = array_column($request->vertices, 1);

            // Insertar nuevos vértices
            foreach ($request->vertices as $orden => $punto) {
                Coordenada::create([
                    'idParcela'   => $parcela->idParcela,
                    'orden'       => $orden + 1,
                    'punto'       => 'P' . ($orden + 1),
                    'coordenadaX' => $punto[0],  // LAT
                    'coordenadaY' => $punto[1],  // LNG
                ]);
            }

            // Actualizar centroide en la parcela
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

        if ($request->has('numeroEjidatario') && $request->numeroEjidatario !== null) {
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
        // Buscar ejidatario por número
        $ejidatario = Ejidatario::where('numeroEjidatario', $request->numeroEjidatario)->first();
        if (!$ejidatario) {
            return back()->withInput()
                         ->with('status', 'error')
                         ->with('mensaje', 'Ejidatario no encontrado.');
        }

        DB::transaction(function () use ($request, $ejidatario) {

            // Calcular centroide si hay coordenadas válidas
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

            // Crear parcela
            $parcela = Parcela::create([
                'noParcela'    => $request->noParcela,
                'superficie'   => $request->superficie,
                'ubicacion'    => $request->ubicacion,
                'idEjidatario' => $ejidatario->idEjidatario,
                'idUso'        => $request->usoSuelo,
                'lat'          => count($lats) ? array_sum($lats) / count($lats) : null,
                'lng'          => count($lngs) ? array_sum($lngs) / count($lngs) : null,
            ]);

            // Guardar coordenadas GPS
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

            // Colindancias
            if ($request->filled('norte')) {
                Colindancia::create([
                    'idParcela' => $parcela->idParcela,
                    'norte'     => $request->norte,
                    'sur'       => $request->sur,
                    'este'      => $request->este,
                    'oeste'     => $request->oeste,
                    'noreste'   => $request->noreste,
                    'noroeste'  => $request->noroeste,
                    'sureste'   => $request->sureste,
                    'suroeste'  => $request->suroeste,
                ]);
            }

            // Info administrativa
            if ($request->filled('num_inscripcionRAN')) {
                InfAdmin::create([
                    'idParcela'          => $parcela->idParcela,
                    'num_inscripcionRAN' => $request->num_inscripcionRAN,
                    'claveNucleoAgrario' => $request->claveNucleoAgrario,
                    'comunidad'          => $request->comunidad,
                    'fechaExpedicion'    => $request->fechaExpedicion,
                ]);
            }
        });

        return redirect()->route('parcelas.mapa')
                         ->with('success', 'Parcela registrada. El polígono ya aparece en el mapa.');
    }

    // ── VER PARCELAS (ruta legacy /verParcela) ───────────────
    public function verParcela()
    {
        $parcelas = Parcela::with('ejidatario')->get();
        return view('ListViews.listadoParcelas', compact('parcelas'));
    }

    // ── EDITAR ───────────────────────────────────────────────
    public function editarParcela($id)
    {
        $parcela     = Parcela::with(['ejidatario', 'coordenadas', 'infAdmin'])->where('idParcela', $id)->firstOrFail();
        $ejidatarios = Ejidatario::orderBy('nombre')->get();
        $usos        = Uso::all();

        // Ejidatario de la parcela
        $ejidatario  = $parcela->ejidatario;

        // Coordenadas ordenadas
        $coordenadas = $parcela->coordenadas;

        // Colindancias como array asociativo para la vista
        $col = Colindancia::where('idParcela', $parcela->idParcela)->first();
        $colindancia = $col ? $col->toArray() : [];

        // Info administrativa
        $infAdmin = $parcela->infAdmin;

        return view('EditViews.editarParcela', compact(
            'parcela', 'ejidatario', 'ejidatarios', 'usos',
            'coordenadas', 'colindancia', 'infAdmin'
        ));
    }

    public function actualizarParcela(Request $request)
    {
        $parcela = Parcela::where('idParcela', $request->idParcela)->firstOrFail();

        DB::transaction(function () use ($request, $parcela) {

            // Actualizar datos básicos
            $parcela->update([
                'noParcela'  => $request->noParcela,
                'superficie' => $request->superficie,
                'ubicacion'  => $request->ubicacion,
                'idUso'      => $request->usoSuelo,
            ]);

            // Actualizar coordenadas
            if ($request->coordenadas) {
                foreach ($request->coordenadas as $c) {
                    Coordenada::where('idCoordenada', $c['idCoordenada'])->update([
                        'punto'       => $c['punto'],
                        'coordenadaX' => $c['coordenadaX'],
                        'coordenadaY' => $c['coordenadaY'],
                    ]);
                }
                // Recalcular centroide
                $coords = Coordenada::where('idParcela', $parcela->idParcela)->get();
                if ($coords->count() >= 3) {
                    $parcela->update([
                        'lat' => $coords->avg('coordenadaX'),
                        'lng' => $coords->avg('coordenadaY'),
                    ]);
                }
            }

            // Actualizar colindancias
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

            // Actualizar info administrativa
            InfAdmin::where('idParcela', $parcela->idParcela)->update([
                'num_inscripcionRAN' => $request->num_inscripcionRAN,
                'claveNucleoAgrario' => $request->claveNucleoAgrario,
                'comunidad'          => $request->comunidad,
                'fechaExpedicion'    => $request->fechaExpedicion,
            ]);
        });

        return redirect()->route('parcelas.index')
                         ->with('success', 'Parcela actualizada correctamente.');
    }


    // ── ELIMINAR PARCELA ─────────────────────────────────────
    public function destroy($id)
    {
        $parcela = Parcela::where('idParcela', $id)->firstOrFail();

        DB::transaction(function () use ($parcela) {
            // Eliminar datos relacionados
            Coordenada::where('idParcela', $parcela->idParcela)->delete();
            Colindancia::where('idParcela', $parcela->idParcela)->delete();
            InfAdmin::where('idParcela', $parcela->idParcela)->delete();
            $parcela->delete();
        });

        return redirect()->route('parcelas.index')
                         ->with('success', 'Parcela eliminada correctamente.');
    }
    // ── HELPER: construir GeoJSON ────────────────────────────
    private function buildGeoJSON($parcelas): array
    {
        $features = [];

        foreach ($parcelas as $p) {
            $vertices = $p->vertices; // [[lat,lng], ...]

            if (count($vertices) < 3) {
                continue; // sin polígono, no se incluye
            }

            // GeoJSON usa [lng, lat] — invertir
            $geoCoords = array_map(fn ($v) => [$v[1], $v[0]], $vertices);
            $geoCoords[] = $geoCoords[0]; // cerrar el anillo

            $features[] = [
                'type'       => 'Feature',
                'geometry'   => [
                    'type'        => 'Polygon',
                    'coordinates' => [$geoCoords],
                ],
                'properties' => [
                    'id'          => $p->idParcela,
                    'folio'       => 'P-' . str_pad($p->noParcela, 3, '0', STR_PAD_LEFT),
                    'noParcela'   => $p->noParcela,
                    'ejidatario'  => $p->ejidatario
                                        ? $p->ejidatario->nombre . ' ' . $p->ejidatario->apellidoPaterno
                                        : 'Sin asignar',
                    'superficie'  => $p->superficie,
                    'uso'         => $p->uso->nombre ?? '—',
                    'estado'      => $p->estado ?? 'sin_regularizar',
                    'ubicacion'   => $p->ubicacion,
                ],
            ];
        }

        return [
            'type'     => 'FeatureCollection',
            'features' => $features,
        ];
    }
}