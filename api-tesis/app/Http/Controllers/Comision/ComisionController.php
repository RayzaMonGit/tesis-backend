<?php

namespace App\Http\Controllers\Comision;
use App\Models\Convocatorias\Convocatoria;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComisionController extends Controller
{
    public function asignarComision(Request $request, $id)
{
    $convocatoria = Convocatoria::findOrFail($id);

    // Validar que sea un array de IDs
    $evaluadoresIds = $request->input('evaluadores', []);

    if (!is_array($evaluadoresIds)) {
        return response()->json([
            'message' => 'El formato de evaluadores no es válido.'
        ], 422);
    }

    // Sincronizar evaluadores (esto reemplaza los anteriores)
    $convocatoria->evaluadores()->sync($evaluadoresIds);

    return response()->json([
        'message' => 'Evaluadores asignados correctamente'
    ]);
}


public function obtenerComision($id)
{
    $convocatoria = Convocatoria::with('evaluadores')->findOrFail($id);

    return response()->json([
        'evaluadores' => $convocatoria->evaluadores
    ]);
}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
