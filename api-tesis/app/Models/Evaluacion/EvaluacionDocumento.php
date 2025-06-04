<?php
namespace App\Models\Evaluacion;

use Illuminate\Database\Eloquent\Model;

class EvaluacionDocumento extends Model
{
    protected $fillable = [
        'id',
        'evaluacion_id',
        'postulacion_documento_id',
        'estado',
        'puntaje',
        'comentarios'
    ];

    protected $casts = [
        'puntaje' => 'decimal:2',
    ];

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class);
    }

    public function documento()
    {
        return $this->belongsTo(PostulacionDocumento::class, 'postulacion_documento_id');
    }
}