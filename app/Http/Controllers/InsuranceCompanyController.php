<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\InsuranceCompanyRequest;
use App\Http\Resources\InsuranceCompanyResource;
use App\Services\InsuranceCompanyService;
use App\Traits\HandlesApiErrors;
use App\DTOs\InsuranceCompanyDTO;
use Ramsey\Uuid\Uuid;

class InsuranceCompanyController extends BaseController
{
    use HandlesApiErrors;

    protected $dataService;

    public function __construct(InsuranceCompanyService $dataService)
    {
        $this->middleware('check.permission:Salesperson')->only(['index', 'show','store','update']);
        $this->dataService = $dataService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $insuranceCompanies = $this->dataService->all();
            return ApiResponseClass::sendResponse(InsuranceCompanyResource::collection($insuranceCompanies), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving insurance companies');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InsuranceCompanyRequest $request): JsonResponse
    {
        try {
        $validatedData = $request->validated();
        $dto = InsuranceCompanyDTO::fromArray($validatedData);
        
       
        $prohibitedAlliances = $request->input('prohibited_alliances', []);
        
        $insuranceCompany = $this->dataService->storeData($dto, $prohibitedAlliances);
        return ApiResponseClass::sendSimpleResponse(new InsuranceCompanyResource($insuranceCompany), 200);
        } catch (\Exception $e) {
        return $this->handleError($e, 'Error storing insurance company');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $insuranceCompany = $this->dataService->showData($uuidObject);
            return ApiResponseClass::sendSimpleResponse(new InsuranceCompanyResource($insuranceCompany), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
            return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving insurance company');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InsuranceCompanyRequest $request, string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $dto = InsuranceCompanyDTO::fromArray($request->validated());
        
       
        $prohibitedAlliances = $request->input('prohibited_alliances', []); 
        
        $insuranceCompany = $this->dataService->updateData($dto, $uuidObject, $prohibitedAlliances);
        return ApiResponseClass::sendSimpleResponse(new InsuranceCompanyResource($insuranceCompany), 200);
        } catch (\Exception $e) {
        return $this->handleError($e, 'Error updating insurance company');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
            $this->dataService->deleteData($uuidObject);
            return ApiResponseClass::sendResponse('Insurance company deleted successfully','', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting insurance company');
        }
    }
}