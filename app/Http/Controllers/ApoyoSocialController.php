<?php

namespace App\Http\Controllers;

use App\Models\ApoyoSocial;
use App\Models\Ejidatario;
use Illuminate\Http\Request;

class ApoyoSocialController extends Controller
{
    // ── Reglas ────────────────────────────────────────────────────────────────
    private function rules(): array
    {
        return [
            'idEjidatario'         => 'required|exists:ejidatarios,idEjidatario',
            'tipo_apoyo'           => 'required|string|max:100',
            'descripcion'          => 'required|string|max:500',
            'fecha_entrega'        => 'required|date_format:Y-m-d|before_or_equal:2100-12-31',
            'monto'                => 'nullable|numeric|min:0',   // opcional: apoyo puede ser en especie
            'cantidad'             => 'required|integer|min:0',
            'unidad_medida'        => 'required|string|max:50',
            'ciclo'                => 'nullable|string|max:20',   // opcional
            'estatus'              => 'required|in:entregado,pendiente,cancelado,aprobado',
            'dependencia'          => 'required|string|max:100',
            'nombre_representante' => 'required|string|max:100',
            'num_beneficiarios'    => 'required|integer|min:1',
            'observaciones'        => 'required|string|max:1000',
        ];
    }

    // ── Mensajes en español ───────────────────────────────────────────────────
    private function messages(): array
    {
        return [
            'idEjidatario.required'         => 'Selecciona un ejidatario.',
            'idEjidatario.exists'           => 'El ejidatario seleccionado no existe.',
            'tipo_apoyo.required'           => 'El tipo de apoyo es obligatorio.',
            'descripcion.required'          => 'La descripción es obligatoria.',
            'fecha_entrega.required'        => 'La fecha de entrega es obligatoria.',
            'fecha_entrega.date_format'     => 'La fecha debe tener el formato DD/MM/AAAA.',
            'fecha_entrega.before_or_equal' => 'La fecha no puede ser posterior al año 2100.',
            'monto.numeric'                 => 'El monto debe ser un número.',
            'monto.min'                     => 'El monto no puede ser negativo.',
            'cantidad.required'             => 'La cantidad es obligatoria.',
            'cantidad.integer'              => 'La cantidad debe ser un número entero.',
            'unidad_medida.required'        => 'La unidad de medida es obligatoria.',
            'estatus.required'              => 'El estatus es obligatorio.',
            'estatus.in'                    => 'El estatus seleccionado no es válido.',
            'dependencia.required'          => 'La dependencia o institución es obligatoria.',
            'nombre_representante.required' => 'El nombre del representante es obligatorio.',
            'num_beneficiarios.required'    => 'El número de beneficiarios es obligatorio.',
            'num_beneficiarios.min'         => 'Debe haber al menos 1 beneficiario.',
            'observaciones.required'        => 'Las observaciones son obligatorias.',
        ];
    }

    // ── Index ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $apoyos = ApoyoSocial::with('ejidatario')
            ->orderBy('fecha_entrega', 'desc')
            ->get();

        return view('ListViews.listadoApoyos', compact('apoyos'));
    }

    // ── Create ────────────────────────────────────────────────────────────────
    public function create()
    {
        $ejidatarios = Ejidatario::where('idEstatus', 1)
            ->orderBy('apellidoPaterno')
            ->get();

        return view('RegisterViews.nuevoApoyo', compact('ejidatarios'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        ApoyoSocial::create($validated);

        return redirect()->route('apoyos.index')
            ->with('success', 'Apoyo registrado correctamente.');
    }

    // ── Edit ──────────────────────────────────────────────────────────────────
    public function edit($id)
    {
        $apoyo       = ApoyoSocial::findOrFail($id);
        $ejidatarios = Ejidatario::where('idEstatus', 1)
            ->orderBy('apellidoPaterno')
            ->get();

        return view('EditViews.editarApoyo', compact('apoyo', 'ejidatarios'));
    }

    // ── Update ────────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $apoyo     = ApoyoSocial::findOrFail($id);
        $validated = $request->validate($this->rules(), $this->messages());

        $apoyo->update($validated);

        return redirect()->route('apoyos.index')
            ->with('success', 'Apoyo actualizado correctamente.');
    }

    // ── Reporte ───────────────────────────────────────────────────────────────
    public function reporte(Request $request)
    {
        $query = ApoyoSocial::with('ejidatario');

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }
        if ($request->filled('tipo_apoyo')) {
            $query->where('tipo_apoyo', 'like', '%' . $request->tipo_apoyo . '%');
        }
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_entrega', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_entrega', '<=', $request->fecha_hasta);
        }

        $apoyos = $query->orderBy('fecha_entrega', 'desc')->get();

        return view('ReportViews.reporteApoyos', compact('apoyos'));
    }

    // ── Destroy ───────────────────────────────────────────────────────────────
    public function destroy($id)
    {
        ApoyoSocial::findOrFail($id)->delete();

        return redirect()->route('apoyos.index')
            ->with('success', 'Apoyo eliminado.');
    }
}