<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Classes\ApiResponseClass;
use App\Http\Requests\CompanySignatureRequest;
use App\Http\Resources\CompanySignatureResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\CompanySignatureService;

class CompanySignatureController extends BaseController
{
    protected $dataService;

    public function __construct(CompanySignatureService $dataService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only(['index', 'store', 'update', 'destroy']);
        
        $this->dataService = $dataService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $data = $this->dataService->all();

        if ($data === null) {
            return response()->json(['message' => 'No company signatures found or invalid data structure'], 404);
        }

        return ApiResponseClass::sendResponse(CompanySignatureResource::collection($data), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanySignatureRequest $request): JsonResponse
    {
        $details = $request->validated();
        $data = $this->dataService->storeData($details);

        return ApiResponseClass::sendSimpleResponse(new CompanySignatureResource($data), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompanySignatureRequest $request, string $uuid): JsonResponse
    {
        // Validar y obtener los detalles de la solicitud
        $details = $request->validated();
        $data = $this->dataService->updateData($details, $uuid);

        return ApiResponseClass::sendSimpleResponse(new CompanySignatureResource($data), 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        $data = $this->dataService->showData($uuid);

        if ($data === null) {
            return response()->json(['message' => 'Company Signature not found'], 404);
        }

        return ApiResponseClass::sendSimpleResponse(new CompanySignatureResource($data), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $data = $this->dataService->deleteData($uuid);

        if ($data === null) {
            return response()->json(['message' => 'Company Signature not found'], 404);
        }

        return response()->json(['message' => 'Company Signature deleted successfully'], 200);
    }
}
