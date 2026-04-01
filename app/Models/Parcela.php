<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    protected $table      = 'parcelas';
    protected $primaryKey = 'idParcela';
    public    $timestamps = false;

    protected $fillable = [
        'noParcela',
        'superficie',
        'ubicacion',
        'idEjidatario',
        'idUso',
        // Columnas del mapa (agregadas con la migración)
        'estado',
        'cultivo',
        'tipo_agua',
        'coordenadas',
        'lat',
        'lng',
    ];

    protected $casts = [
        'coordenadas' => 'array',
        'lat'         => 'float',
        'lng'         => 'float',
    ];

    // ── Relaciones ──────────────────────────────────────────

    public function ejidatario()
    {
        return $this->belongsTo(Ejidatario::class, 'idEjidatario', 'idEjidatario');
    }

    public function uso()
    {
        return $this->belongsTo(Uso::class, 'idUso', 'idUso');
    }

    // tabla: coordenada (sin 's')
    public function coordenadasPuntos()
    {
        return $this->hasMany(Coordenada::class, 'idParcela', 'idParcela');
    }

    // tabla: colindancia (sin 's')
    public function colindancia()
    {
        return $this->hasOne(Colindancia::class, 'idParcela', 'idParcela');
    }

    // tabla: infadmin
    public function infoAdministrativa()
    {
        return $this->hasOne(InfAdmin::class, 'idParcela', 'idParcela');
    }

    // ── Scope por estado ────────────────────────────────────

    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    // ── Color para el mapa según estado ─────────────────────

    public function getColorMapaAttribute(): string
    {
        return match($this->estado ?? 'inactiva') {
            'ocupada'    => '#1D9E75',
            'disponible' => '#378ADD',
            'litigio'    => '#E24B4A',
            'inactiva'   => '#888780',
            default      => '#888780',
        };
    }

    // ── Array para Leaflet ───────────────────────────────────

    public function toMapArray(): array
    {
        // Usar coordenadas JSON si existen, si no usar tabla coordenada
        $coords = $this->coordenadas;
        if (empty($coords) && $this->relationLoaded('coordenadasPuntos')) {
            $coords = $this->coordenadasPuntos->map(fn($c) => [
                (float) $c->coordenadaX,
                (float) $c->coordenadaY,
            ])->toArray();
        }

        $nombreEjidatario = $this->ejidatario
            ? $this->ejidatario->nombre . ' ' .
              $this->ejidatario->apellidoPaterno . ' ' .
              $this->ejidatario->apellidoMaterno
            : 'Sin asignar';

        return [
            'id'          => $this->idParcela,
            'clave'       => $this->noParcela,
            'ejidatario'  => $nombreEjidatario,
            'superficie'  => $this->superficie,
            'ubicacion'   => $this->ubicacion,
            'estado'      => $this->estado ?? 'inactiva',
            'cultivo'     => $this->cultivo ?? '—',
            'tipo_agua'   => $this->tipo_agua ?? '—',
            'color'       => $this->color_mapa,
            'coordenadas' => $coords ?? [],
            'lat'         => $this->lat,
            'lng'         => $this->lng,
        ];
    }
}
