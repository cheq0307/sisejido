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
        $validated = $request->validate([
            'noParcela'    => 'required|integer',
            'superficie'   => 'required|string|max:30',
            'ubicacion'    => 'required|string|max:30',
            'idEjidatario' => 'required|exists:ejidatarios,idEjidatario',
            'idUso'        => 'required|exists:tipousosuelo,idUso',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $parcela = Parcela::create($validated);

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
                    'idParcela'           => $parcela->idParcela,
                    'num_inscripcionRAN'  => $request->num_inscripcionRAN,
                    'claveNucleoAgrario'  => $request->claveNucleoAgrario,
                    'comunidad'           => $request->comunidad,
                    'fechaExpedicion'     => $request->fechaExpedicion,
                ]);
            }
        });

        return redirect()->route('parcelas.index')
                         ->with('success', 'Parcela registrada correctamente.');
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
        $parcela     = Parcela::with(['ejidatario', 'colindancias', 'infAdmin'])->where('idParcela', $id)->firstOrFail();
        $ejidatarios = Ejidatario::orderBy('nombre')->get();
        $usos        = Uso::all();
        return view('EditViews.editarParcela', compact('parcela', 'ejidatarios', 'usos'));
    }

    public function actualizarParcela(Request $request)
    {
        $parcela = Parcela::where('idParcela', $request->idParcela)->firstOrFail();
        $parcela->update($request->only([
            'noParcela', 'superficie', 'ubicacion', 'idEjidatario', 'idUso',
        ]));

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