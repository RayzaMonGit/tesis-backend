<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CriterioFormulario extends Model
{
    protected $table = 'criterios_formulario';

    protected $fillable = [
        'seccion_id',
        'nombre',
        'puntaje',
        'max_items',
        'orden',
    ];

    public function seccion()
    {
        return $this->belongsTo(SeccionFormulario::class, 'seccion_id');
    }
}
