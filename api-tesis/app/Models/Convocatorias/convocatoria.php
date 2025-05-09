<?php

namespace App\Models\Convocatorias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use App\Models\Convocatorias\requisitos;
use App\Models\Convocatorias\Requisitos;

class Convocatoria extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = 'convocatorias';
    protected $fillable = [
        'id',
        'estado',
        'titulo',
        'descripcion',
        'area',
        'fecha_inicio',
        'fecha_fin',
        'plazas_disponibles',
        'sueldo_referencial',
        'documento',
    ];

    public function requisitos() {
        return $this->hasMany(Requisitos::class, 'id_convocatoria', 'id');
    }
    
}
