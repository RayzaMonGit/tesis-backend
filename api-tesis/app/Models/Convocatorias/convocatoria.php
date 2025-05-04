<?php

namespace App\Models\Convocatorias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Convocatorias\requisitos;

class convocatoria extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'estado',
        'titulo',
        'descripcion',
        'area',
        'fecha_inicio',
        'fecha_fin',
        'plazas_disponibles',
        'sueldo_referencial',
        'documento',
        'requisitos_id',
    ];

    public function requisitos(){
        return $this->hasMany(Requisitos::class,"requisitos_id");
        
    }
    
}
