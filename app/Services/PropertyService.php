<?php // app/Services/CustomerPropertyService.php
namespace App\Services;

use App\Interfaces\PropertyRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Models\CustomerProperty;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\QueryException;

class PropertyService
{
    protected $baseController;
    protected $propertyRepository;
    protected $cacheKey;
    protected $cacheTime = 720;
    protected $userId;
    
    public function __construct(PropertyRepositoryInterface $propertyRepository, BaseController $baseController)
    {
        $this->propertyRepository = $propertyRepository;
        $this->baseController = $baseController;
    }

    public function all()
    {
        try {
            $this->userId = Auth::id();
            $this->cacheKey = 'customer_properties_' . $this->userId . '_total_list';

            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->propertyRepository->index();
            });

            $data = Cache::get($this->cacheKey);

            if ($data === null || !is_iterable($data)) {
                Log::warning('Data fetched from cache is null or not iterable');
                return null;
            }

            return $data;

        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching customer properties: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching customer properties: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    public function updateCustomerProperty(array $details, $uuid, array $customerIds)
{
    try {
        $customerProperty = $this->propertyRepository->update($details, $uuid, $customerIds);

        $this->updateCustomerPropertiesCache();

        return $customerProperty;
    } catch (\Exception $ex) {
        throw new \Exception('Error occurred while updating customer property: ' . $ex->getMessage());
    }
}

    private function updateCustomerPropertiesCache()
    {
        $this->userId = Auth::id();
        $this->cacheKey = 'customer_properties_' . $this->userId . '_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return CustomerProperty::orderBy('id', 'DESC')->get();
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }

    public function storeCustomerProperty(array $details, array $customerIds)
{
    try {
        $details['uuid'] = Uuid::uuid4()->toString();
        $details['user_id'] = Auth::id();

        $customerProperty = $this->propertyRepository->store($details, $customerIds);

        $this->updateCustomerPropertiesCache();

        return $customerProperty;
    } catch (\Exception $ex) {
        throw new \Exception('Error occurred while creating customer property: ' . $ex->getMessage());
    }
}

    public function showCustomerProperty(string $uuid)
    {
        try {
            $cacheKey = 'customer_property_' . $uuid;

            $customerProperty = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
                return $this->propertyRepository->getByUuid($uuid);
            });

            return $customerProperty;

        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while retrieving customer property: ' . $ex->getMessage());
        }
    }

    public function deleteCustomerProperty(string $uuid)
    {
        try {
            $customerProperty = $this->propertyRepository->delete($uuid);

            $this->baseController->invalidateCache('customer_property_' . $uuid);

            $this->updateCustomerPropertiesCache();

            return $customerProperty;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while deleting customer property: ' . $ex->getMessage());
        }
    }

    public function restoreCustomerProperty(string $uuid)
    {
        try {
            $customerProperty = $this->propertyRepository->restore($uuid);

            $this->updateCustomerPropertiesCache();

            return $customerProperty;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while restoring customer property: ' . $ex->getMessage());
        }
    }
}