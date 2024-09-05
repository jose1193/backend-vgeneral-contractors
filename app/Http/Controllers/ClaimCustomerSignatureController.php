<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Classes\ApiResponseClass;
use App\Http\Requests\ClaimCustomerSignatureRequest;
use App\Http\Resources\ClaimCustomerSignatureResource;
use Illuminate\Http\JsonResponse;
use App\Services\ClaimCustomerSignatureService;

class ClaimCustomerSignatureController extends BaseController
{
    protected $signatureService;

    public function __construct(ClaimCustomerSignatureService $signatureService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only(['destroy']);
        
        $this->signatureService = $signatureService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $signatures = $this->signatureService->all();

        //if ($signatures->isEmpty()) {
            //return response()->json(['message' => 'No signatures found'], 404);
        //}

        return ApiResponseClass::sendResponse(ClaimCustomerSignatureResource::collection($signatures), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClaimCustomerSignatureRequest $request): JsonResponse
    {
        $signature = $this->signatureService->storeSignature($request->validated());

        return ApiResponseClass::sendSimpleResponse(new ClaimCustomerSignatureResource($signature), 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        $signature = $this->signatureService->showData($uuid);

        if (!$signature) {
            return response()->json(['message' => 'Signature not found'], 404);
        }

        return ApiResponseClass::sendSimpleResponse(new ClaimCustomerSignatureResource($signature), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClaimCustomerSignatureRequest $request, string $uuid): JsonResponse
    {
        $signature = $this->signatureService->updateSignature($request->validated(), $uuid);

        return ApiResponseClass::sendSimpleResponse(new ClaimCustomerSignatureResource($signature), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->signatureService->deleteData($uuid);

        return ApiResponseClass::sendResponse('Signature deleted successfully', '', 200);
    }
}
