<?php

namespace App\Models\formulario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormularioEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'formularios_evaluacion';

    protected $fillable = [
        'nombre',
        'descripcion',
        'resolucion',
        'puntaje_total',
    ];

    public function secciones()
    {
        return $this->hasMany(SeccionFormulario::class, 'formulario_id');
    }
}
