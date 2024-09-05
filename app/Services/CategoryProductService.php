<?php // app/Services/CategoryProductService.php
namespace App\Services;

use App\Interfaces\CategoryProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Models\CustomerProperty;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\QueryException;


class CategoryProductService
{
    protected $baseController;
    protected $categoryProductRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;
    protected $userId;
    
    public function __construct(CategoryProductRepositoryInterface $categoryProductRepositoryInterface, BaseController $baseController)
    {
        $this->categoryProductRepositoryInterface = $categoryProductRepositoryInterface;
        $this->baseController = $baseController;
    }

    public function all()
    {
        try {
            $this->userId = Auth::id();
            $this->cacheKey = 'category_products_' . $this->userId . '_total_list';

            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->categoryProductRepositoryInterface->index();
            });

            $data = Cache::get($this->cacheKey);

            if ($data === null || !is_iterable($data)) {
                Log::warning('Data fetched from cache is null or not iterable');
                return null;
            }

            return $data;

        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching Product Categories: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching product categories: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    public function updateCategory(array $updateDetails, string $id)
    {
        DB::beginTransaction();

        try {
            $categoryProduct = $this->categoryProductRepositoryInterface->getByUuid($id);

            if (!$categoryProduct) {
                throw new \Exception('Product Category not found');
            }

            $categoryProduct = $this->categoryProductRepositoryInterface->update($updateDetails, $id);

            $this->updateCategoryProductsCache();

            DB::commit();

            return $categoryProduct;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while updating Product Category: ' . $ex->getMessage());
        }
    }

    private function updateCategoryProductsCache()
    {
        $this->userId = Auth::id();
        $this->cacheKey = 'category_products_' . $this->userId . '_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->categoryProductRepositoryInterface->index();
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }

    public function storeCategory(array $details)
    {
        DB::beginTransaction();

        try {
            $details['uuid'] = Uuid::uuid4()->toString();
            $details['user_id'] = Auth::id();

            $categoryProduct = $this->categoryProductRepositoryInterface->store($details);

            $this->updateCategoryProductsCache();

            DB::commit();

            return $categoryProduct;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while creating Product Category: ' . $ex->getMessage());
        }
    }

    public function showCategory(string $id)
    {
        try {
            $cacheKey = 'category_product_' . $id;

            $categoryProduct = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($id) {
                return $this->categoryProductRepositoryInterface->getById($id);
            });

            return $categoryProduct;

        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while retrieving Product Category: ' . $ex->getMessage());
        }
    }

    public function deleteCategory(string $id)
    {
        try {
            $categoryProduct = $this->categoryProductRepositoryInterface->delete($id);

            $this->baseController->invalidateCache('category_product_' . $id);

            $this->updateCategoryProductsCache();

            return $categoryProduct;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while deleting Product Category: ' . $ex->getMessage());
        }
    }

    public function restoreCategory(string $id)
    {
        try {
            $categoryProduct = $this->categoryProductRepositoryInterface->restore($id);

            $this->updateCategoryProductsCache();

            return $categoryProduct;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while restoring Product Category: ' . $ex->getMessage());
        }
    }
}
