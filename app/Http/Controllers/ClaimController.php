<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\ClaimRequest;
use App\Http\Resources\ClaimResource;
use App\Services\ClaimService;
use App\Traits\HandlesApiErrors;
use App\DTOs\ClaimDTO;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClaimController extends BaseController
{
    use HandlesApiErrors;

    protected $claimService;

    public function __construct(ClaimService $claimService)
    {
        $this->middleware('check.permission:Salesperson')->only(['index', 'store', 'update']);
        $this->claimService = $claimService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $claims = $this->claimService->all();
            if ($claims->isEmpty()) {
                return response()->json(['message' => 'No claims found'], 404);
            }
            return ApiResponseClass::sendResponse(ClaimResource::collection($claims), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error fetching claims');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClaimRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            $dto = ClaimDTO::fromArray($validatedData);
            
            $causeOfLoss = $request->input('cause_of_loss_id', []);
            $technicalIds = $request->get('technical_user_id', []);
            $serviceRequestIds = $request->get('service_request_id', []);
            
            $claim = $this->claimService->storeData($dto, $technicalIds, $serviceRequestIds, $causeOfLoss);
            
            DB::commit();
            return ApiResponseClass::sendSimpleResponse(new ClaimResource($claim), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleError($e, 'Error creating claim');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $claim = $this->claimService->showData($uuidObject);
            if (!$claim) {
                return response()->json(['message' => 'Claim not found'], 404);
            }
            return ApiResponseClass::sendSimpleResponse(new ClaimResource($claim), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error fetching claim');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClaimRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = ClaimDTO::fromArray($request->validated());
            
            $causeOfLoss = $request->input('cause_of_loss_id', []);
            $technicalIds = $request->get('technical_user_id', []);
            $serviceRequestIds = $request->get('service_request_id', []);
            
            $claim = $this->claimService->updateData($dto, $uuidObject, $technicalIds, $serviceRequestIds, $causeOfLoss);
            
            return ApiResponseClass::sendSimpleResponse(new ClaimResource($claim), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating claim');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        DB::beginTransaction();
        try {
            $uuidObject = Uuid::fromString($uuid);
            $this->claimService->deleteData($uuidObject);
            DB::commit();
            return ApiResponseClass::sendResponse('Claim deleted successfully', '', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleError($e, 'Error deleting claim');
        }
    }

    /**
     * Restore the specified resource.
     */
    public function restore(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $claim = $this->claimService->restoreData($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new ClaimResource($claim), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error occurred while restoring claim');
        }
    }
}