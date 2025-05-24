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
        $request->validate([
            'postulacion_id' => 'required|exists:postulaciones,id',
            'nombre' => 'required|string',
            'archivo' => 'required|file',
            'es_requisito_ley' => 'boolean',
            'es_requisito_personalizado' => 'boolean',
        ]);

        $ruta = $request->file('archivo')->store('postulacion_documentos');

        $documento = PostulacionDocumento::create([
            'postulacion_id' => $request->postulacion_id,
            'nombre' => $request->nombre,
            'archivo' => $ruta,
            'es_requisito_ley' => $request->es_requisito_ley ?? false,
            'es_requisito_personalizado' => $request->es_requisito_personalizado ?? false,
        ]);
        return new PostulacionDocumentoResource($documento);

        //return response()->json($documento, 201);
    }

    public function destroy($id)
    {
        $documento = PostulacionDocumento::findOrFail($id);
        Storage::delete($documento->archivo);
        $documento->delete();

        return response()->json(['message' => 'Documento eliminado']);
    }
}
