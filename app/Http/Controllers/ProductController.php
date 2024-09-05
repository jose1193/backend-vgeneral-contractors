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

use App\Interfaces\ProductRepositoryInterface;
use App\Http\Requests\ProductRequest;
use App\Classes\ApiResponseClass;
use App\Http\Resources\ProductResource;


use App\Services\ProductService;

class ProductController extends BaseController
{
    protected $cacheTime = 720;
    protected $productService;

    public function __construct(ProductService $productService)
    {
        // Middleware para permisos
        $this->middleware('check.permission:Super Admin')->only(['index', 'store', 'show', 'update', 'destroy']);
        
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $products = $this->productService->all();

            if ($products === null) {
                return response()->json(['message' => 'No products found or invalid data structure'], 404);
            }

            return ApiResponseClass::sendResponse(ProductResource::collection($products), 200);
        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching Products: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Database error occurred while fetching Products'], 500);
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching Products: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'Error occurred while fetching Products'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request): JsonResponse
    {
        try {
            $details = $request->validated();
            
            $product = $this->productService->storeProduct($details);

            return ApiResponseClass::sendSimpleResponse(new ProductResource($product), 201);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while creating Product', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $product = $this->productService->showProduct($uuid);

            if ($product === null) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            return ApiResponseClass::sendSimpleResponse(new ProductResource($product), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while retrieving Product', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, string $uuid): JsonResponse
    {
        try {
            $details = $request->validated();

            $product = $this->productService->updateProduct($details, $uuid);

            return ApiResponseClass::sendSimpleResponse(new ProductResource($product), 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while updating Product', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $product = $this->productService->deleteProduct($uuid);

            if ($product === null) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            return response()->json(['message' => 'Product deleted successfully'], 200);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Error occurred while deleting Product', 'error' => $ex->getMessage()], 500);
        }
    }

    // RESTORE PRODUCT
    public function restore($uuid)
    {
        try {
            
            $product = $this->productService->restoreProduct($uuid);

            return ApiResponseClass::sendSimpleResponse(new ProductResource($product), 200);

        } catch (\Exception $e) {
            // Registrar el mensaje de la excepción en el log
            Log::error('Error occurred while restoring product: ' . $e->getMessage());

            // Manejar cualquier excepción y devolver una respuesta de error
            return response()->json(['message' => 'Error occurred while restoring product', 'error' => $e->getMessage()], 500);
        }
    }
}
