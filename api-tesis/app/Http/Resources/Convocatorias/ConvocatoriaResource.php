<?php

namespace App\Http\Resources\Convocatorias;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConvocatoriaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            "estado" => $this->resource->estado,
            "titulo" => $this->resource->titulo,
            "descripcion" => $this->resource->descripcion,
            "area" => $this->resource->area,
            "fecha_inicio" => $this->resource->fecha_inicio,
            "fecha_fin" => $this->resource->fecha_fin,
            "plazas_disponibles" => $this->resource->plazas_disponibles,
            "sueldo_referencial" => $this->resource->sueldo_referencial,
            "documento" => env("APP_URL")."/storage/".$this->resource->documento,
            "requisitos_id" => $this->resource->requisitos_id,
            "requisitos" => [
                "descripcion" => $this->resource->requisitos->descripcion,
                "tipo" => $this->resource->requisitos->tipo,
            ],
        ];
    }
}
