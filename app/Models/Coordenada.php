<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coordenada extends Model
{
    protected $table      = 'coordenada';
    protected $primaryKey = 'idCoordenada';
    public    $timestamps = false;

    protected $fillable = [
        'idParcela',
        'orden',
        'punto',
        'coordenadaX',   // LAT  (eje X geográfico, norte-sur)
        'coordenadaY',   // LNG  (eje Y geográfico, este-oeste)
    ];

    protected $casts = [
        'coordenadaX' => 'float',
        'coordenadaY' => 'float',
        'orden'       => 'integer',
    ];

    // ── Relación ────────────────────────────────────────────
    public function parcela()
    {
        return $this->belongsTo(Parcela::class, 'idParcela', 'idParcela');
    }
}