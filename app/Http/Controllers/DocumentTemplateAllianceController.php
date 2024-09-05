<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DocumentTemplateAllianceRequest; 
use App\Http\Resources\DocumentTemplateAllianceResource; 
use App\Services\DocumentTemplateAllianceService;
use App\Classes\ApiResponseClass; 

class DocumentTemplateAllianceController extends BaseController
{
    protected $service;

    public function __construct(DocumentTemplateAllianceService $service)
    {
        // Middleware for permissions, adjust as necessary
        $this->middleware('check.permission:Super Admin')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $documentTemplateAlliances = $this->service->all();

        return ApiResponseClass::sendResponse(DocumentTemplateAllianceResource::collection($documentTemplateAlliances), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentTemplateAllianceRequest $request): JsonResponse
    {
        $documentTemplateAlliance = $this->service->storeData($request->validated());

        return ApiResponseClass::sendSimpleResponse(new DocumentTemplateAllianceResource($documentTemplateAlliance), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        $documentTemplateAlliance = $this->service->showData($uuid);

        return ApiResponseClass::sendSimpleResponse(new DocumentTemplateAllianceResource($documentTemplateAlliance), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentTemplateAllianceRequest $request, string $uuid): JsonResponse
    {
        $documentTemplateAlliance = $this->service->updateData($request->validated(), $uuid);

        return ApiResponseClass::sendSimpleResponse(new DocumentTemplateAllianceResource($documentTemplateAlliance), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->service->deleteData($uuid);

        return ApiResponseClass::sendResponse('Document template alliance deleted successfully', '', 200);
    }
}
