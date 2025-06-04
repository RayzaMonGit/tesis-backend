<?php
namespace App\Http\Controllers\Evaluacion;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Postulacion\Postulacion;
use Illuminate\Http\Request;

class EvaluController extends Controller
{
    public function guardarCompleta(Request $request)
{
    \DB::beginTransaction();
    try {
        // 1. Guardar o actualizar la evaluación general
        $evaluacion = \App\Models\Evaluacion\Evaluacion::updateOrCreate(
            [
                'postulacion_id' => $request->postulacion_id,
                'evaluador_id' => auth()->id(),
            ],
            [
                'puntaje_total' => $request->puntaje_total,
                'comentarios' => $request->comentarios_generales,
                'estado' => $request->estado,
            ]
        );

        // 2. Guardar requisitos
        if (is_array($request->requisitos)) {
           
// ...dentro del foreach de requisitos...
foreach ($request->requisitos as $req) {
    // Validar si se envió postulacion_documento_id
    if (!empty($req['postulacion_documento_id'])) {
        $doc = \App\Models\Postulacion\PostulacionDocumento::find($req['postulacion_documento_id']);
        if (!$doc || $doc->requisito_id != $req['requisito_id']) {
            // Si el documento no existe o no corresponde al requisito, retorna error
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'El documento no corresponde al requisito evaluado.'
            ], 422);
        }
    }

    // Guardar el requisito evaluado (aquí tu lógica normal)
    \App\Models\Evaluacion\EvaluacionRequisito::updateOrCreate(
        [
            'evaluacion_id' => $evaluacion->id,
            'requisito_id' => $req['requisito_id'],
        ],
        [
            'estado' => $req['estado'],
            'comentarios' => $req['comentarios'] ?? '',
            'es_requisito_ley' => $req['es_requisito_ley'] ?? false,
            'postulacion_documento_id' => $req['postulacion_documento_id'] ?? null,
        ]
    );
}
        }

        // 3. Guardar documentos evaluados
        if (is_array($request->documentos)) {
            foreach ($request->documentos as $doc) {
                \App\Models\Evaluacion\EvaluacionRequisito::updateOrCreate(
                    [
                        'evaluacion_id' => $evaluacion->id,
                        'postulacion_documento_id' => $doc['postulacion_documento_id'],
                    ],
                    [
                        'estado' => $doc['estado'],
                        'puntaje' => $doc['puntaje'],
                        'comentarios' => $doc['comentarios'] ?? '',
                    ]
                );
            }
        }

        \DB::commit();
        return response()->json(['success' => true, 'evaluacion_id' => $evaluacion->id]);
    } catch (\Exception $e) {
        \DB::rollBack();
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
    public function index($postulacionId)
    {
        try {
            $postulacion = Postulacion::findOrFail($postulacionId);
            $evaluaciones = $postulacion->evaluaciones()
                ->with(['evaluador', 'documentos.documento'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $evaluaciones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar evaluaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, $postulacionId)
    {
        try {
            $validated = $this->validate($request, [
                'comentarios' => 'nullable|string|max:500',
                'estado' => 'required|in:borrador,finalizada,revision'
            ]);

            $postulacion = Postulacion::findOrFail($postulacionId);

            $evaluacion = $postulacion->evaluaciones()->create([
                'evaluador_id' => auth()->id(),
                'puntaje_total' => 0,
                'estado' => $validated['estado'],
                'comentarios' => $validated['comentarios'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'data' => $evaluacion->load('evaluador')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear evaluación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $evaluacion = Evaluacion::with(['evaluador', 'documentos.documento'])
                            ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $evaluacion
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Evaluación no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}