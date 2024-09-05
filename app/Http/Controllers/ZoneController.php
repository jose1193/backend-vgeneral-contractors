<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\BaseController as BaseController;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;

use App\Interfaces\ZoneRepositoryInterface;
use App\Http\Requests\ZoneRequest;
use App\Classes\ApiResponseClass;
use App\Http\Resources\ZoneResource;

use App\Services\ZoneService;

class ZoneController extends BaseController
{
    protected $cacheTime = 720;
    protected $zoneService;

    public function __construct(ZoneService $zoneService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only(['index', 'store', 'update', 'destroy']);
        
        $this->zoneService = $zoneService;
    }

    // SHOW LIST OF ZONES
    public function index(): JsonResponse
    {
        try {
            // Obtener todas las zonas usando el servicio
            $zones = $this->zoneService->all();

            if ($zones === null) {
                return response()->json(['message' => 'No zones found or invalid data structure'], 404);
            }

            return ApiResponseClass::sendResponse(ZoneResource::collection($zones), 200);

        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching zones: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Database error occurred while fetching zones'], 500);

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching zones: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching zones'], 500);
        }
    }

    // STORE ZONE
    public function store(ZoneRequest $request): JsonResponse
    {
        try {
            // Validar y obtener los detalles de la solicitud
            $details = $request->validated();
           
            // Utilizar el servicio para almacenar la zona
            $zone = $this->zoneService->storeZone($details);
            
            return ApiResponseClass::sendSimpleResponse(new ZoneResource($zone), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while creating zone', 'error' => $ex->getMessage()], 500);
        }
    }

    // UPDATE ZONE
    public function update(ZoneRequest $request, string $uuid): JsonResponse
    {
        $updateDetails = $request->validated();

        try {
            // Utilizar el servicio para actualizar la zona
            $zone = $this->zoneService->updateZone($updateDetails, $uuid);

            return ApiResponseClass::sendSimpleResponse(new ZoneResource($zone), 200);

        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while updating zone', 'error' => $ex->getMessage()], 500);
        }
    }

    // SHOW ZONE
    public function show(string $uuid): JsonResponse
    {
        try {
            // Utilizar el servicio para obtener la zona
            $zone = $this->zoneService->showZone($uuid);

            if ($zone === null) {
                return response()->json(['message' => 'Zone not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new ZoneResource($zone), 200);

        } catch (\Exception $e) {
            // Registrar el mensaje de la excepción en el log
            Log::error('Error occurred while fetching zone: ' . $e->getMessage());

            // Manejar cualquier excepción y devolver una respuesta de error
            return response()->json(['message' => 'Error occurred while fetching zone', 'error' => $e->getMessage()], 500);
        }
    }

    // DELETE ZONE
    public function destroy(string $uuid): JsonResponse
    {
        try {
            // Utilizar el servicio para eliminar la zona
            $zone = $this->zoneService->deleteZone($uuid);

            if ($zone === null) {
                return response()->json(['message' => 'Zone not found'], 404);
            }

            return response()->json(['message' => 'Zone deleted successfully'], 200);

        } catch (\Exception $e) {
            // Registrar el mensaje de la excepción en el log
            Log::error('Error occurred while deleting zone: ' . $e->getMessage());

            // Manejar cualquier excepción y devolver una respuesta de error
            return response()->json(['message' => 'Error occurred while deleting zone', 'error' => $e->getMessage()], 500);
        }
    }


    public function restore($uuid)
    {
        try {
            // Utilizar el servicio para restaurar el cliente
            $zone = $this->zoneService->restoreZone($uuid);

            return ApiResponseClass::sendSimpleResponse(new ZoneResource($zone), 200);

        } catch (\Exception $e) {
            // Registrar el mensaje de la excepción en el log
            Log::error('Error occurred while restoring zone: ' . $e->getMessage());

            // Manejar cualquier excepción y devolver una respuesta de error
            return response()->json(['message' => 'Error occurred while restoring zone', 'error' => $e->getMessage()], 500);
        }
    }
}