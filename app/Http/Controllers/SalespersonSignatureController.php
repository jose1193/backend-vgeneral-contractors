<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\SalespersonSignatureRequest;
use App\Http\Resources\SalespersonSignatureResource;
use App\Services\SalespersonSignatureService;
use App\Traits\HandlesApiErrors;
use App\DTOs\SalespersonSignatureDTO;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;

class SalespersonSignatureController extends BaseController
{
    use HandlesApiErrors;

    protected $serviceData;

    public function __construct(SalespersonSignatureService $serviceData)
    {
        $this->middleware('check.permission:Salesperson')->only(['index', 'store', 'update']);
        $this->serviceData = $serviceData;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $salespersonSignatures = $this->serviceData->all();
            return ApiResponseClass::sendResponse(SalespersonSignatureResource::collection($salespersonSignatures), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving salesperson signatures');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SalespersonSignatureRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = SalespersonSignatureDTO::fromArray($validatedData);
            $salespersonSignature = $this->serviceData->storeData($dto);
            return ApiResponseClass::sendSimpleResponse(new SalespersonSignatureResource($salespersonSignature), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing salesperson signature');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $salespersonSignature = $this->serviceData->showData($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new SalespersonSignatureResource($salespersonSignature), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving salesperson signature');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SalespersonSignatureRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = SalespersonSignatureDTO::fromArray($request->validated());
            $salespersonSignature = $this->serviceData->updateData($dto, $uuidObject);
            return ApiResponseClass::sendSimpleResponse(new SalespersonSignatureResource($salespersonSignature), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating salesperson signature');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $this->serviceData->deleteData($uuidObject);
            return ApiResponseClass::sendResponse('Salesperson signature deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting salesperson signature');
        }
    }

}