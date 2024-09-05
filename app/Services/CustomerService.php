<?php // app/Services/Customer.php
namespace App\Services;

use App\Interfaces\CustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMailCustomer;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;


class CustomerService
{
    protected $baseController;
    protected $customerRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;
    protected $userId;
    
    public function __construct(CustomerRepositoryInterface $customerRepositoryInterface, BaseController $baseController)
    {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->baseController = $baseController;
    }

    public function all()
    {
        try {
            $this->userId = Auth::id();
            $this->cacheKey = 'customers_' . $this->userId . '_total_list';

            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->customerRepositoryInterface->index();
            });

            $data = Cache::get($this->cacheKey);

            if ($data === null || !is_iterable($data)) {
                Log::warning('Data fetched from cache is null or not iterable');
                return null;
            }

            return $data;

        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching customers: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching customers: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    public function updateCustomer(array $updateDetails, string $uuid)
    {
        DB::beginTransaction();

        try {
            $customer = $this->customerRepositoryInterface->getByUuid($uuid);
            

            $customer = $this->customerRepositoryInterface->update($updateDetails, $uuid);

           
            $this->updateCustomersCache();

            DB::commit();

            return $customer;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while updating customer: ' . $ex->getMessage());
        }
    }

    

    private function updateCustomersCache()
    {
        $this->userId = Auth::id();
        $this->cacheKey = 'customers_' . $this->userId . '_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return Customer::withTrashed()->orderBy('id', 'DESC')->get();
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }

    public function storeCustomer(array $details)
    {
        DB::beginTransaction();

        try {
            $details['uuid'] = Uuid::uuid4()->toString();
            $details['user_id'] = Auth::id();

            $customer = $this->customerRepositoryInterface->store($details);

            $this->updateCustomersCache();

            DB::commit();

            return $customer;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while creating customer: ' . $ex->getMessage());
        }
    }

    public function showCustomer(string $uuid)
    {
        try {
            $cacheKey = 'customer_' . $uuid;

            $customer = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
                return $this->customerRepositoryInterface->getByUuid($uuid);
            });

            return $customer;

        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while retrieving customer: ' . $ex->getMessage());
        }
    }

    public function deleteCustomer(string $uuid)
    {
        try {
            $customer = $this->customerRepositoryInterface->delete($uuid);

            $this->baseController->invalidateCache('customer_' . $uuid);

            $this->updateCustomersCache();

            return $customer;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while deleting customer: ' . $ex->getMessage());
        }
    }

    public function restoreCustomer(string $uuid)
    {
        try {
            $customer = $this->customerRepositoryInterface->restore($uuid);

            $this->baseController->invalidateCache('customer_' . $uuid);

            $this->updateCustomersCache();

            return $customer;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while restoring customer: ' . $ex->getMessage());
        }
    }
}