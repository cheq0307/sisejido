<?php

namespace App\Http\Controllers;

use App\Models\ApoyoSocial;
use App\Models\Ejidatario;
use Illuminate\Http\Request;

class ApoyoSocialController extends Controller
{
    public function index()
    {
        $apoyos = ApoyoSocial::with('ejidatario')->orderBy('fecha_entrega', 'desc')->get();
        return view('ListViews.listadoApoyos', compact('apoyos'));
    }

    public function create()
    {
        $ejidatarios = Ejidatario::where('idEstatus', 1)->orderBy('apellidoPaterno')->get();
        return view('RegisterViews.nuevoApoyo', compact('ejidatarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'idEjidatario'        => 'required|exists:ejidatarios,idEjidatario',
            'tipo_apoyo'          => 'required|string|max:100',
            'fecha_entrega'       => ['required','date_format:Y-m-d','before_or_equal:2100-12-31',],
            'nombre_representante'=> 'required|string|max:100',
            'monto'               => 'required|numeric|min:0',
            'cantidad'            => 'required|integer|min:0',
            'num_beneficiarios'   => 'required|integer|min:1',
            'estatus'             => 'required|in:entregado,pendiente,cancelado,aprobado',
        ]);

        ApoyoSocial::create($request->all());

        return redirect()->route('apoyos.index')
                         ->with('success', 'Apoyo registrado correctamente.');
    }

    public function edit($id)
    {
        $apoyo = ApoyoSocial::findOrFail($id);
        $ejidatarios = Ejidatario::where('idEstatus', 1)->orderBy('apellidoPaterno')->get();
        return view('EditViews.editarApoyo', compact('apoyo', 'ejidatarios'));
    }

    public function update(Request $request, $id)
    {
        $apoyo = ApoyoSocial::findOrFail($id);

        $request->validate([
            'idEjidatario'        => 'required|exists:ejidatarios,idEjidatario',
            'tipo_apoyo'          => 'required|string|max:100',
                'fecha_entrega' => [
        'required',
        'date_format:Y-m-d',
        'before_or_equal:2100-12-31',
    ],
            'nombre_representante'=> 'required|string|max:100',
            'monto'               => 'required|numeric|min:0',
            'cantidad'            => 'required|integer|min:0',
            'num_beneficiarios'   => 'required|integer|min:1',
            'estatus'             => 'required|in:entregado,pendiente,cancelado,aprobado',
        ]);

        $apoyo->update($request->all());

        return redirect()->route('apoyos.index')
                         ->with('success', 'Apoyo actualizado correctamente.');
    }
    public function reporte(Request $request)
{
    $query = ApoyoSocial::with('ejidatario');

    if ($request->estatus) {
        $query->where('estatus', $request->estatus);
    }
    if ($request->tipo_apoyo) {
        $query->where('tipo_apoyo', 'like', '%' . $request->tipo_apoyo . '%');
    }
    if ($request->fecha_desde) {
        $query->where('fecha_entrega', '>=', $request->fecha_desde);
    }
    if ($request->fecha_hasta) {
        $query->where('fecha_entrega', '<=', $request->fecha_hasta);
    }

    $apoyos = $query->orderBy('fecha_entrega', 'desc')->get();
    return view('ReportViews.reporteApoyos', compact('apoyos'));
}

    public function destroy($id)
    {
        ApoyoSocial::findOrFail($id)->delete();
        return redirect()->route('apoyos.index')
                         ->with('success', 'Apoyo eliminado.');
    }
}