<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

use App\Classes\ApiResponseClass;

use App\Http\Requests\ClaimRequest;
use App\Http\Resources\ClaimResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\ClaimService;

class ClaimController extends BaseController
{
    protected $claimService;

    public function __construct(ClaimService $claimService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Director Assistant')->only(['update', 'destroy']);
        
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
            Log::error('Error fetching claims: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Error fetching claims' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClaimRequest $request): JsonResponse
{
    DB::beginTransaction();

    try {
        // Extraer el array de IDs de alianzas
        $alliancesIds = $request->get('alliance_company_id', []);
        $technicalIds = $request->get('technical_user_id', []);
        $serviceRequestIds = $request->get('service_request_id', []);
        // Pasar ambos parámetros al método storeData
        $claim = $this->claimService->storeData($request->validated(), $alliancesIds, $technicalIds,$serviceRequestIds);

        DB::commit();

        return ApiResponseClass::sendSimpleResponse(new ClaimResource($claim), 200);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error creating claim: ' . $e->getMessage(), ['exception' => $e]);
        return response()->json(['message' => 'Error creating claim'. $e->getMessage()], 500);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $claim = $this->claimService->showData($uuid);

            if (!$claim) {
                return response()->json(['message' => 'Claim not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new ClaimResource($claim), 200);
        } catch (\Exception $e) {
            Log::error('Error fetching claim: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Error fetching claim'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClaimRequest $request, string $uuid): JsonResponse
{
    try {
        // Extraer el array de IDs de alianzas y técnicos
        $alliancesIds = $request->get('alliance_company_id', []);
        $technicalIds = $request->get('technical_user_id', []);
        $serviceRequestIds = $request->get('service_request_id', []);
        // Pasar ambos parámetros al método updateData
        $claim = $this->claimService->updateData($request->validated(), $uuid, $alliancesIds, $technicalIds, $serviceRequestIds);

        return ApiResponseClass::sendSimpleResponse(new ClaimResource($claim), 200);
    } catch (\Exception $e) {
        Log::error('Error updating claim: ' . $e->getMessage(), ['exception' => $e]);
        return response()->json(['message' => 'Error updating claim: ' . $e->getMessage()], 500);
    }
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        DB::beginTransaction();

        try {
            $this->claimService->deleteData($uuid);
            DB::commit();

            return ApiResponseClass::sendResponse('Claim deleted successfully', '', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting claim: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Error deleting claim'. $e->getMessage()], 500);
        }
    }



}