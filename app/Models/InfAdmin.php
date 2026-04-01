<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InfAdmin extends Model {
    protected $table      = 'infadmin';
    protected $primaryKey = 'id_InfAdmin';
    public    $timestamps = false;
    protected $fillable   = [
        'num_inscripcionRAN','claveNucleoAgrario',
        'comunidad','fechaExpedicion','idParcela'
    ];
}
