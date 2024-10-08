<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\TypeDamageRequest;
use App\Http\Resources\TypeDamageResource;
use App\Services\TypeDamageService;
use App\Traits\HandlesApiErrors;
use App\DTOs\TypeDamageDTO;
use Ramsey\Uuid\Uuid;

class TypeDamageController extends BaseController
{
    use HandlesApiErrors;

    protected $dataService;

    public function __construct(TypeDamageService $dataService)
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
            $typeDamages = $this->dataService->all();
            return ApiResponseClass::sendResponse(TypeDamageResource::collection($typeDamages), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving type damages');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TypeDamageRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = TypeDamageDTO::fromArray($validatedData);
            
            $typeDamage = $this->dataService->storeData($dto);
            
            return ApiResponseClass::sendSimpleResponse(new TypeDamageResource($typeDamage), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing type damage');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $typeDamage = $this->dataService->showData($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new TypeDamageResource($typeDamage), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving type damage');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TypeDamageRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = TypeDamageDTO::fromArray($request->validated()); 
            $typeDamage = $this->dataService->updateData($dto, $uuidObject); 
            return ApiResponseClass::sendSimpleResponse(new TypeDamageResource($typeDamage), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating type damage');
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
            return ApiResponseClass::sendResponse('Type damage deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting type damage');
        }
    }
}