<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use App\Models\FileEsx;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;

use App\Interfaces\FileEsxRepositoryInterface;
use App\Http\Requests\FileEsxRequest;
use App\Classes\ApiResponseClass;
use App\Http\Resources\FileEsxResource;


use App\Services\FileEsxService;

class FileEsxController extends BaseController
{
    protected $fileEsxService;

    public function __construct(FileEsxService $fileEsxService)
    {
        $this->middleware('check.permission:Super Admin')->only(['store', 'show', 'update', 'destroy']);
        $this->fileEsxService = $fileEsxService;
    }

    /**
     * Display a listing of the resource.
     */
    

    public function index(): JsonResponse
    {
        try {
            $files = $this->fileEsxService->all();
            return ApiResponseClass::sendResponse(FileEsxResource::collection($files), 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching files: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Error occurred while fetching files'], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
      public function store(FileEsxRequest $request): JsonResponse
    {
        try {
            $file = $this->fileEsxService->storeFile($request->validated());
            return ApiResponseClass::sendSimpleResponse(new FileEsxResource($file), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while creating file', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $file = $this->fileEsxService->showFile($uuid);
            return ApiResponseClass::sendSimpleResponse(new FileEsxResource($file), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while retrieving file', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FileEsxRequest $request, string $uuid): JsonResponse
    {
        try {
            $file = $this->fileEsxService->updateFile($request->validated(), $uuid);
            return ApiResponseClass::sendSimpleResponse(new FileEsxResource($file), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while updating file', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->fileEsxService->deleteFile($uuid);
            return response()->json(['message' => 'File deleted successfully'], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while deleting file', 'error' => $ex->getMessage()], 500);
        }
    }
}
