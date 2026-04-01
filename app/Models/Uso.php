<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Uso extends Model {
    protected $table      = 'tipousosuelo';
    protected $primaryKey = 'idUso';
    public    $timestamps = false;
    protected $fillable   = ['nombre'];
}
