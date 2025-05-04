<?php

namespace App\Http\Controllers\Convocatorias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Convocatorias\Convocatoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Concvocatorias\ConvocatoriaCollection;


class ConvocatoriaController extends Controller
{/**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");
        $area = $request->get("area");

        //users viene de la tabla users en la base de datos
        $convocatorias = Convocatoria::where(function($q) use($search,$area){
            if($area){
                $q->where("area",$area);
            }
           if($search){
              $q->whereHas("requisitos",function($q) use($search){
                $q->where(DB::raw("requisitos.descripcion"),"ilike","%".$search."%");
             });

           }

        })
        ->orderBy("id","desc")->get();

        return response()->json([
            "convocatorias" => ConvocatoriaCollection::make($convocatorias),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        if($request->hasFile("documento")){
            $path = Storage::putFile("convocatorias",$request->file("documento"));
            $request->request->add(["documento" => $path]);
        }
        
        $convocatoria = Convocatoria::create($request->all());
        $requisitos=Requisitos::create([
            "descripcion"=>$request->descripcion,
            "tipo"=>$request->tipo
        ]);
        $convocatoria->update([
            "requisitos_id"=>$requisitos->id
        ]);

        return response()->json([
            "message" => 200,
        ]);
   }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $convocatoria=Convocatoria::findOrFail($id);
        return response()->json([
            "convocatoria" => ConvocatoriaResource::make($convocatoria),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $convocatoria=Convocatoria::findOrFail($id);
        if($request->hasFile("documento")){
            if($convocatoria->avatar){
                Storage::delete($convocatoria->avatar);
            }
            $path=Storage::putFile("convocatorias",$request->file("documento"));
            $request->request->add(["documento"=>$path]);
        }
        
        $convocatoria->update($request->all());
       $requisitos=$convocatoria->requisitos;
        $requisitos->update([
            "descripcion"=>$request->descripcion,
            "tipo"=>$request->tipo
        ]);
        return response()->json([
            "message"=>200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {$convocatoria = Convocatoria::findOrFail($id);
        if($convocatoria->avatar){
            Storage::delete($convocatoria->avatar);
        }
        $convocatoria->delete();

        return response()->json([
            "message" => 200,
        ]);
    }
}
