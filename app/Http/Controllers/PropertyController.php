<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;

use App\Interfaces\PropertyRepositoryInterface;
use App\Http\Requests\PropertyRequest;
use App\Classes\ApiResponseClass;
use App\Http\Resources\PropertyResource;

use App\Services\PropertyService;

class PropertyController extends BaseController
{
    protected $cacheTime = 720;
    protected $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Lead')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        
        $this->propertyService = $propertyService;
    }

    // SHOW LIST OF CUSTOMER PROPERTIES
    public function index(): JsonResponse
    {
        try {
            // Obtener todas las propiedades de los clientes usando el servicio
            $customerProperties = $this->propertyService->all();

            if ($customerProperties === null) {
                return response()->json(['message' => 'No customer properties found or invalid data structure'], 404);
            }

            return ApiResponseClass::sendResponse(PropertyResource::collection($customerProperties), 200);

        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching customer properties: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Database error occurred while fetching customer properties'], 500);

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching customer properties: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching customer properties'], 500);
        }
    }

    // STORE  PROPERTY
public function store(PropertyRequest $request)
{
    try {
        $details = $request->validated();
        $customerIds = $details['customer_id'] ?? [];
        unset($details['customer_id']); 

        $customerProperty = $this->propertyService->storeCustomerProperty($details, $customerIds);

        return ApiResponseClass::sendSimpleResponse(new PropertyResource($customerProperty), 200);
    } catch (\Exception $ex) {
        return response()->json(['message' => 'Error occurred while creating customer property', 'error' => $ex->getMessage()], 500);
    }
}

    // SHOW CUSTOMER PROPERTY
    public function show($uuid)
    {
        try {
            // Utilizar el servicio para obtener la propiedad del cliente
            $customerProperty = $this->propertyService->showCustomerProperty($uuid);

            return ApiResponseClass::sendSimpleResponse(new PropertyResource($customerProperty), 200);

        } catch (\Exception $e) {
            // Registrar el mensaje de la excepción en el log
            Log::error('Error occurred while fetching customer property: ' . $e->getMessage());

            // Manejar cualquier excepción y devolver una respuesta de error
            return response()->json(['message' => 'Error occurred while fetching customer property', 'error' => $e->getMessage()], 500);
        }
    }

    // UPDATE CUSTOMER PROPERTY
    public function update(PropertyRequest $request, $uuid): JsonResponse
{
    $updateDetails = $request->validated();
    $customerIds = $updateDetails['customer_id'] ?? [];

    try {
        // Utilizar el servicio para actualizar la propiedad del cliente
        $customerProperty = $this->propertyService->updateCustomerProperty($updateDetails, $uuid, $customerIds);

        return ApiResponseClass::sendSimpleResponse(new PropertyResource($customerProperty), 200);

    } catch (\Exception $ex) {
        return response()->json(['message' => 'Error occurred while updating customer property', 'error' => $ex->getMessage()], 500);
    }
}

    // DELETE CUSTOMER PROPERTY
    public function destroy($uuid)
    {
        try {
            // Utilizar el servicio para eliminar la propiedad del cliente
            $customerProperty = $this->propertyService->deleteCustomerProperty($uuid);

            return ApiResponseClass::sendResponse('Customer Property Delete Successful', '', 200);

        } catch (\Exception $e) {
            // Registrar el mensaje de la excepción en el log
            Log::error('Error occurred while deleting customer property: ' . $e->getMessage());

            // Manejar cualquier excepción y devolver una respuesta de error
            return response()->json(['message' => 'Error occurred while deleting customer property', 'error' => $e->getMessage()], 500);
        }
    }

}

