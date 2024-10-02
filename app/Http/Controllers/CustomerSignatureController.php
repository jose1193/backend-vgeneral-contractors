<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\CustomerSignatureRequest;
use App\Http\Resources\CustomerSignatureResource;
use App\Services\CustomerSignatureService;
use App\Traits\HandlesApiErrors;
use App\DTOs\CustomerSignatureDTO;
use Ramsey\Uuid\Uuid;

class CustomerSignatureController extends BaseController
{
    use HandlesApiErrors;

    protected $dataService;

    public function __construct(CustomerSignatureService $dataService)
    {
        $this->middleware('check.permission:Super Admin')->only(['destroy']);
        $this->dataService = $dataService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $customerSignatures = $this->dataService->all();
            return ApiResponseClass::sendResponse(CustomerSignatureResource::collection($customerSignatures), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving customer signatures');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerSignatureRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = CustomerSignatureDTO::fromArray($validatedData);
            $customerSignature = $this->dataService->storeData($dto);
            return ApiResponseClass::sendSimpleResponse(new CustomerSignatureResource($customerSignature), 201);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing customer signature');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $customerSignature = $this->dataService->showData($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new CustomerSignatureResource($customerSignature), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving customer signature');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerSignatureRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $customerSignature = $this->dataService->updateData(CustomerSignatureDTO::fromArray($request->validated()), $uuidObject);
            return ApiResponseClass::sendSimpleResponse(new CustomerSignatureResource($customerSignature), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating customer signature');
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
            return ApiResponseClass::sendResponse('Customer signature deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting customer signature');
        }
    }
}