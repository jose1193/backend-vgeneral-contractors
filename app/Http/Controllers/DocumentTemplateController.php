<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DocumentTemplateRequest;
use App\Http\Resources\DocumentTemplateResource;
use App\Services\DocumentTemplateService;
use App\Classes\ApiResponseClass;
use App\Traits\HandlesApiErrors;
use App\DTOs\DocumentTemplateDTO;
use Ramsey\Uuid\Uuid;

class DocumentTemplateController extends BaseController
{
    use HandlesApiErrors;

    protected $service;

    public function __construct(DocumentTemplateService $service)
    {
        $this->middleware('check.permission:Super Admin')->only(['index', 'store', 'update', 'destroy']);
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $documentTemplates = $this->service->all();
            return ApiResponseClass::sendResponse(DocumentTemplateResource::collection($documentTemplates), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving document templates');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentTemplateRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = DocumentTemplateDTO::fromArray($validatedData);
            $documentTemplate = $this->service->storeData($dto);
            return ApiResponseClass::sendSimpleResponse(new DocumentTemplateResource($documentTemplate), 201);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing document template');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $documentTemplate = $this->service->showData($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new DocumentTemplateResource($documentTemplate), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving document template');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentTemplateRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = DocumentTemplateDTO::fromArray($request->validated());
            $documentTemplate = $this->service->updateData($dto, $uuidObject);
            return ApiResponseClass::sendSimpleResponse(new DocumentTemplateResource($documentTemplate), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating document template');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $this->service->deleteData($uuidObject);
            return ApiResponseClass::sendResponse('Document template deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting document template');
        }
    }
}