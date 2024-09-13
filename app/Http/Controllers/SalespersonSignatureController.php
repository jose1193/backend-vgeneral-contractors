<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\SalespersonSignatureRequest; 
use App\Http\Resources\SalespersonSignatureResource; 
use App\Services\SalespersonSignatureService; 

class SalespersonSignatureController extends BaseController
{
    protected $serviceData;

    public function __construct(SalespersonSignatureService $serviceData)
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
        $salesperson_signatures = $this->serviceData->all();

        return ApiResponseClass::sendResponse(SalespersonSignatureResource::collection($salesperson_signatures), 200);
    }

    /**
     * Almacena un recurso recién creado en el almacenamiento.
     */
    public function store(SalespersonSignatureRequest $request): JsonResponse
    {
        $salesperson_signature = $this->serviceData->storeData($request->validated());

        return ApiResponseClass::sendSimpleResponse(new SalespersonSignatureResource($salesperson_signature), 200);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(string $uuid): JsonResponse
    {
        $salesperson_signature = $this->serviceData->showData($uuid);

        return ApiResponseClass::sendSimpleResponse(new SalespersonSignatureResource($salesperson_signature), 200);
    }

    /**
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(SalespersonSignatureRequest $request, string $uuid): JsonResponse
    {
        $salesperson_signature = $this->serviceData->updateData($request->validated(), $uuid);

        return ApiResponseClass::sendSimpleResponse(new SalespersonSignatureResource($salesperson_signature), 200);
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->serviceData->deleteData($uuid);

        return ApiResponseClass::sendResponse('Sales Person Signature deleted successfully', '', 200);
    }


    
}
