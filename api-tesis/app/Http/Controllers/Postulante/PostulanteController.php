<?php

namespace App\Http\Controllers\Postulante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Models\Postulante;
use Illuminate\Support\Facades\DB;

use App\Http\Resources\postulante\PostulanteCollection;
use App\Http\Resources\postulante\PostulanteResource;


class PostulanteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
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
