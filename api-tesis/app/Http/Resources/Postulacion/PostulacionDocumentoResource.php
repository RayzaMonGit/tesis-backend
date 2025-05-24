<?php

namespace App\Http\Resources\Postulacion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostulacionDocumentoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'postulacion_id' => $this->postulacion_id,
            'nombre' => $this->nombre,
            'archivo' => Storage::url($this->archivo), // genera URL pública si usás storage:link
            'es_requisito_ley' => $this->es_requisito_ley,
            'es_requisito_personalizado' => $this->es_requisito_personalizado,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
