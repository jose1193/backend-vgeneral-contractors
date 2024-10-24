<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\AllianceCompanyRequest;
use App\Http\Resources\AllianceCompanyResource;
use App\Services\AllianceCompanyService;
use App\Traits\HandlesApiErrors;
use App\DTOs\AllianceCompanyDTO;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Log;

class AllianceCompanyController extends BaseController 
{
    use HandlesApiErrors;

    protected $dataService;

    public function __construct(AllianceCompanyService $dataService)
    {
        $this->middleware('check.permission:Salesperson')->only(['index', 'show','store','update']);
        $this->dataService = $dataService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $data = $this->dataService->all();
            return ApiResponseClass::sendResponse(AllianceCompanyResource::collection($data), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving alliance companies');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AllianceCompanyRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = AllianceCompanyDTO::fromArray($validatedData);
            
            $data = $this->dataService->storeData($dto);
            return ApiResponseClass::sendSimpleResponse(new AllianceCompanyResource($data), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing alliance company');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $data = $this->dataService->showData($uuidObject);
            
            if ($data === null) {
                return response()->json(['message' => 'Alliance Company not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new AllianceCompanyResource($data), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving alliance company');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AllianceCompanyRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = AllianceCompanyDTO::fromArray($request->validated());
            
            $data = $this->dataService->updateData($dto, $uuidObject);
            return ApiResponseClass::sendSimpleResponse(new AllianceCompanyResource($data), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating alliance company');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $this->dataService->deleteData($uuidObject);
            return ApiResponseClass::sendResponse('Alliance company deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting alliance company');
        }
    }
}