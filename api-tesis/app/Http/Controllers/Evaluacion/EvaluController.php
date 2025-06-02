<?php
namespace App\Http\Controllers\Evaluacion;

use App\Models\Evaluacion\{Evaluacion, Postulacion};
//use App\Http\Requests\StoreEvaluacionRequest;
//use App\Http\Resources\EvaluacionResource;

class EvaluDocumentoController extends Controller
{
    public function store(StoreEvaluacionDocumentoRequest $request, $evaluacionId)
    {
        $evaluacion = Evaluacion::findOrFail($evaluacionId);
        $documento = PostulacionDocumento::findOrFail($request->postulacion_documento_id);

        $evalDocumento = $evaluacion->documentos()->create([
            'postulacion_documento_id' => $documento->id,
            'estado' => $request->estado,
            'puntaje' => $request->puntaje,
            'comentarios' => $request->comentarios
        ]);

        $this->actualizarPuntajeTotal($evaluacion);

        return new EvaluacionDocumentoResource($evalDocumento->load('documento'));
    }

    public function update(StoreEvaluacionDocumentoRequest $request, $id)
    {
        $evalDocumento = EvaluacionDocumento::with('evaluacion')->findOrFail($id);
        $evalDocumento->update($request->validated());

        $this->actualizarPuntajeTotal($evalDocumento->evaluacion);

        return new EvaluacionDocumentoResource($evalDocumento->fresh()->load('documento'));
    }

    public function destroy($id)
    {
        $evalDocumento = EvaluacionDocumento::with('evaluacion')->findOrFail($id);
        $evalDocumento->delete();

        $this->actualizarPuntajeTotal($evalDocumento->evaluacion);

        return response()->json(['message' => 'Documento de evaluación eliminado']);
    }

    private function actualizarPuntajeTotal(Evaluacion $evaluacion)
    {
        $evaluacion->update([
            'puntaje_total' => $evaluacion->documentos()->sum('puntaje')
        ]);
    }
}