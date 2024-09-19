<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Hash;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;

use App\Interfaces\UsersRepositoryInterface;
use App\Http\Requests\CreateUserRequest;
use App\Classes\ApiResponseClass;
use App\Http\Resources\UserResource;

use App\Services\UserService;



class UsersController extends BaseController
{

    
    protected $cacheTime = 720;
   

    protected $userService;

    public function __construct(UserService $userService)
    {
         // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        
        $this->userService = $userService;
    }

      public function getUsersRoles(string $role): JsonResponse
    {
            try {
           
            $users = $this->userService->getUsersByRole($role);

            if ($users->isEmpty()) {
            return response()->json(['message' => 'No Public Adjusters found'], 404);
            }

            // Retorna la lista de usuarios en formato JSON
            return ApiResponseClass::sendResponse(UserResource::collection($users), 200);

            } catch (QueryException $e) {
            Log::error('Database error occurred while fetching Public Adjusters: ' . $e->getMessage(), [
            'exception' => $e
            ]);
            return response()->json(['message' => 'Database error occurred while fetching Public Adjusters'], 500);

            } catch (\Exception $e) {
            Log::error('Error occurred while fetching Public Adjusters: ' . $e->getMessage(), [
            'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching Public Adjusters'], 500);
            }   
        }

        public function getTechnicalServices(): JsonResponse
    {
        try {
        // Obtener usuarios con el rol "Technical Services"
        $users = $this->userService->getUsersByRole('Technical Services');

        if ($users->isEmpty()) {
            return response()->json(['message' => 'No Technical Services found'], 404);
        }

        // Retorna la lista de usuarios en formato JSON
        return ApiResponseClass::sendResponse(UserResource::collection($users), 200);

        } catch (QueryException $e) {
        Log::error('Database error occurred while fetching Technical Services: ' . $e->getMessage(), [
            'exception' => $e
        ]);
        return response()->json(['message' => 'Database error occurred while fetching Technical Services'], 500);

        } catch (\Exception $e) {
        Log::error('Error occurred while fetching Technical Services: ' . $e->getMessage(), [
            'exception' => $e
        ]);
        return response()->json(['message' => 'Error occurred while fetching Technical Services'], 500);
        }
    }

    // SHOW LIST OF USERS

       public function index(): JsonResponse
    {
        try {
            // Obtener todos los usuarios usando el servicio
            $users = $this->userService->all();

            if ($users === null) {
                return response()->json(['message' => 'No users found or invalid data structure'], 404);
            }

            return ApiResponseClass::sendResponse(UserResource::collection($users), 200);

        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching users: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Database error occurred while fetching users'], 500);

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching users: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching users'], 500);
        }
    }


    // SYNC ROLES
    public function create()
    {
    $cacheKey = 'roles_list';
    $roles = $this->getCachedData($cacheKey, 360, function () {
        return Role::orderBy('id', 'DESC')->get();
    });
    return response()->json(['roles' => $roles], 200);
    }



    // STORE USER
   
public function store(CreateUserRequest $request)
{
    try {
        // Validar y obtener los detalles de la solicitud
        $details = $request->validated();
       
        // Utilizar el servicio para almacenar el usuario
        $user = $this->userService->storeUser($details);
        
        return ApiResponseClass::sendSimpleResponse(new UserResource($user), 200);
    } catch (\Exception $ex) {
        return response()->json(['message' => 'Error occurred while creating user', 'error' => $ex->getMessage()], 500);
    }
}



   // Método para actualizar un usuario
   public function update(CreateUserRequest $request, $uuid): JsonResponse
    {
        $updateDetails = $request->validated();

        try {
           // Utilizar el servicio para actualizar el usuario
            $user = $this->userService->updateUser($updateDetails, $uuid);

            return ApiResponseClass::sendSimpleResponse(new UserResource($user), 200);

        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while updating user', 'error' => $ex->getMessage()], 500);
        }
    }


    // SHOW PROFILE USER
   public function show($uuid)
{
    try {
        // Utilizar el servicio para obtener el usuario
        $user = $this->userService->showUser($uuid);

        return ApiResponseClass::sendSimpleResponse(new UserResource($user), 200);

    } catch (\Exception $e) {
        // Registrar el mensaje de la excepción en el log
        Log::error('Error occurred while fetching user: ' . $e->getMessage());

        // Manejar cualquier excepción y devolver una respuesta de error
        return response()->json(['message' => 'Error occurred while fetching user', 'error' => $e->getMessage()], 500);
    }
}



    // USER DELETE

 public function destroy($uuid)
{
    try {
        // Utilizar el servicio para obtener el usuario
        $user = $this->userService->deleteUser($uuid);

        return ApiResponseClass::sendResponse('User Delete Successful','',200);

    } catch (\Exception $e) {
        // Registrar el mensaje de la excepción en el log
        Log::error('Error occurred while deleting user: ' . $e->getMessage());

        // Manejar cualquier excepción y devolver una respuesta de error
        return response()->json(['message' => 'Error occurred while deleting user', 'error' => $e->getMessage()], 500);
    }
}

   


// USER RESTORE

 public function restore($uuid)
{
    try {
        // Utilizar el servicio para obtener el usuario
        $user = $this->userService->restoreUser($uuid);

            return ApiResponseClass::sendSimpleResponse(new UserResource($user), 200);

    } catch (\Exception $e) {
        // Registrar el mensaje de la excepción en el log
        Log::error('Error occurred while restoring user: ' . $e->getMessage());

        // Manejar cualquier excepción y devolver una respuesta de error
        return response()->json(['message' => 'Error occurred while restoring user', 'error' => $e->getMessage()], 500);
    }
}





}
