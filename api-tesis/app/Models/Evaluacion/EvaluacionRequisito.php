<?php
namespace App\Models\Evaluacion;

use Illuminate\Database\Eloquent\Model;

class EvaluacionRequisito extends Model
{
    
    protected $table = 'evaluacion_requisitos';
    protected $fillable = [
    'evaluacion_id',
    'requisito_id',
    'estado',
    'comentarios',
    'es_requisito_ley',
    'postulacion_documento_id'
];
    protected $casts = [
        'es_requisito_ley' => 'boolean',
    ];
public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class);
    }

    public function requisito()
    {
        return $this->belongsTo(Requisito::class);
    }
}

