<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    protected $table      = 'parcelas';
    protected $primaryKey = 'idParcela';
    public    $timestamps = false;

    protected $fillable = [
        'noParcela', 'superficie', 'ubicacion',
        'idEjidatario', 'idUso',
        'estado', 'cultivo', 'tipo_agua',
        'coordenadas',          // JSON legacy (puede quedar vacío)
        'lat', 'lng',           // centroide para zoom rápido
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

    public function colindancias()
    {
        return $this->hasMany(Colindancia::class, 'idParcela', 'idParcela');
    }

    /**
     * Vértices del polígono, siempre ordenados por `orden` ASC.
     * Cada vértice: coordenadaX = LAT, coordenadaY = LNG.
     */
    public function coordenadas()
    {
        return $this->hasMany(Coordenada::class, 'idParcela', 'idParcela')
                    ->orderBy('orden');
    }

    public function infAdmin()
    {
        return $this->hasOne(InfAdmin::class, 'idParcela', 'idParcela');
    }

    // ── Helpers ─────────────────────────────────────────────

    /**
     * Devuelve los vértices como array [[lat, lng], ...]
     * listo para JSON y Leaflet.
     */
    public function getVerticesAttribute(): array
    {
        $coords = $this->relationLoaded('coordenadas')
            ? $this->getRelation('coordenadas')
            : $this->coordenadas()->orderBy('orden')->get();

        return ($coords ?? collect())
            ->map(fn ($c) => [(float) $c->coordenadaX, (float) $c->coordenadaY])
            ->toArray();
    }

    /**
     * ¿Tiene polígono dibujado?
     */
    public function tienePoligono(): bool
    {
        return $this->coordenadas()->count() >= 3;
    }
}