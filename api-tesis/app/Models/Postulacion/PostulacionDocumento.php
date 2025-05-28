<?php

namespace App\Models\Postulacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostulacionDocumento extends Model
{
    use HasFactory;

    protected $table = 'postulacion_documentos';

    protected $fillable = [
        'postulacion_id',
        'requisito_id',
        'nombre',
        'archivo',
        'es_requisito_ley',
        'es_requisito_personalizado',
    ];
/*
    protected $casts = [
        'es_requisito_ley' => 'boolean',
        'es_requisito_personalizado' => 'boolean',
    ];*/

    // Relación con Postulacion
    public function postulacion()
    {
        return $this->belongsTo(\App\Models\Postulacion\Postulacion::class);
    }
}