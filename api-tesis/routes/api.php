<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Rol\RoleController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Convocatorias\ConvocatoriaController;
 
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
    Route::resource("staffs",StaffController::class);

    Route::post("convocatorias/{id}", [ConvocatoriaController::class, "update"]); // método POST para actualizar con archivos
    Route::resource("convocatorias", ConvocatoriaController::class);
});