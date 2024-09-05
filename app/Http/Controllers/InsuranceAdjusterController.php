<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use App\Models\InsuranceAdjuster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;

use App\Interfaces\InsuranceAdjusterRepositoryInterface;
use App\Http\Requests\InsuranceAdjusterRequest;
use App\Classes\ApiResponseClass;
use App\Http\Resources\InsuranceAdjusterResource;

use App\Services\InsuranceAdjusterService;

class InsuranceAdjusterController extends BaseController
{
    protected $insuranceAdjusterService;

    public function __construct(InsuranceAdjusterService $insuranceAdjusterService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only(['index', 'store', 'show', 'update', 'destroy']);

        $this->insuranceAdjusterService = $insuranceAdjusterService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            // Obtener todos los ajustadores usando el servicio
            $adjusters = $this->insuranceAdjusterService->all();

            if ($adjusters === null) {
                return response()->json(['message' => 'No insurance adjusters found or invalid data structure'], 404);
            }

            return ApiResponseClass::sendResponse(InsuranceAdjusterResource::collection($adjusters), 200);

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching insurance adjusters: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching insurance adjusters', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InsuranceAdjusterRequest $request): JsonResponse
    {
        try {
            // Validar y obtener los detalles de la solicitud
            $details = $request->validated();
            
            // Utilizar el servicio para almacenar el ajustador
            $adjuster = $this->insuranceAdjusterService->storeInsuranceAdjuster($details);

            return ApiResponseClass::sendSimpleResponse(new InsuranceAdjusterResource($adjuster), 200);
        } catch (\Exception $ex) {
            Log::error('Error occurred while creating insurance adjuster: ' . $ex->getMessage(), [
                'exception' => $ex
            ]);
            return response()->json(['message' => 'Error occurred while creating insurance adjuster', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            // Utilizar el servicio para obtener el ajustador
            $adjuster = $this->insuranceAdjusterService->showInsuranceAdjuster($uuid);

            if ($adjuster === null) {
                return response()->json(['message' => 'Insurance adjuster not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new InsuranceAdjusterResource($adjuster), 200);

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching insurance adjuster: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching insurance adjuster', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InsuranceAdjusterRequest $request, string $uuid): JsonResponse
    {
        $updateDetails = $request->validated();

        try {
            // Utilizar el servicio para actualizar el ajustador
            $adjuster = $this->insuranceAdjusterService->updateInsuranceAdjuster($updateDetails, $uuid);

            if ($adjuster === null) {
                return response()->json(['message' => 'Insurance adjuster not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new InsuranceAdjusterResource($adjuster), 200);

        } catch (\Exception $ex) {
            Log::error('Error occurred while updating insurance adjuster: ' . $ex->getMessage(), [
                'exception' => $ex
            ]);
            return response()->json(['message' => 'Error occurred while updating insurance adjuster', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            // Utilizar el servicio para eliminar el ajustador
            $this->insuranceAdjusterService->deleteInsuranceAdjuster($uuid);

            return ApiResponseClass::sendResponse('Insurance adjuster deleted successfully', '', 200);

        } catch (\Exception $e) {
            Log::error('Error occurred while deleting insurance adjuster: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while deleting insurance adjuster', 'error' => $e->getMessage()], 500);
        }
    }
}
