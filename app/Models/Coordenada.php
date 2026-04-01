<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Coordenada extends Model {
    protected $table      = 'coordenada';
    protected $primaryKey = 'idCoordenada';
    public    $timestamps = false;
    protected $fillable   = ['idParcela','punto','coordenadaX','coordenadaY'];
}
