<?php
namespace App\Http\Controllers;

use App\Models\FormularioEvaluacion;
use App\Models\SeccionFormulario;
use App\Models\CriterioFormulario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormularioEvaluacionController extends Controller
{
    public function index()
    {
        return FormularioEvaluacion::with('secciones.criterios')->get();
    }

    public function show($id)
    {
        $formulario = FormularioEvaluacion::with('secciones.criterios')->findOrFail($id);
        return response()->json($formulario);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'resolucion' => 'nullable|string|max:255',
            'puntaje_total' => 'required|numeric',
            'secciones' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $formulario = FormularioEvaluacion::create($validated);

            foreach ($validated['secciones'] as $s) {
                $seccion = $formulario->secciones()->create([
                    'titulo' => $s['titulo'],
                    'puntaje_max' => $s['puntaje_max'],
                    'orden' => $s['orden'],
                ]);

                foreach ($s['criterios'] as $c) {
                    $seccion->criterios()->create([
                        'nombre' => $c['nombre'],
                        'puntaje' => $c['puntaje'],
                        'max_items' => $c['max_items'],
                        'orden' => $c['orden'],
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Formulario creado correctamente.'], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al crear formulario.', 'detail' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $formulario = FormularioEvaluacion::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'resolucion' => 'nullable|string|max:255',
            'puntaje_total' => 'required|numeric',
            'secciones' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $formulario->update($validated);

            // Borrar secciones y criterios anteriores
            foreach ($formulario->secciones as $s) {
                $s->criterios()->delete();
            }
            $formulario->secciones()->delete();

            // Volver a crear secciones y criterios
            foreach ($validated['secciones'] as $s) {
                $seccion = $formulario->secciones()->create([
                    'titulo' => $s['titulo'],
                    'puntaje_max' => $s['puntaje_max'],
                    'orden' => $s['orden'],
                ]);

                foreach ($s['criterios'] as $c) {
                    $seccion->criterios()->create([
                        'nombre' => $c['nombre'],
                        'puntaje' => $c['puntaje'],
                        'max_items' => $c['max_items'],
                        'orden' => $c['orden'],
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
