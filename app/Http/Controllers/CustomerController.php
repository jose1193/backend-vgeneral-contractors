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

use App\Interfaces\CustomerRepositoryInterface;
use App\Http\Requests\CustomerRequest;
use App\Classes\ApiResponseClass;
use App\Http\Resources\CustomerResource;

use App\Services\CustomerService;

class CustomerController extends BaseController
{
    protected $cacheTime = 720;
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Lead')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        
        $this->customerService = $customerService;
    }

    // SHOW LIST OF CUSTOMERS
    public function index(): JsonResponse
    {
        try {
            // Obtener todos los clientes usando el servicio
            $customers = $this->customerService->all();

            if ($customers === null) {
                return response()->json(['message' => 'No customers found or invalid data structure'], 404);
            }

            return ApiResponseClass::sendResponse(CustomerResource::collection($customers), 200);

        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching customers: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Database error occurred while fetching customers'], 500);

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching customers: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching customers'], 500);
        }
    }

   
    // STORE CUSTOMER
    public function store(CustomerRequest $request)
    {
        try {
            // Validar y obtener los detalles de la solicitud
            $details = $request->validated();
           
            // Utilizar el servicio para almacenar el cliente
            $customer = $this->customerService->storeCustomer($details);
            
            return ApiResponseClass::sendSimpleResponse(new CustomerResource($customer), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while creating customer', 'error' => $ex->getMessage()], 500);
        }
    }

    // UPDATE CUSTOMER
    public function update(CustomerRequest $request, $uuid): JsonResponse
    {
        $updateDetails = $request->validated();

        try {
            // Utilizar el servicio para actualizar el cliente
            $customer = $this->customerService->updateCustomer($updateDetails, $uuid);

            return ApiResponseClass::sendSimpleResponse(new CustomerResource($customer), 200);

        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while updating customer', 'error' => $ex->getMessage()], 500);
        }
    }

    // SHOW CUSTOMER
    public function show($uuid)
    {
        try {
            // Utilizar el servicio para obtener el cliente
            $customer = $this->customerService->showCustomer($uuid);

            return ApiResponseClass::sendSimpleResponse(new CustomerResource($customer), 200);

        } catch (\Exception $e) {
            // Registrar el mensaje de la excepción en el log
            Log::error('Error occurred while fetching customer: ' . $e->getMessage());

            // Manejar cualquier excepción y devolver una respuesta de error
            return response()->json(['message' => 'Error occurred while fetching customer', 'error' => $e->getMessage()], 500);
        }
    }

    // DELETE CUSTOMER
    public function destroy($uuid)
    {
        try {
            // Utilizar el servicio para obtener el cliente
            $customer = $this->customerService->deleteCustomer($uuid);

            return ApiResponseClass::sendResponse('Customer Delete Successful', '', 200);

        } catch (\Exception $e) {
            // Registrar el mensaje de la excepción en el log
            Log::error('Error occurred while deleting customer: ' . $e->getMessage());

            // Manejar cualquier excepción y devolver una respuesta de error
            return response()->json(['message' => 'Error occurred while deleting customer', 'error' => $e->getMessage()], 500);
        }
    }

    // RESTORE CUSTOMER
    public function restore($uuid)
    {
        try {
            // Utilizar el servicio para restaurar el cliente
            $customer = $this->customerService->restoreCustomer($uuid);

            return ApiResponseClass::sendSimpleResponse(new CustomerResource($customer), 200);

        } catch (\Exception $e) {
            // Registrar el mensaje de la excepción en el log
            Log::error('Error occurred while restoring customer: ' . $e->getMessage());

            // Manejar cualquier excepción y devolver una respuesta de error
            return response()->json(['message' => 'Error occurred while restoring customer', 'error' => $e->getMessage()], 500);
        }
    }
}
