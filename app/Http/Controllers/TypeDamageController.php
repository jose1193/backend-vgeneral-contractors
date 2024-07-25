<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;

use App\Classes\ApiResponseClass;
use App\Http\Requests\TypeDamageRequest;
use App\Http\Resources\TypeDamageResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;

use App\Services\TypeDamageService;

class TypeDamageController extends BaseController
{
    protected $cacheTime = 720;
    protected $typeDamageService;

    public function __construct(TypeDamageService $typeDamageService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        
        $this->typeDamageService = $typeDamageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            // Obtener todos los type damages usando el servicio
            $damages = $this->typeDamageService->all();

            if ($damages === null) {
                return response()->json(['message' => 'No type damage found or invalid data structure'], 404);
            }

            return ApiResponseClass::sendResponse(TypeDamageResource::collection($damages), 200);
        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching Type Damages: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Database error occurred while fetching Type Damages'], 500);
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching Type Damages: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching Type Damages'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TypeDamageRequest $request): JsonResponse
    {
        try {
            // Validar y obtener los detalles de la solicitud
            $details = $request->validated();
            
            // Utilizar el servicio para almacenar el type damage
            $damage = $this->typeDamageService->storeTypeDamage($details);

            return ApiResponseClass::sendSimpleResponse(new TypeDamageResource($damage), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while creating Type Damages', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $damage = $this->typeDamageService->showTypeDamage($uuid);

            if ($damage === null) {
                return response()->json(['message' => 'Type Damage not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new TypeDamageResource($damage), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while retrieving Type Damage', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TypeDamageRequest $request, string $uuid): JsonResponse
    {
        try {
            // Validar y obtener los detalles de la solicitud
            $details = $request->validated();

            // Utilizar el servicio para actualizar el type damage
            $damage = $this->typeDamageService->updateTypeDamage($details, $uuid);

            return ApiResponseClass::sendSimpleResponse(new TypeDamageResource($damage), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while updating Type Damage', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $damage = $this->typeDamageService->deleteTypeDamage($uuid);

            if ($damage === null) {
                return response()->json(['message' => 'Type Damage not found'], 404);
            }

            return response()->json(['message' => 'Type Damage deleted successfully'], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while deleting Type Damage', 'error' => $ex->getMessage()], 500);
        }
    }
}