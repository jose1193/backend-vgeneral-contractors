<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DocumentTemplateAllianceRequest;
use App\Http\Resources\DocumentTemplateAllianceResource;
use App\Services\DocumentTemplateAllianceService;
use App\Classes\ApiResponseClass;
use App\Traits\HandlesApiErrors;
use App\DTOs\DocumentTemplateAllianceDTO;
use Ramsey\Uuid\Uuid;

class DocumentTemplateAllianceController extends BaseController
{
    use HandlesApiErrors;

    protected $service;

    public function __construct(DocumentTemplateAllianceService $service)
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
            $documentTemplateAlliances = $this->service->all();
            return ApiResponseClass::sendResponse(DocumentTemplateAllianceResource::collection($documentTemplateAlliances), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving document template alliances');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentTemplateAllianceRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = DocumentTemplateAllianceDTO::fromArray($validatedData);
            $documentTemplateAlliance = $this->service->storeData($dto);
            return ApiResponseClass::sendSimpleResponse(new DocumentTemplateAllianceResource($documentTemplateAlliance), 201);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing document template alliance');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $documentTemplateAlliance = $this->service->showData($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new DocumentTemplateAllianceResource($documentTemplateAlliance), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving document template alliance');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentTemplateAllianceRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = DocumentTemplateAllianceDTO::fromArray($request->validated());
            $documentTemplateAlliance = $this->service->updateData($dto, $uuidObject);
            return ApiResponseClass::sendSimpleResponse(new DocumentTemplateAllianceResource($documentTemplateAlliance), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating document template alliance');
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
            return ApiResponseClass::sendResponse('Document template alliance deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting document template alliance');
        }
    }
}