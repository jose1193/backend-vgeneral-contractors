<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\PublicCompanyRequest;
use App\Http\Resources\PublicCompanyResource;
use App\Services\PublicCompanyService;
use App\Traits\HandlesApiErrors;
use App\DTOs\PublicCompanyDTO;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Log;

class PublicCompanyController extends BaseController
{
    use HandlesApiErrors;

    protected $dataService;

    public function __construct(PublicCompanyService $dataService)
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
            $data = $this->dataService->all();
            return ApiResponseClass::sendResponse(PublicCompanyResource::collection($data), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving public companies');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PublicCompanyRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = PublicCompanyDTO::fromArray($validatedData);
            
            $data = $this->dataService->storeData($dto);
            return ApiResponseClass::sendSimpleResponse(new PublicCompanyResource($data), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing public company');
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
                return response()->json(['message' => 'Public Company not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new PublicCompanyResource($data), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving public company');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PublicCompanyRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = PublicCompanyDTO::fromArray($request->validated());
            
            $data = $this->dataService->updateData($dto, $uuidObject);
            return ApiResponseClass::sendSimpleResponse(new PublicCompanyResource($data), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating public company');
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
            return ApiResponseClass::sendResponse('Public company deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting public company');
        }
    }
}