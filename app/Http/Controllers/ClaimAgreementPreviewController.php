<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Classes\ApiResponseClass;
use App\Http\Requests\ClaimAgreementPreviewRequest;
use App\Http\Resources\ClaimAgreementPreviewResource;
use Illuminate\Http\JsonResponse;

use App\Services\ClaimAgreementPreviewService;


class ClaimAgreementPreviewController extends BaseController
{
    protected $serviceData;

    public function __construct(ClaimAgreementPreviewService $serviceData)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only(['destroy']);
        
        $this->serviceData = $serviceData;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $claim_agreement = $this->serviceData->all();

        // if ($signatures->isEmpty()) {
        //     return response()->json(['message' => 'No signatures found'], 404);
        // }

         return ApiResponseClass::sendResponse(ClaimAgreementPreviewResource::collection($claim_agreement), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClaimAgreementPreviewRequest $request): JsonResponse
    {
        $claim_agreement = $this->serviceData->storeData($request->validated());

        return ApiResponseClass::sendSimpleResponse(new ClaimAgreementPreviewResource($claim_agreement), 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        $claim_agreement = $this->serviceData->showData($uuid);

        return ApiResponseClass::sendSimpleResponse(new ClaimAgreementPreviewResource($claim_agreement), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClaimAgreementPreviewRequest $request, string $uuid): JsonResponse
    {
        $claim_agreement = $this->serviceData->updateData($request->validated(), $uuid);

        return ApiResponseClass::sendSimpleResponse(new ClaimAgreementPreviewResource($claim_agreement), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->serviceData->deleteData($uuid);

        return ApiResponseClass::sendResponse('Signature deleted successfully', '', 200);
    }
}