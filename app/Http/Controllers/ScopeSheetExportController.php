<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\ScopeSheetExportRequest; 
use App\Http\Resources\ScopeSheetExportResource; 
use App\Services\ScopeSheetExportService;
use App\Traits\HandlesApiErrors; 


class ScopeSheetExportController extends BaseController
{
    use HandlesApiErrors; 
    protected $serviceData;

    public function __construct(ScopeSheetExportService $serviceData)
    {
    $this->middleware('check.permission:Lead')->only(['show','store']);
    $this->serviceData = $serviceData;
    }


    /**
     * Muestra una lista de recursos.
     */
     public function index(): JsonResponse
    {
        try {
            $scope_sheet_exports = $this->serviceData->all();
            return ApiResponseClass::sendResponse(ScopeSheetExportResource::collection($scope_sheet_exports), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving scope sheet exports');
        }
    }

    /**
     * Almacena un recurso recién creado en el almacenamiento.
     */
    public function store(ScopeSheetExportRequest $request): JsonResponse
    {
        try {
            $scope_sheet_export = $this->serviceData->storeData($request->validated());
            return ApiResponseClass::sendSimpleResponse(new ScopeSheetExportResource($scope_sheet_export), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing scope sheet export');
        }
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $scope_sheet_export = $this->serviceData->showData($uuid);
            return ApiResponseClass::sendSimpleResponse(new ScopeSheetExportResource($scope_sheet_export), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving scope sheet export');
        }
    }

    /**
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(ScopeSheetExportRequest $request, string $uuid): JsonResponse
    {
        try {
            $scope_sheet_export = $this->serviceData->updateData($request->validated(), $uuid);
            return ApiResponseClass::sendSimpleResponse(new ScopeSheetExportResource($scope_sheet_export), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating scope sheet export');
        }
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->serviceData->deleteData($uuid);
            return ApiResponseClass::sendResponse('Scope sheet export deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting scope sheet export');
        }
    }
}
