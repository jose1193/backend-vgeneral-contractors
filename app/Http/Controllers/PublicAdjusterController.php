<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use App\Models\PublicAdjuster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;

use App\Interfaces\PublicAdjusterRepositoryInterface;
use App\Http\Requests\PublicAdjusterRequest;
use App\Classes\ApiResponseClass;
use App\Http\Resources\PublicAdjusterResource;

use App\Services\PublicAdjusterService;


class PublicAdjusterController extends BaseController
{
    protected $publicAdjusterService;

    public function __construct(PublicAdjusterService $publicAdjusterService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only([ 'store', 'show', 'update', 'destroy']);

        $this->publicAdjusterService = $publicAdjusterService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            // Obtener todos los ajustadores públicos usando el servicio
            $adjusters = $this->publicAdjusterService->all();

            if ($adjusters === null) {
                return response()->json(['message' => 'No public adjusters found or invalid data structure'], 404);
            }

            return ApiResponseClass::sendResponse(PublicAdjusterResource::collection($adjusters), 200);

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching public adjusters: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching public adjusters', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PublicAdjusterRequest $request): JsonResponse
    {
        try {
            // Validar y obtener los detalles de la solicitud
            $details = $request->validated();

            // Utilizar el servicio para almacenar el ajustador
            $adjuster = $this->publicAdjusterService->storePublicAdjuster($details);

            return ApiResponseClass::sendSimpleResponse(new PublicAdjusterResource($adjuster), 200);
        } catch (\Exception $ex) {
            Log::error('Error occurred while creating public adjuster: ' . $ex->getMessage(), [
                'exception' => $ex
            ]);
            return response()->json(['message' => 'Error occurred while creating public adjuster', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            // Utilizar el servicio para obtener el ajustador
            $adjuster = $this->publicAdjusterService->showPublicAdjuster($id);

            if ($adjuster === null) {
                return response()->json(['message' => 'Public adjuster not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new PublicAdjusterResource($adjuster), 200);

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching public adjuster: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching public adjuster', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PublicAdjusterRequest $request, string $id): JsonResponse
    {
        $updateDetails = $request->validated();

        try {
            // Utilizar el servicio para actualizar el ajustador
            $adjuster = $this->publicAdjusterService->updatePublicAdjuster($updateDetails, $id);

            if ($adjuster === null) {
                return response()->json(['message' => 'Public adjuster not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new PublicAdjusterResource($adjuster), 200);

        } catch (\Exception $ex) {
            Log::error('Error occurred while updating public adjuster: ' . $ex->getMessage(), [
                'exception' => $ex
            ]);
            return response()->json(['message' => 'Error occurred while updating public adjuster', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            // Utilizar el servicio para eliminar el ajustador
            $this->publicAdjusterService->deletePublicAdjuster($id);

            return ApiResponseClass::sendResponse('Public adjuster deleted successfully', '', 200);

        } catch (\Exception $e) {
            Log::error('Error occurred while deleting public adjuster: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while deleting public adjuster', 'error' => $e->getMessage()], 500);
        }
    }
}