<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\ClaimAgreementFullRequest; 
use App\Http\Resources\ClaimAgreementFullResource; 
use App\Services\ClaimAgreementFullService; 

class ClaimAgreementFullController extends BaseController
{
    protected $serviceData;

    public function __construct(ClaimAgreementFullService $serviceData)
    {
        // Middleware for permissions, adjust as necessary
        $this->middleware('check.permission:Super Admin')->only(['destroy']);
        
        $this->serviceData = $serviceData;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $claim_agreements = $this->serviceData->all();

        // if ($claim_agreements->isEmpty()) {
        //     return response()->json(['message' => 'No claim agreements found'], 404);
        // }

        return ApiResponseClass::sendResponse(ClaimAgreementFullResource::collection($claim_agreements), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClaimAgreementFullRequest $request): JsonResponse
    {
    try {
        $claim_agreement = $this->serviceData->storeData($request->validated());
        return ApiResponseClass::sendSimpleResponse(new ClaimAgreementFullResource($claim_agreement), 200);
    } catch (Exception $e) {
        // Aquí manejas el error y devuelves una respuesta JSON
        return response()->json([
            'error' => true,
            'message' => 'Error storing claim agreement: ' . $e->getMessage(),
            'code' => 500 // O el código adecuado según la lógica de negocio
        ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $claim_agreement = $this->serviceData->showData($id);

        return ApiResponseClass::sendSimpleResponse(new ClaimAgreementFullResource($claim_agreement), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClaimAgreementFullRequest $request, string $id): JsonResponse
    {
        $claim_agreement = $this->serviceData->updateData($request->validated(), $id);

        return ApiResponseClass::sendSimpleResponse(new ClaimAgreementFullResource($claim_agreement), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->serviceData->deleteData($id);

        return ApiResponseClass::sendResponse('Claim agreement deleted successfully', '', 200);
    }
}
