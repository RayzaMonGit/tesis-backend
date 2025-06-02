<?php
namespace App\Http\Controllers\Evaluacion;

use App\Models\Evaluacion\{Evaluacion, EvaluacionDocumento};
//use App\Http\Requests\StoreEvaluacionDocumentoRequest;
//use App\Http\Resources\EvaluacionDocumentoResource;

class EvaluDocumentoController extends Controller
{
    public function store(StoreEvaluacionDocumentoRequest $request, Evaluacion $evaluacion)
    {
        $documento = $evaluacion->documentos()->create($request->validated());

        // Actualizar puntaje total
        $evaluacion->update([
            'puntaje_total' => $evaluacion->documentos()->sum('puntaje')
        ]);

        return new EvaluacionDocumentoResource($documento->load('documento'));
    }

    public function update(StoreEvaluacionDocumentoRequest $request, EvaluacionDocumento $documento)
    {
        $documento->update($request->validated());

        // Actualizar puntaje total
        $documento->evaluacion->update([
            'puntaje_total' => $documento->evaluacion->documentos()->sum('puntaje')
        ]);

        return new EvaluacionDocumentoResource($documento->fresh()->load('documento'));
    }
}