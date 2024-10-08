<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\CauseOfLossRequest;
use App\Http\Resources\CauseOfLossResource;
use App\Services\CauseOfLossService;
use App\Traits\HandlesApiErrors;
use App\DTOs\CauseOfLossDTO;
use Ramsey\Uuid\Uuid;

class CauseOfLossController extends BaseController
{
    use HandlesApiErrors;

    protected $dataService;

    public function __construct(CauseOfLossService $dataService)
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
            $causeOfLosses = $this->dataService->all();
            return ApiResponseClass::sendResponse(CauseOfLossResource::collection($causeOfLosses), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving causes of loss');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CauseOfLossRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = CauseOfLossDTO::fromArray($validatedData); 
            $causeOfLoss = $this->dataService->storeData($dto); 
            return ApiResponseClass::sendSimpleResponse(new CauseOfLossResource($causeOfLoss), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing cause of loss');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $causeOfLoss = $this->dataService->showData($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new CauseOfLossResource($causeOfLoss), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving cause of loss');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CauseOfLossRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = CauseOfLossDTO::fromArray($request->validated()); // Usando DTO
            $causeOfLoss = $this->dataService->updateData($dto, $uuidObject); // Pasando DTO al servicio
            return ApiResponseClass::sendSimpleResponse(new CauseOfLossResource($causeOfLoss), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating cause of loss');
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
            return ApiResponseClass::sendResponse('Cause of loss deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting cause of loss');
        }
    }
}
