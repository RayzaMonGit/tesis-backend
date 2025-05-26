<?php

namespace App\Http\Controllers\Postulacion;

use App\Http\Controllers\Controller;
use App\Models\Postulacion\PostulacionDocumento;
use Illuminate\Http\Request;
use App\Http\Resources\Postulacion\PostulacionDocumentoResource;
use App\Http\Resources\Postulacion\PostulacionDocumentoCollection;
use App\Models\Postulacion\Postulacion;
use App\Models\Postulante\Postulante;
use App\Models\Convocatoria\Convocatoria;
use App\Http\Resources\Postulante\PostulanteResource;
use App\Http\Resources\Postulante\PostulanteCollection;
use App\Http\Resources\Convocatoria\ConvocatoriaResource;
use App\Http\Resources\Convocatoria\ConvocatoriaCollection;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PostulacionDocumentoController extends Controller
{
    public function index($id)
{
    // Busca todos los documentos asociados a la postulación con el ID dado
    $documentos = PostulacionDocumento::where('postulacion_id', $id)->get();
    return PostulacionDocumentoResource::collection($documentos);
}

public function all()
{
    $documentos = PostulacionDocumento::all();
    return PostulacionDocumentoResource::collection($documentos);
}

    public function store(Request $request)
{
     \Log::info('📥 Datos recibidos en store:', [
        'postulacion_id' => $request->input('postulacion_id'),
        'requisito_id' => $request->input('requisito_id'),
        'nombre' => $request->input('nombre'),
        'es_requisito_ley' => $request->input('es_requisito_ley'),
        'es_requisito_personalizado' => $request->input('es_requisito_personalizado'),
        'archivo_presente' => $request->hasFile('archivo'),
        'archivo_nombre' => $request->file('archivo')?->getClientOriginalName(),
        'archivo_tamaño' => $request->file('archivo')?->getSize(),
    ]);
   


    $request->validate([
        'postulacion_id' => 'required|exists:postulaciones,id',
        'requisito_id' => 'required|exists:requisitos,id',
        'nombre' => 'required|string',
        'archivo' => 'required|file',
        'es_requisito_ley' => 'required|boolean',
        'es_requisito_personalizado' => 'required|boolean',
    ]);

    $ruta = $request->file('archivo')->store("postulacion-documentos/{$request->postulacion_id}/requisitos", 'public');

    PostulacionDocumento::create([
        'postulacion_id' => $request->postulacion_id,
        'requisito_id' => $request->requisito_id,
        'nombre' => $request->nombre,
        'archivo' => $ruta,
        'es_requisito_ley' => $request->es_requisito_ley,
        'es_requisito_personalizado' => $request->es_requisito_personalizado,
    ]);

    return response()->json(['message' => 'Documento guardado correctamente']);
}

/*
    public function storeMultiple(Request $request)
    {
        Log::info('=== INICIO storeMultiple ===');
        Log::info('Método HTTP:', $request->method());
        Log::info('URL completa:', $request->fullUrl());
        Log::info('Datos recibidos en storeMultiple:', $request->all());
        Log::info('Archivos recibidos:', $request->allFiles());
        Log::info('Headers:', $request->headers->all());

        $request->validate([
            'postulacion_id' => 'required|exists:postulaciones,id',
            'archivos' => 'required|array',
            'archivos.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'requisito_ids' => 'required|array',
            'requisito_ids.*' => 'required|integer',
        ]);

        $postulacionId = $request->postulacion_id;
        $archivos = $request->file('archivos');
        $requisitoIds = $request->requisito_ids;

        if (count($archivos) !== count($requisitoIds)) {
            return response()->json([
                'error' => 'El número de archivos debe coincidir con el número de requisitos'
            ], 400);
        }

        $documentosCreados = [];

        DB::beginTransaction();
        
        try {
            foreach ($archivos as $index => $archivo) {
                $requisitoId = $requisitoIds[$index];
                
                // Generar nombre único para el archivo
                $nombreArchivo = time() . '_' . $requisitoId . '_' . $archivo->getClientOriginalName();
                $ruta = $archivo->storeAs('postulacion_documentos', $nombreArchivo);

                // Determinar si es requisito de ley o personalizado
                // Esto depende de tu lógica de negocio
                $esRequisitoLey = $this->esRequisitoLey($requisitoId);
                $esRequisitoPersonalizado = !$esRequisitoLey;

                $documento = PostulacionDocumento::create([
                    'postulacion_id' => $postulacionId,
                    'requisito_id' => $requisitoId, // Asumiendo que tienes esta columna
                    'nombre' => $archivo->getClientOriginalName(),
                    'archivo' => $ruta,
                    'es_requisito_ley' => $esRequisitoLey,
                    'es_requisito_personalizado' => $esRequisitoPersonalizado,
                ]);

                $documentosCreados[] = $documento;
                
                Log::info("Documento creado: ID {$documento->id}, Requisito ID: {$requisitoId}");
            }

            DB::commit();
            
            return response()->json([
                'message' => 'Documentos guardados correctamente',
                'documentos' => PostulacionDocumentoResource::collection($documentosCreados)
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('❌ Error al guardar documentos: ' . $e->getMessage());
            Log::error('❌ Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Error al guardar los documentos',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
*/
    // Método auxiliar para determinar si un requisito es de ley
    private function esRequisitoLey($requisitoId)
    {
        // Implementa tu lógica aquí
        // Por ejemplo, consultar si existe en la tabla de requisitos_ley
        // return DB::table('requisitos_ley')->where('id', $requisitoId)->exists();
        
        // Por ahora retornamos false, ajusta según tu lógica
        return false;
    }
    public function destroy($id)
    {
        $documento = PostulacionDocumento::findOrFail($id);
        Storage::delete($documento->archivo);
        $documento->delete();

        return response()->json(['message' => 'Documento eliminado']);
    }
}
