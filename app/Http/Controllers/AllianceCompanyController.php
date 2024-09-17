<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

use App\Classes\ApiResponseClass;
use App\Http\Requests\AllianceCompanyRequest;
use App\Http\Resources\AllianceCompanyResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\AllianceCompanyService;


class AllianceCompanyController extends BaseController
{
    protected $dataService;

    public function __construct(AllianceCompanyService $dataService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only(['store', 'update', 'destroy']);

        $this->dataService = $dataService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $data = $this->dataService->all();

            if ($data === null) {
                return response()->json(['message' => 'No alliance companies found or invalid data structure'], 404);
            }

            return ApiResponseClass::sendResponse(AllianceCompanyResource::collection($data), 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching Alliance Companies: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching Alliance Companies'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AllianceCompanyRequest $request): JsonResponse
    {
        try {
            $details = $request->validated();
            $data = $this->dataService->store($details);

            return ApiResponseClass::sendSimpleResponse(new AllianceCompanyResource($data), 200);
        } catch (\Exception $ex) {
            Log::error('Error occurred while creating Alliance Company: ' . $ex->getMessage(), [
                'exception' => $ex
            ]);
            return response()->json(['message' => 'Error occurred while creating Alliance Company', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $data = $this->dataService->show($id);

            if ($data === null) {
                return response()->json(['message' => 'Alliance Company not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new AllianceCompanyResource($data), 200);
        } catch (\Exception $ex) {
            Log::error('Error occurred while retrieving Alliance Company: ' . $ex->getMessage(), [
                'exception' => $ex
            ]);
            return response()->json(['message' => 'Error occurred while retrieving Alliance Company', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AllianceCompanyRequest $request, string $id): JsonResponse
    {
        try {
            $details = $request->validated();
            $data = $this->dataService->update($details, $id);

            return ApiResponseClass::sendSimpleResponse(new AllianceCompanyResource($data), 200);
        } catch (\Exception $ex) {
            Log::error('Error occurred while updating Alliance Company: ' . $ex->getMessage(), [
                'exception' => $ex
            ]);
            return response()->json(['message' => 'Error occurred while updating Alliance Company', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $data = $this->dataService->delete($id);

            if ($data === null) {
                return response()->json(['message' => 'Alliance Company not found'], 404);
            }

            return response()->json(['message' => 'Alliance Company deleted successfully'], 200);
        } catch (\Exception $ex) {
            Log::error('Error occurred while deleting Alliance Company: ' . $ex->getMessage(), [
                'exception' => $ex
            ]);
            return response()->json(['message' => 'Error occurred while deleting Alliance Company', 'error' => $ex->getMessage()], 500);
        }
    }
}