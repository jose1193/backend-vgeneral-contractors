<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DocumentTemplateRequest; 
use App\Http\Resources\DocumentTemplateResource; 
use App\Services\DocumentTemplateService;
use App\Classes\ApiResponseClass; 

class DocumentTemplateController extends BaseController
{
    protected $service;

    public function __construct(DocumentTemplateService $service)
    {
        // Middleware para permisos, ajusta según sea necesario
         $this->middleware('check.permission:Super Admin')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $documentTemplates = $this->service->all();

        return ApiResponseClass::sendResponse(DocumentTemplateResource::collection($documentTemplates), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentTemplateRequest $request): JsonResponse
    {
        $documentTemplate = $this->service->storeData($request->validated());

        return ApiResponseClass::sendSimpleResponse(new DocumentTemplateResource($documentTemplate), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        $documentTemplate = $this->service->showData($uuid);

        return ApiResponseClass::sendSimpleResponse(new DocumentTemplateResource($documentTemplate), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentTemplateRequest $request, string $uuid): JsonResponse
    {
        $documentTemplate = $this->service->updateData($request->validated(), $uuid);

        return ApiResponseClass::sendSimpleResponse(new DocumentTemplateResource($documentTemplate), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->service->deleteData($uuid);

        return ApiResponseClass::sendResponse('Document template deleted successfully', '', 200);
    }
}