<?php

namespace App\Http\Controllers\Postulacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Postulacion\Postulacion;
use App\Http\Requests\Postulacion\StorePostulacionRequest;
use App\Http\Resources\Postulacion\PostulacionResource;


use App\Models\Postulante\Postulante;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Postulante\PostulanteResource;
use App\Http\Resources\Postulante\PostulanteCollection;
use App\Models\Convocatoria\Convocatoria;
use App\Http\Resources\Convocatoria\ConvocatoriaResource;
use App\Http\Resources\Convocatoria\ConvocatoriaCollection;


class PostulacionController extends Controller
{
    public function index()
{
    $postulaciones = Postulacion::with([
        'postulante.user',
        'convocatoria.formulario.secciones.criterios', 
        'convocatoria.requisitosLey',
        'convocatoria.evaluadores'
    ])->get();

    return PostulacionResource::collection($postulaciones);
}


    public function store(Request $request)
{
    $validated = $request->validate([
        'postulante_id' => 'required|exists:postulantes,id',
        'convocatoria_id' => 'required|exists:convocatorias,id',
    ]);

    // Verificar si ya existe una postulación para ese postulante y convocatoria
    $existe = Postulacion::where('postulante_id', $validated['postulante_id'])
        ->where('convocatoria_id', $validated['convocatoria_id'])
        ->first();

    if ($existe) {
        return response()->json([
            'message' => 'Ya estás postulado a esta convocatoria.',
            'redirect' => true, // bandera para frontend
            'postulacion_id' => $existe->id // para redireccionar a los documentos
        ], 200);
    }

    DB::beginTransaction();
    try {
        $postulacion = Postulacion::create($validated);
        DB::commit();
        return response()->json([
            'message' => 'Formulario creado correctamente.',
            'postulacion_id' => $postulacion->id
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Error al crear la postulación',
            'details' => $e->getMessage(),
        ], 500);
    }
}


public function porPostulante($postulanteId)
{
    $postulaciones = Postulacion::with([
        'postulante.user',
        'convocatoria.formulario.secciones.criterios', 
        'convocatoria.requisitosLey',
        'convocatoria.evaluadores'
    ])
    ->where('postulante_id', $postulanteId)
    ->get();

    return PostulacionResource::collection($postulaciones);
}

    public function show($id)
{
    // Busca la postulación de usuario postulante_id
    $postulacion = Postulacion::with([
        'postulante.user',
        'convocatoria.formulario.secciones.criterios', 
        'convocatoria.requisitosLey',
        'convocatoria.evaluadores'
    ])->findOrFail($id);
    // Devuelve la postulación como un recurso
    return new PostulacionResource($postulacion);

    /*$postulacion = Postulacion::with([
        'postulante.user',
        'convocatoria.formulario.secciones.criterios', 
        'convocatoria.requisitosLey',
        'convocatoria.evaluadores'
    ])->findOrFail($id);

    return new PostulacionResource($postulacion);*/
}


    public function update(Request $request, $id)
{
    // Busca la postulación por ID
    $postulacion = Postulacion::findOrFail($id);

    // Valida los datos enviados
    $validated = $request->validate([
        'estado' => 'required|in:pendiente,en evaluación,evaluado,rechazado,aprobado',
        // Agrega otras validaciones si es necesario
    ]);

    // Actualiza la postulación con los datos validados
    $postulacion->update($validated);

    // Devuelve la postulación actualizada como un recurso
    return new PostulacionResource($postulacion);
}

    public function destroy($id)
    {
        $postulacion = Postulacion::findOrFail($id);
        DB::beginTransaction();
        try {
            $postulacion->delete();
            DB::commit();
            return response()->json(['message' => 'Postulación eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar la postulación.'], 500);
        }
    }

    public function porConvocatoria($convocatoriaId)
{
    $postulaciones = Postulacion::where('convocatoria_id', $convocatoriaId)
        ->with([
            'postulante.user',
            'convocatoria.formulario.secciones.criterios', 
            'convocatoria.requisitosLey',
            'convocatoria.evaluadores'
        ])
        ->get();

    return PostulacionResource::collection($postulaciones);
}

public function cambiarEstado(Request $request, $id)
{

    $postulacion = Postulacion::findOrFail($id);
    
    // Verificar que el usuario tiene permisos para cambiar el estado
    // Aquí deberías agregar tu lógica de autorización
    if($request->has('estado')) {
        $postulacion->estado = $request->estado;
    }
    // Si se envía una nota preliminar, también la actualizamos
    if ($request->has('nota_preliminar')) {
        $postulacion->nota_preliminar = $request->nota_preliminar;
    }
    $postulacion->save();

    return response()->json([
        'success' => true,
        'message' => 'Estado actualizado correctamente',
        'data' => $postulacion
    ]);
}

}