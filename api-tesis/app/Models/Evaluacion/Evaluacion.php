<?php
namespace App\Models\Evaluacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluacion extends Model
{
    use SoftDeletes;
    protected $table = 'evaluaciones';
    protected $fillable = [
        'postulacion_id',
        'evaluador_id',
        'puntaje_total',
        'comentarios',
        'estado'
    ];

    protected $casts = [
        'puntaje_total' => 'decimal:2',
    ];

    public function postulacion()
    {
        return $this->belongsTo(Postulacion::class);
    }

    public function evaluador()
    {
        return $this->belongsTo(User::class, 'evaluador_id');
    }

    public function documentos()
    {
        return $this->hasMany(EvaluacionDocumento::class);
    }
}