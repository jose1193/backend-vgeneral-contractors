<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use App\Models\CategoryProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;

use App\Interfaces\CategoryProductRepositoryInterface;
use App\Http\Requests\CategoryProductRequest;
use App\Classes\ApiResponseClass;
use App\Http\Resources\CategoryProductResource;

use App\Services\CategoryProductService;

class CategoryProductController extends BaseController
{
    protected $cacheTime = 720;
    protected $categoryProductService;

    public function __construct(CategoryProductService $categoryProductService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only(['index', 'store', 'show', 'update', 'destroy']);
        
        $this->categoryProductService = $categoryProductService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $categories = $this->categoryProductService->all();

            if ($categories === null) {
                return response()->json(['message' => 'No products category found or invalid data structure'], 404);
            }

            return ApiResponseClass::sendResponse(CategoryProductResource::collection($categories), 200);

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching products category: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching products category'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryProductRequest $request): JsonResponse
    {
        try {
            $details = $request->validated();
           
            $category = $this->categoryProductService->storeCategory($details);
            
            return ApiResponseClass::sendSimpleResponse(new CategoryProductResource($category), 201);
        } catch (\Exception $e) {
            Log::error('Error occurred while creating category product: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while creating category product'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $category = $this->categoryProductService->showCategory($id);

            return ApiResponseClass::sendSimpleResponse(new CategoryProductResource($category), 200);

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching category product: ' . $e->getMessage());
            return response()->json(['message' => 'Error occurred while fetching category product'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryProductRequest $request, string $id): JsonResponse
    {
        try {
            $updateDetails = $request->validated();

            $category = $this->categoryProductService->updateCategory($updateDetails, $id);

            return ApiResponseClass::sendSimpleResponse(new CategoryProductResource($category), 200);

        } catch (\Exception $e) {
            Log::error('Error occurred while updating category product: ' . $e->getMessage());
            return response()->json(['message' => 'Error occurred while updating category product'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->categoryProductService->deleteCategory($id);

            return ApiResponseClass::sendResponse('Product Category Delete Successful', '', 200);

        } catch (\Exception $e) {
            Log::error('Error occurred while deleting Product Category: ' . $e->getMessage());
            return response()->json(['message' => 'Error occurred while deleting Product Category'], 500);
        }
    }
}
