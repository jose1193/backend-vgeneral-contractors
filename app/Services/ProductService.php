<?php // app/Services/ProductService.php
namespace App\Services;

use App\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMailCustomer;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;


class ProductService
{
    protected $baseController;
    protected $productRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;
    protected $userId;

    public function __construct(ProductRepositoryInterface $productRepositoryInterface, BaseController $baseController)
    {
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->baseController = $baseController;
    }

    public function all()
    {
        try {
            $this->userId = Auth::id();
            $this->cacheKey = 'products_' . $this->userId . '_total_list';

            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->productRepositoryInterface->index();
            });

            $data = Cache::get($this->cacheKey);

            if ($data === null || !is_iterable($data)) {
                Log::warning('Data fetched from cache is null or not iterable');
                return null;
            }

            return $data;

        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching products: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;

        } catch (Exception $e) {
            Log::error('Error occurred while fetching products: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    public function updateProduct(array $updateDetails, string $uuid)
    {
        DB::beginTransaction();

        try {
            $product = $this->productRepositoryInterface->update($updateDetails, $uuid);

            $this->updateProductsCache();
            DB::commit();

            return $product;
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception('Error occurred while updating product: ' . $ex->getMessage());
        }
    }

    private function updateProductsCache()
    {
        $this->userId = Auth::id();
        $this->cacheKey = 'products_' . $this->userId . '_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return Product::orderBy('id', 'DESC')->get();
            });
        } else {
            throw new Exception('Invalid cacheKey provided');
        }
    }

    public function storeProduct(array $details)
    {
        DB::beginTransaction();

        try {
            $details['uuid'] = Uuid::uuid4()->toString();
            $details['user_id'] = Auth::id();

            $product = $this->productRepositoryInterface->store($details);

            $this->updateProductsCache();

            DB::commit();

            return $product;
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception('Error occurred while creating product: ' . $ex->getMessage());
        }
    }

    public function showProduct(string $uuid)
    {
        try {
            $cacheKey = 'product_' . $uuid;

            $product = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
                return $this->productRepositoryInterface->getByUuid($uuid);
            });

            return $product;

        } catch (Exception $ex) {
            throw new Exception('Error occurred while retrieving product: ' . $ex->getMessage());
        }
    }

    public function deleteProduct(string $uuid)
    {
        try {
            $product = $this->productRepositoryInterface->delete($uuid);

            $this->baseController->invalidateCache('product_' . $uuid);

            $this->updateProductsCache();

            return $product;
        } catch (Exception $ex) {
            throw new Exception('Error occurred while deleting product: ' . $ex->getMessage());
        }
    }

    public function restoreProduct(string $uuid)
    {
        try {
            $product = $this->productRepositoryInterface->restore($uuid);

            $this->baseController->invalidateCache('product_' . $uuid);

            $this->updateProductsCache();

            return $product;
        } catch (Exception $ex) {
            throw new Exception('Error occurred while restoring product: ' . $ex->getMessage());
        }
    }
}