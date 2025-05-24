<?php

namespace App\Models\Postulacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostulacionDocumento extends Model
{
    use HasFactory;

    protected $fillable = [
        'postulacion_id',
        'nombre',
        'archivo',
        'es_requisito_ley',
        'es_requisito_personalizado',
    ];

    public function postulacion()
    {
        return $this->belongsTo(Postulacion::class);
    }
}
