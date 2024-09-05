<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\ScopeSheetZonePhotoRequest; 
use App\Http\Resources\ScopeSheetZonePhotoResource; 
use App\Services\ScopeSheetZonePhotoService;

class ScopeSheetZonePhotoController extends BaseController
{
    protected $serviceData;

    public function __construct(ScopeSheetZonePhotoService $serviceData)
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
        $scope_sheet_zone_photos = $this->serviceData->all();

        return ApiResponseClass::sendResponse(ScopeSheetZonePhotoResource::collection($scope_sheet_zone_photos), 200);
    }

    /**
     * Almacena un recurso recién creado en el almacenamiento.
     */
    public function store(ScopeSheetZonePhotoRequest $request): JsonResponse
    {
        $scope_sheet_zone_photo = $this->serviceData->storeData($request->validated());

        return ApiResponseClass::sendSimpleResponse(new ScopeSheetZonePhotoResource($scope_sheet_zone_photo), 200);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(string $uuid): JsonResponse
    {
        $scope_sheet_zone_photo = $this->serviceData->showData($uuid);

        return ApiResponseClass::sendSimpleResponse(new ScopeSheetZonePhotoResource($scope_sheet_zone_photo), 200);
    }

    /**
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(ScopeSheetZonePhotoRequest $request, string $uuid): JsonResponse
    {
        $scope_sheet_zone_photo = $this->serviceData->updateData($request->validated(), $uuid);

        return ApiResponseClass::sendSimpleResponse(new ScopeSheetZonePhotoResource($scope_sheet_zone_photo), 200);
    }

     public function reorderImages(ScopeSheetZonePhotoRequest $request): JsonResponse
{
    $validatedData = $request->validated();
    $scope_sheet_presentation = $this->serviceData->reorderImages(
        $validatedData['scope_sheet_zone_id'],
        $validatedData['ordered_photo_ids']
    );
    
    return ApiResponseClass::sendSimpleResponse(new ScopeSheetZonePhotoResource($scope_sheet_presentation), 200);
}

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->serviceData->deleteData($uuid);

        return ApiResponseClass::sendResponse('Scope sheet zone photo deleted successfully', '', 200);
    }
}
