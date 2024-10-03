<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use App\Traits\HandlesApiErrors;
use App\DTOs\CustomerDTO;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    use HandlesApiErrors;

    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->middleware('check.permission:Salesperson')->only(['index', 'store', 'update']);
        $this->customerService = $customerService;
    }
    

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $customers = $this->customerService->allCustomers();
            return ApiResponseClass::sendResponse(CustomerResource::collection($customers), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving customers');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $dto = CustomerDTO::fromArray($validatedData);
            $customer = $this->customerService->storeCustomer($dto);
            return ApiResponseClass::sendSimpleResponse(new CustomerResource($customer), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing customer');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $customer = $this->customerService->showCustomer($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new CustomerResource($customer), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving customer');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = CustomerDTO::fromArray($request->validated());
            $customer = $this->customerService->updateCustomer($dto, $uuidObject);
            return ApiResponseClass::sendSimpleResponse(new CustomerResource($customer), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating customer');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $this->customerService->deleteCustomer($uuidObject);
            return ApiResponseClass::sendResponse('Customer deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting customer');
        }
    }

    /**
     * Restore the specified resource.
     */
    public function restore(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $customer = $this->customerService->restoreCustomer($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new CustomerResource($customer), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error restoring customer');
        }
    }

      public function checkEmailAvailability(Request $request, string $email): JsonResponse
    {
        try {
            $uuid = $request->query('uuid');
            $result = $this->customerService->checkEmailAvailability($email, $uuid);
            return ApiResponseClass::sendResponse($result, 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error checking email availability');
        }
    }

}
