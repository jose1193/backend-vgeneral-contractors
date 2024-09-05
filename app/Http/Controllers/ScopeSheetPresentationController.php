<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\ScopeSheetPresentationRequest; 
use App\Http\Resources\ScopeSheetPresentationResource; 
use App\Services\ScopeSheetPresentationService; 
use App\Http\Requests\ScopeSheetPresentationPhotoRequest;

class ScopeSheetPresentationController extends BaseController
{
    protected $serviceData;

    public function __construct(ScopeSheetPresentationService $serviceData)
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
        $scope_sheets_presentations = $this->serviceData->all();

        return ApiResponseClass::sendResponse(ScopeSheetPresentationResource::collection($scope_sheets_presentations), 200);
    }

    /**
     * Almacena un recurso recién creado en el almacenamiento.
     */
    public function store(ScopeSheetPresentationRequest $request): JsonResponse
    {
        $scope_sheet_presentation = $this->serviceData->storeData($request->validated());

        return ApiResponseClass::sendSimpleResponse(new ScopeSheetPresentationResource($scope_sheet_presentation), 200);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(string $uuid): JsonResponse
    {
        $scope_sheet_presentation = $this->serviceData->showData($uuid);

        return ApiResponseClass::sendSimpleResponse(new ScopeSheetPresentationResource($scope_sheet_presentation), 200);
    }

    /**
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(ScopeSheetPresentationRequest $request, string $uuid): JsonResponse
    {
        $scope_sheet_presentation = $this->serviceData->updateData($request->validated(), $uuid);

        return ApiResponseClass::sendSimpleResponse(new ScopeSheetPresentationResource($scope_sheet_presentation), 200);
    }

     public function reorderImages(ScopeSheetPresentationRequest $request): JsonResponse
    {
    $validatedData = $request->validated();
    $scope_sheet_presentation = $this->serviceData->reorderImages(
        $validatedData['scope_sheet_id'],
        $validatedData['ordered_photo_ids']
    );
    
    return ApiResponseClass::sendSimpleResponse(new ScopeSheetPresentationResource($scope_sheet_presentation), 200);
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->serviceData->deleteData($uuid);

        return ApiResponseClass::sendResponse('Scope sheet presentation deleted successfully', '', 200);
    }
}
