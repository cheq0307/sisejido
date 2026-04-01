<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Models\Ejidatario;
use App\Models\Uso;
use App\Models\Colindancia;
use App\Models\Coordenada;
use App\Models\InfAdmin;
use Illuminate\Http\Request;

class ParcelaController extends Controller
{
    // Listado
    public function index()
    {
        $parcelas = Parcela::with('ejidatario')->get();
        return view('ListViews.listadoParcelas', compact('parcelas'));
    }

    // Mapa Leaflet
    public function mapa()
    {
        $parcelas = Parcela::with(['ejidatario', 'coordenadasPuntos'])
            ->get()
            ->map(fn($p) => $p->toMapArray());

        $resumen = [
            'total'       => Parcela::count(),
            'ocupadas'    => Parcela::where('estado', 'ocupada')->count(),
            'disponibles' => Parcela::where('estado', 'disponible')->count(),
            'litigio'     => Parcela::where('estado', 'litigio')->count(),
            'inactivas'   => Parcela::where('estado', 'inactiva')->count(),
        ];

        return view('ListViews.mapaParcelas', [
            'parcelasJson' => $parcelas->toJson(),
            'resumen'      => $resumen,
        ]);
    }

    // Formulario nueva parcela + buscador ejidatario
    public function create(Request $request)
    {
        $usos       = Uso::all();           // tabla: tipousosuelo
        $error      = null;
        $ejidatario = null;

        if ($request->has('numeroEjidatario') && $request->numeroEjidatario !== null) {
            $ejidatario = Ejidatario::where('numeroEjidatario', $request->numeroEjidatario)->first();
            if (!$ejidatario) {
                $error = 'No se encontró un ejidatario con ese número.';
            }
        }

        return view('RegisterViews.nuevaParcela', compact('usos', 'error', 'ejidatario'));
    }

    // Guardar parcela completa
    public function store(Request $request)
    {
        $request->validate([
            'noParcela'        => 'required|integer',
            'superficie'       => 'required|string|max:30',
            'ubicacion'        => 'required|string|max:30',
            'numeroEjidatario' => 'required',
            'usoSuelo'         => 'required|exists:tipousosuelo,idUso',
        ]);

        $ejidatario = Ejidatario::where('numeroEjidatario', $request->numeroEjidatario)->first();
        if (!$ejidatario) {
            return back()->withInput()
                ->with('status', 'error')
                ->with('mensaje', 'Ejidatario no encontrado.');
        }

        // Crear parcela
        $parcela = Parcela::create([
            'noParcela'    => $request->noParcela,
            'superficie'   => $request->superficie,
            'ubicacion'    => $request->ubicacion,
            'idEjidatario' => $ejidatario->idEjidatario,
            'idUso'        => $request->usoSuelo,
        ]);

        // Colindancias
        if ($request->filled('norte')) {
            Colindancia::create([
                'idParcela' => $parcela->idParcela,
                'norte'     => $request->norte    ?? '',
                'sur'       => $request->sur      ?? '',
                'este'      => $request->este     ?? '',
                'oeste'     => $request->oeste    ?? '',
                'noreste'   => $request->noreste  ?? '',
                'noroeste'  => $request->noroeste ?? '',
                'sureste'   => $request->sureste  ?? '',
                'suroeste'  => $request->suroeste ?? '',
            ]);
        }

        // Coordenadas
        $puntos  = $request->input('punto', []);
        $coordsX = $request->input('coordenadaX', []);
        $coordsY = $request->input('coordenadaY', []);

        foreach ($puntos as $i => $punto) {
            if (!empty($coordsX[$i]) && !empty($coordsY[$i])) {
                Coordenada::create([
                    'idParcela'   => $parcela->idParcela,
                    'punto'       => $punto,
                    'coordenadaX' => $coordsX[$i],
                    'coordenadaY' => $coordsY[$i],
                ]);
            }
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

        return redirect()->route('parcelas.index')->with('status', 'success');
    }

    // Ver parcela por número
    public function verParcela(Request $request)
    {
        $parcela = null;

        if ($request->has('noParcela')) {
            $parcela = Parcela::with([
                'ejidatario', 'uso', 'colindancia',
                'coordenadasPuntos', 'infoAdministrativa'
            ])->where('noParcela', $request->noParcela)->first();

            if (!$parcela) {
                return back()->with('parcela_error', 'No se encontró la parcela con ese número.');
            }
        }

        return view('ListViews.listadoParcelas', compact('parcela'));
    }

    // Formulario editar
    public function editarParcela($id)
    {
        $parcela = Parcela::with(['colindancia', 'coordenadasPuntos', 'infoAdministrativa'])
            ->findOrFail($id);
        $usos = Uso::all();
        return view('EditViews.editarParcela', compact('parcela', 'usos'));
    }

    // Actualizar
    public function actualizarParcela(Request $request)
    {
        $parcela = Parcela::findOrFail($request->idParcela);

        $parcela->update([
            'noParcela'    => $request->noParcela,
            'superficie'   => $request->superficie,
            'ubicacion'    => $request->ubicacion,
            'idEjidatario' => $request->idEjidatario,
            'idUso'        => $request->usoSuelo,
        ]);

        if ($parcela->colindancia) {
            $parcela->colindancia->update([
                'norte'    => $request->norte    ?? '',
                'sur'      => $request->sur      ?? '',
                'este'     => $request->este     ?? '',
                'oeste'    => $request->oeste    ?? '',
                'noreste'  => $request->noreste  ?? '',
                'noroeste' => $request->noroeste ?? '',
                'sureste'  => $request->sureste  ?? '',
                'suroeste' => $request->suroeste ?? '',
            ]);
        }

        return redirect()->route('parcelas.index')->with('status', 'success');
    }

    // API JSON para Leaflet
    public function apiParcelas()
    {
        $parcelas = Parcela::with(['ejidatario', 'coordenadasPuntos'])
            ->get()
            ->map(fn($p) => $p->toMapArray());

        return response()->json($parcelas);
    }

    // API: guardar coordenadas desde el mapa
    public function actualizarCoordenadas(Request $request, $id)
    {
        $parcela = Parcela::findOrFail($id);
        $parcela->update([
            'lat'         => $request->lat,
            'lng'         => $request->lng,
            'coordenadas' => $request->coordenadas,
        ]);
        return response()->json(['success' => true, 'parcela' => $parcela->toMapArray()]);
    }
}
