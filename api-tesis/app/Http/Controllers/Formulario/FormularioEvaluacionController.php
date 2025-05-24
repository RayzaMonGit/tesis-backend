<?php
namespace App\Http\Controllers\Formulario;

use App\Http\Controllers\Controller;

use App\Models\Formulario\FormularioEvaluacion;
use App\Models\Formulario\SeccionFormulario;
use App\Models\Formulario\CriterioFormulario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Formularios\FormularioEvaluacionResource;

class FormularioEvaluacionController extends Controller
{
    public function index()
    {
        return FormularioEvaluacion::with('secciones.criterios')->get();
    }


public function show($id)
{
    $formulario = FormularioEvaluacion::with('secciones.criterios')->findOrFail($id);

    return new FormularioEvaluacionResource($formulario);
}


    public function store(Request $request)
    {
        $validated = $request->validate([
            
            //'resolucion' => 'nullable|string|max:255',
            'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'puntaje_total' => 'required|numeric',
        'secciones' => 'required|array|min:1',
        'secciones.*.titulo' => 'required|string|max:255',
        'secciones.*.puntaje_max' => 'required|numeric',
        'secciones.*.criterios' => 'required|array|min:1',
        'secciones.*.criterios.*.nombre' => 'required|string|max:255',
        'secciones.*.criterios.*.puntaje_por_item' => 'nullable|numeric',
        'secciones.*.criterios.*.max_items' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            $formulario = FormularioEvaluacion::create($validated);

            foreach ($validated['secciones'] as $s) {
                $seccion = $formulario->secciones()->create([
                    'titulo' => $s['titulo'],
                    'puntaje_max' => $s['puntaje_max'],
                    //'orden' => $s['orden'],
                ]);

                foreach ($s['criterios'] as $c) {
                    $seccion->criterios()->create([
                        'nombre' => $c['nombre'],
                        'puntaje_por_item'=> $c['puntaje_por_item'],
                        //'puntaje' => $c['puntaje'],
                        'max_items' => $c['max_items'],
                        //'orden' => $c['orden'],
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Formulario creado correctamente.'], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al crear formulario.',
                'detail' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
            
        }
    }

    public function update(Request $request, $id)
    {
        
        $validated = $request->validate([
            
            //'resolucion' => 'nullable|string|max:255',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'puntaje_total' => 'required|numeric',
            'secciones' => 'required|array|min:1',
            'secciones.*.titulo' => 'required|string|max:255',
            'secciones.*.puntaje_max' => 'required|numeric',
            'secciones.*.criterios' => 'required|array|min:1',
            'secciones.*.criterios.*.nombre' => 'required|string|max:255',
            'secciones.*.criterios.*.puntaje_por_item' => 'nullable|numeric',
            'secciones.*.criterios.*.max_items' => 'nullable|integer',
        ]);
        
        DB::beginTransaction();
        try {
            $formulario = FormularioEvaluacion::findOrFail($id);
            $formulario->update($validated);

            // Eliminar secciones y criterios existentes
            foreach ($formulario->secciones as $s) {
                $s->criterios()->delete();
                $s->delete();
            }

            // Crear nuevas secciones y criterios
            foreach ($validated['secciones'] as $s) {
                $seccion = $formulario->secciones()->create([
                    'titulo' => $s['titulo'],
                    'puntaje_max' => $s['puntaje_max'],
                    //'orden' => $s['orden'],
                ]);

                foreach ($s['criterios'] as $c) {
                    $seccion->criterios()->create([
                        'nombre' => $c['nombre'],
                        'puntaje_por_item'=> $c['puntaje_por_item'],
                        //'puntaje' => $c['puntaje'],
                        'max_items' => $c['max_items'],
                        //'orden' => $c['orden'],
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Formulario actualizado correctamente.']);
            
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar formulario.', 'detail' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $formulario = FormularioEvaluacion::findOrFail($id);

        DB::beginTransaction();

        try {
            foreach ($formulario->secciones as $s) {
                $s->criterios()->delete();
            }
            $formulario->secciones()->delete();
            $formulario->delete();

            DB::commit();
            return response()->json(['message' => 'Formulario eliminado correctamente.']);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar formulario.', 'detail' => $e->getMessage()], 500);
        }
    }
}
