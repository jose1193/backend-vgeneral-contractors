<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\PermissionController;
//use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ProfilePhotoController;

use App\Http\Controllers\CheckUsernameController;
use App\Http\Controllers\CheckEmailController;
use App\Http\Controllers\BiometricAuthController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\CreateUserController;
use App\Http\Controllers\PasswordResetUserController;

//Route::get('/user', function (Request $request) {
    //return $request->user();
//})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);

Route::post('/register', [CreateUserController::class, 'store']);

Route::get('/username-available/{username}', [CheckUsernameController::class, 'checkUsernameAvailability']);

Route::get('/email-available/{email}', [CheckEmailController::class, 'checkEmailAvailability']);

// Route related to User Social Login
Route::post('/social-login', [SocialLoginController::class, 'handleProviderCallback']);


Route::controller(PasswordResetUserController::class)->group(function () {
    Route::post('/forgot-password', 'store'); 
    Route::post('/enter-pin', 'verifyResetPassword');
    Route::post('/reset-password', 'updatePassword');  
   
});




//Route::controller(RegisterController::class)->group(function(){
    //Route::post('register', 'register');
    //Route::post('login', 'login');
//});



Route::middleware(['auth:sanctum','handle.notfound'])->group(function() {
    //Route::get('/user', function (Request $request) {
        //return $request->user();
    //});

    // Rutas protegidas por autenticación y verificación
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    //Route::get('/users', [AuthController::class, 'getUsers']);
    Route::post('update-password', [AuthController::class, 'updatePassword']);
    
    //Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('update-profile', [CreateUserController::class, 'update']);
    Route::post('update-profile-photo', [ProfilePhotoController::class, 'update']);
    

    // Rutas relacionadas con roles
    Route::get('roles-list', [RoleController::class, 'index']); // Obtener una lista de roles
    Route::post('roles', [RoleController::class, 'store']); // Crear un nuevo rol
    Route::get('roles/{id}', [RoleController::class, 'show']); // Mostrar un rol específico
    Route::put('roles-update/{id}', [RoleController::class, 'update']); // Actualizar un rol existente
    Route::delete('roles-delete/{id}', [RoleController::class, 'destroy']); // Eliminar un rol existente
    Route::get('roles-permissions', [RoleController::class, 'create']); // Mostrar listado de permisos
    Route::get('roles/{id}/edit', [RoleController::class, 'edit']); // Mostrar listado de roles y permisos del usuario a editar

    
    // Routes related to Users
    Route::prefix('users')->group(function () {
    Route::get('/', [UsersController::class, 'index']);    
    Route::post('/store', [UsersController::class, 'store']); 
    Route::get('/{uuid}', [UsersController::class, 'show']); 
    Route::put('/update/{uuid}', [UsersController::class, 'update']); 
    Route::post('/update-images/{uuid}/', [UsersController::class, 'updateImage']); 
    Route::delete('delete/{uuid}', [UsersController::class, 'destroy']);
    Route::put('restore/{uuid}', [UsersController::class, 'restore']);  
    
    }); 

    // Rutas relacionadas con permisos
    Route::get('permissions-list', [PermissionController::class, 'index']);
    Route::post('permissions', [PermissionController::class, 'store']);
    Route::get('permissions/{id}', [PermissionController::class, 'show']);
    Route::put('permissions-update/{id}', [PermissionController::class, 'update']);
    Route::delete('permissions-delete/{id}', [PermissionController::class, 'destroy']);
    Route::get('permissions/create', [PermissionController::class, 'create']);
    Route::get('permissions/{id}/edit', [PermissionController::class, 'edit']);

    
   
    // Routes related to Biometric Login
    Route::post('/biometric-login', [BiometricAuthController::class, 'store']);

   
    // Otras rutas protegidas...
});
    
