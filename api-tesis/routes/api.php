<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Rol\RoleController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Convocatorias\ConvocatoriaController;
use App\Http\Controllers\Postulante\PostulanteController;
use App\Http\Controllers\Postulante\RegisterController;
use App\Http\Controllers\Requisitos\RequisitosLeyController;
use App\Http\Controllers\Comision\ComisionController;
use App\Http\Controllers\Formulario\FormularioEvaluacionController;


//para que se registre un usuario sin estar autentificado
Route::resource("register",RegisterController::class);
//Route::post("register/{id}",[RegisterController::class,"update"]);
 
Route::group([
   // 'middleware' => 'api',
    'prefix' => 'auth',
   //'middleware' => ['auth:api','role:admin']
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');//->middleware('auth:api')
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');//->middleware('auth:api')
    Route::post('/me', [AuthController::class, 'me'])->name('me');//->middleware('auth:api')
});
Route::group([
    'middleware' => ['auth:api']
],function($router) {
    Route::resource("role",RoleController::class);
    //ruta post para actualizar los datos del usuario
    Route::post("staffs/{id}",[StaffController::class,"update"]);
    //para e index de staff
    Route::resource("staffs",StaffController::class);

    Route::post("convocatorias/{id}", [ConvocatoriaController::class, "update"]); // método POST para actualizar con archivos
    Route::resource("convocatorias", ConvocatoriaController::class);//esto en teoria ya estaba bien

    Route::get('convocatorias/{id}/requisitos', [ConvocatoriaController::class, 'requisitos']);

    //Route::resource('postulantes', [PostulanteController::class]);
    Route::resource("postulantes", PostulanteController::class);
    Route::post("postulantes/{id}", [PostulanteController::class,"update"]);

    Route::resource("requisitosley", RequisitosLeyController::class);

    // Asignar requisitos ley a una convocatoria
    Route::post('/convocatorias/{id}/requisitos-ley', [ConvocatoriaController::class, 'asignarRequisitosLey']);
    // Obtener todos los requisitos (ley + personalizados)
    Route::get('convocatorias/{id}/todos-requisitos', [ConvocatoriaController::class, 'todosRequisitos']);
    Route::post('convocatorias/{id}/todos-requisitos', [ConvocatoriaController::class, 'updateRequisitos']);

    //comisiones
    Route::get('convocatorias/{id}/comision', [ComisionController::class, 'obtenerComision']);
    Route::post('convocatorias/{id}/comision', [ComisionController::class, 'asignarComision']);

    //Formulario

    Route::get('formularios-evaluacion', [FormularioEvaluacionController::class, 'index']);
    Route::post('formularios-evaluacion', [FormularioEvaluacionController::class, 'store']);
    Route::get('formularios-evaluacion/{id}', [FormularioEvaluacionController::class, 'show']);
    Route::post('formularios-evaluacion/{id}', [FormularioEvaluacionController::class, 'update']); // POST en vez de PUT
    Route::delete('formularios-evaluacion/{id}', [FormularioEvaluacionController::class, 'destroy']);


});