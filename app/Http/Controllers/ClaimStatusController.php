<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\ClaimStatusRequest;
use App\Http\Resources\ClaimStatusResource;
use App\Services\ClaimStatusService;
use App\Traits\HandlesApiErrors;
use App\DTOs\ClaimStatusDTO;
use Ramsey\Uuid\Uuid;

class ClaimStatusController extends BaseController
{
    use HandlesApiErrors;

    protected $dataService;

    public function __construct(ClaimStatusService $dataService)
    {
        $this->middleware('check.permission:Super Admin')->only(['index', 'store', 'show', 'update', 'destroy']);
        $this->dataService = $dataService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $claimStatuses = $this->dataService->all();
            return ApiResponseClass::sendResponse(ClaimStatusResource::collection($claimStatuses), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving claim statuses');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClaimStatusRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = ClaimStatusDTO::fromArray($validatedData); 
            $claimStatus = $this->dataService->storeData($dto); 
            return ApiResponseClass::sendSimpleResponse(new ClaimStatusResource($claimStatus), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing claim status');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $claimStatus = $this->dataService->showData($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new ClaimStatusResource($claimStatus), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving claim status');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClaimStatusRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = ClaimStatusDTO::fromArray($request->validated());
            $claimStatus = $this->dataService->updateData($dto, $uuidObject);
            return ApiResponseClass::sendSimpleResponse(new ClaimStatusResource($claimStatus), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating claim status');
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
            return ApiResponseClass::sendResponse('Claim status deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting claim status');
        }
    }
}
