<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;

use App\Classes\ApiResponseClass;
use App\Http\Requests\PublicCompanyRequest;
use App\Http\Resources\PublicCompanyResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\PublicCompanyService;

class PublicCompanyController extends BaseController
{
    protected $cacheTime = 720;
    protected $dataService;
    
    public function __construct(PublicCompanyService $dataService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        
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
                return response()->json(['message' => 'No public companies found or invalid data structure'], 404);
            }

            return ApiResponseClass::sendResponse(PublicCompanyResource::collection($data), 200);
        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching Public Companies: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Database error occurred while fetching Public Companies'], 500);
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching Public Companies: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching Public Companies'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PublicCompanyRequest $request): JsonResponse
    {
        try {
            $details = $request->validated();
            $data = $this->dataService->storeData($details);

            return ApiResponseClass::sendSimpleResponse(new PublicCompanyResource($data), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while creating Public Company', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PublicCompanyRequest $request, string $uuid): JsonResponse
    {
        try {
            // Validar y obtener los detalles de la solicitud
            $details = $request->validated();
            $data = $this->dataService->updateData($details, $uuid);

            return ApiResponseClass::sendSimpleResponse(new PublicCompanyResource($data), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while updating Public Company', 'error' => $ex->getMessage()], 500);
        }
    }

     /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $data = $this->dataService->showData($uuid);

            if ($data === null) {
                return response()->json(['message' => 'Public Company not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new PublicCompanyResource($data), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while retrieving Public Company', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $data = $this->dataService->deleteData($uuid);

            if ($data === null) {
                return response()->json(['message' => 'Public Company not found'], 404);
            }

            return response()->json(['message' => 'Public Company deleted successfully'], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while deleting Public Company', 'error' => $ex->getMessage()], 500);
        }
    }
}

