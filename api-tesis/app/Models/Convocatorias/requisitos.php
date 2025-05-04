<?php

namespace App\Models\Convocatorias;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Convocatorias\Convocatoria;

class requisitos extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = 'requisitos';
    protected $fillable = [
        'id_convocatoria',
        'descripcion',
        'tipo',
    ];

    public function convocatoria()
{
    return $this->belongsTo(Convocatoria::class, 'id_convocatoria');
}
}
