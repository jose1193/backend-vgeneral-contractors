<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\CustomerSignatureRequest; 
use App\Http\Resources\CustomerSignatureResource; 
use App\Services\CustomerSignatureService; 

class CustomerSignatureController extends BaseController
{
    protected $serviceData;

    public function __construct(CustomerSignatureService $serviceData)
    {
        // Middleware para permisos, ajústalo según sea necesario
        $this->middleware('check.permission:Super Admin')->only(['destroy']);
        
        $this->serviceData = $serviceData;
    }

    /**
     * Muestra una lista de recursos.
     */
    public function index(): JsonResponse
    {
        $customer_signatures = $this->serviceData->all();

        return ApiResponseClass::sendResponse(CustomerSignatureResource::collection($customer_signatures), 200);
    }

    /**
     * Almacena un recurso recién creado en el almacenamiento.
     */
    public function store(CustomerSignatureRequest $request): JsonResponse
    {
        $customer_signature = $this->serviceData->storeData($request->validated());

        return ApiResponseClass::sendSimpleResponse(new CustomerSignatureResource($customer_signature), 201);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(string $uuid): JsonResponse
    {
        $customer_signature = $this->serviceData->showData($uuid);

        return ApiResponseClass::sendSimpleResponse(new CustomerSignatureResource($customer_signature), 200);
    }

    /**
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(CustomerSignatureRequest $request, string $uuid): JsonResponse
    {
        $customer_signature = $this->serviceData->updateData($request->validated(), $uuid);

        return ApiResponseClass::sendSimpleResponse(new CustomerSignatureResource($customer_signature), 200);
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->serviceData->deleteData($uuid);

        return ApiResponseClass::sendResponse('Customer signature deleted successfully', '', 200);
    }
}
