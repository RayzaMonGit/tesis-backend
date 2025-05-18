<?php

namespace App\Http\Controllers\Convocatorias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Convocatorias\Convocatoria;
use App\Models\Convocatorias\Requisitos;
use App\Models\Convocatorias\RequisitosLey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Convocatorias\ConvocatoriaCollection;
use App\Http\Resources\Convocatorias\ConvocatoriaResource;
use App\Http\Resources\Convocatorias\RequisitoResource;
use App\Http\Resources\Requisitos\RequisitosLeyResource;



class ConvocatoriaController extends Controller
{
    public function requisitos($id) {
        try {
            // Cargar la convocatoria con sus requisitos
            $convocatoria = Convocatoria::with('requisitos')->findOrFail($id);
    
            return response()->json([
                'requisitos' => $convocatoria->requisitos->toArray(), // Convertir a array
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            // Manejar errores
            return response()->json([
                'requisitos' => [],
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function asignarRequisitosLey(Request $request, $id)
{
    $convocatoria = Convocatoria::findOrFail($id);
    $convocatoria->requisitosLey()->sync($request->requisitos_ley_ids); // sincroniza

    return response()->json([
        'message' => 'Requisitos ley asignados correctamente',
        'requisitos' => $convocatoria->requisitosLey,
    ]);
}

public function todosRequisitos($id)
{
    $convocatoria = Convocatoria::with(['requisitos', 'requisitosLey'])->findOrFail($id);

    return response()->json([
        'requisitos_personalizados' => RequisitoResource::collection($convocatoria->requisitos),
        'requisitos_ley' => RequisitosLeyResource::collection($convocatoria->requisitosLey),
    ]);
}



    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $search = $request->get("search");
    $estado = $request->get("estado");
    $area = $request->get("area");

    $convocatorias = Convocatoria::with('requisitos')
        ->when($area, function ($q) use ($area) {
            $q->where("area", $area);
        })
        ->when($estado, function ($q) use ($estado) {
            $q->where("estado", $estado);
        })
        ->when($search, function ($q) use ($search) {
            $q->where(function($query) use ($search) {
                $query->where("titulo", "ilike", "%{$search}%")
                      ->orWhereHas("requisitos", function($q) use ($search){
                          $q->where("descripcion", "ilike", "%{$search}%");
                      });
            });
        })
        ->orderBy("id", "desc")
        ->paginate(10);

    return ConvocatoriaResource::collection($convocatorias);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Iniciar transacción para garantizar que se guarden tanto la convocatoria como sus requisitos
        DB::beginTransaction();
        
        try {
            // Procesar documento
            $documentoPath = null;
            if($request->hasFile("documento")){
                /*$path = $request->file("documento")->store("convocatorias", "public");
                $request->request->add(["documento" => $path]);*/
                $documentoPath = $request->file("documento")->store("convocatorias", "public");
            }
            
            // Crear la convocatoria
            $convocatoria = Convocatoria::create([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'area' => $request->area,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'estado' => $request->estado ?? 'Borrador',
                'plazas_disponibles' => $request->plazas_disponibles,
                'sueldo_referencial' => $request->sueldo_referencial,
                'documento' => $documentoPath,
            ]);

            // sincroniza los requisitos de ley si se enviaron desde el frontend
                if ($request->has('requisitos_ley_ids')) {
                    $convocatoria->requisitosLey()->sync($request->requisitos_ley_ids);
                }
                            
            // Asume que requisitos_ley_ids es un array de IDs seleccionados, como [1, 3, 5].
            if ($request->has('requisitos_ley_ids')) {
                $convocatoria->requisitosLey()->sync($request->requisitos_ley_ids);
            }            
            
            
            // Procesar requisitos personalizados
            if ($request->has('requisitos_personalizados')) {
                $requisitosPersonalizados = json_decode($request->requisitos_personalizados, true);
                
                foreach ($requisitosPersonalizados as $req) {
                    Requisitos::create([
                        'id_convocatoria' => $convocatoria->id,
                        'descripcion' => $req['nombre'],
                        'tipo' => $req['tipo']
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                "message" => 200,
                "convocatoria" => $convocatoria->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                "message" => 500,
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $convocatoria = Convocatoria::with('requisitos')->findOrFail($id);
        return response()->json([
            "convocatoria" => ConvocatoriaResource::make($convocatoria),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        
        try {
            $convocatoria = Convocatoria::findOrFail($id);
            
            // Procesar documento
            if($request->hasFile("documento")){
                if($convocatoria->documento){
                    Storage::delete($convocatoria->documento);
                }
                $path = $request->file("documento")->store("convocatorias", "public");

                $request->request->add(["documento" => $path]);
            }
            
            // Actualizar convocatoria
            $convocatoria->update([
                'titulo' => $request->titulo ?? $convocatoria->titulo,
                'descripcion' => $request->descripcion ?? $convocatoria->descripcion,
                'area' => $request->area ?? $convocatoria->area,
                'fecha_inicio' => $request->fecha_inicio ?? $convocatoria->fecha_inicio,
                'fecha_fin' => $request->fecha_fin ?? $convocatoria->fecha_fin,
                'estado' => $request->estado ?? $convocatoria->estado,
                'plazas_disponibles' => $request->plazas_disponibles ?? $convocatoria->plazas_disponibles,
                'sueldo_referencial' => $request->sueldo_referencial ?? $convocatoria->sueldo_referencial,
                'documento' => $request->documento ?? $convocatoria->documento,
            ]);
            
            // Si se están actualizando los requisitos, eliminar los anteriores
            if ($request->has('requisitos_obligatorios') || $request->has('requisitos_personalizados')) {
                // Eliminar requisitos existentes
                Requisitos::where('id_convocatoria', $convocatoria->id)->delete();
                
                // Procesar requisitos obligatorios seleccionados
                if ($request->has('requisitos_obligatorios')) {
                    $requisitosObligatorios = json_decode($request->requisitos_obligatorios, true);
                    
                    foreach ($requisitosObligatorios as $req) {
                        if ($req['seleccionado']) {
                            Requisitos::create([
                                'id_convocatoria' => $convocatoria->id,
                                'descripcion' => $req['texto'],
                                'tipo' => 'Obligatorio'
                            ]);
                        }
                    }
                }
                
                // Procesar requisitos personalizados
                if ($request->has('requisitos_personalizados')) {
                    $requisitosPersonalizados = json_decode($request->requisitos_personalizados, true);
                    
                    foreach ($requisitosPersonalizados as $req) {
                        Requisitos::create([
                            'id_convocatoria' => $convocatoria->id,
                            'descripcion' => $req['nombre'],
                            'tipo' => $req['tipo']
                        ]);
                    }
                }
            }
            // Sincronizar requisitos de ley si se enviaron
if ($request->has('requisitos_ley_ids')) {
    $convocatoria->requisitosLey()->sync($request->requisitos_ley_ids);
}

            DB::commit();
            
            return response()->json([
                "message" => 200
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                "message" => 500,
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        
        try {
            $convocatoria = Convocatoria::findOrFail($id);
            
            // Eliminar documento asociado
            if($convocatoria->documento){
                Storage::delete($convocatoria->documento);
            }
            
            // Eliminar requisitos asociados
            Requisitos::where('id_convocatoria', $convocatoria->id)->delete();
            
            // Eliminar convocatoria
            $convocatoria->delete();
            
            DB::commit();
            
            return response()->json([
                "message" => 200
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                "message" => 500,
                "error" => $e->getMessage()
            ], 500);
        }
    }
}