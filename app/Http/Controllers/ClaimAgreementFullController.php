<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\ClaimAgreementFullRequest;
use App\Http\Resources\ClaimAgreementFullResource;
use App\Services\ClaimAgreementFullService;
use App\Traits\HandlesApiErrors;
use App\DTOs\ClaimAgreementFullDTO;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;

class ClaimAgreementFullController extends BaseController
{
    use HandlesApiErrors;

    protected $serviceData;

    public function __construct(ClaimAgreementFullService $serviceData)
    {
        $this->middleware('check.permission:Salesperson')->only(['index', 'store', 'update']);
        $this->middleware('check.permission:Super Admin')->only(['destroy']);
        $this->serviceData = $serviceData;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $claim_agreements = $this->serviceData->all();
            //if ($claim_agreements->isEmpty()) {
                //return response()->json(['message' => 'No claim agreements found'], 404);
            //}
            return ApiResponseClass::sendResponse(ClaimAgreementFullResource::collection($claim_agreements), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error fetching claim agreements');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClaimAgreementFullRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            $dto = ClaimAgreementFullDTO::fromArray($validatedData);
            $claim_agreement = $this->serviceData->storeData($dto);
            
            DB::commit();
            return ApiResponseClass::sendSimpleResponse(new ClaimAgreementFullResource($claim_agreement), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleError($e, 'Error creating claim agreement');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $claim_agreement = $this->serviceData->showData($uuidObject);
            if (!$claim_agreement) {
                return response()->json(['message' => 'Claim agreement not found'], 404);
            }
            return ApiResponseClass::sendSimpleResponse(new ClaimAgreementFullResource($claim_agreement), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error fetching claim agreement');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClaimAgreementFullRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $validatedData = $request->validated();
            $dto = ClaimAgreementFullDTO::fromArray($validatedData);
            $claim_agreement = $this->serviceData->updateData($dto, $uuidObject);
            
            return ApiResponseClass::sendSimpleResponse(new ClaimAgreementFullResource($claim_agreement), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating claim agreement');
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
            $this->serviceData->deleteData($uuidObject);
            DB::commit();
            return ApiResponseClass::sendResponse('Claim agreement deleted successfully', '', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleError($e, 'Error deleting claim agreement');
        }
    }
}