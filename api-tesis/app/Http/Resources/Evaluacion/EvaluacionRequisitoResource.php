<?php
namespace App\Http\Resources\Evaluacion;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluacionRequisitoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'evaluacion_id' => $this->evaluacion_id,
            'requisito_id' => $this->requisito_id,
            'estado' => $this->estado,
            'comentarios' => $this->comentarios,
            'es_requisito_ley' => $this->es_requisito_ley,
        ];
    }


    public function with($request)
    {
        return [
            'success' => true,
            'message' => 'Requisito evaluado correctamente',
        ];
    }
}