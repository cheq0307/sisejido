<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApoyoSocial extends Model
{
    public $timestamps = false;
    protected $table = 'apoyos_sociales';
    protected $primaryKey = 'idApoyo';

    protected $fillable = [
        'idEjidatario',
        'tipo_apoyo',
        'descripcion',
        'monto',
        'cantidad',
        'unidad_medida',
        'fecha_entrega',
        'ciclo',
        'dependencia',
        'representante_dependencia', // ← campo nuevo
        'nombre_representante',
        'num_beneficiarios',
        'estatus',
        'observaciones',
    ];

    public function ejidatario()
    {
        return $this->belongsTo(Ejidatario::class, 'idEjidatario', 'idEjidatario');
    }
}