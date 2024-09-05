<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\ScopeSheetZoneRequest; 
use App\Http\Resources\ScopeSheetZoneResource; 
use App\Services\ScopeSheetZoneService;


class ScopeSheetZoneController extends BaseController
{
    protected $serviceData;

    public function __construct(ScopeSheetZoneService $serviceData)
    {
        // Middleware para permisos, ajústalo según sea necesario
        $this->middleware('check.permission:Director Assistant')->only(['destroy']);
        
        $this->serviceData = $serviceData;
    }

    /**
     * Muestra una lista de recursos.
     */
    public function index(): JsonResponse
    {
        $scope_sheets = $this->serviceData->all();

        return ApiResponseClass::sendResponse(ScopeSheetZoneResource::collection($scope_sheets), 200);
    }

    /**
     * Almacena un recurso recién creado en el almacenamiento.
     */
    public function store(ScopeSheetZoneRequest $request): JsonResponse
    {
        $scope_sheet = $this->serviceData->storeData($request->validated());

        return ApiResponseClass::sendSimpleResponse(new ScopeSheetZoneResource($scope_sheet), 200);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(string $uuid): JsonResponse
    {
        $scope_sheet = $this->serviceData->showData($uuid);

        return ApiResponseClass::sendSimpleResponse(new ScopeSheetZoneResource($scope_sheet), 200);
    }

    /**
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(ScopeSheetZoneRequest $request, string $uuid): JsonResponse
    {
        $scope_sheet = $this->serviceData->updateData($request->validated(), $uuid);

        return ApiResponseClass::sendSimpleResponse(new ScopeSheetZoneResource($scope_sheet), 200);
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->serviceData->deleteData($uuid);

        return ApiResponseClass::sendResponse('Scope sheet Zone deleted successfully', '', 200);
    }
}
