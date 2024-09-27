<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Http\Requests\CompanySignatureRequest;
use App\Http\Resources\CompanySignatureResource;
use App\Services\CompanySignatureService;
use App\Traits\HandlesApiErrors;
use App\DTOs\CompanySignatureDTO;
use Ramsey\Uuid\Uuid;

class CompanySignatureController extends BaseController
{
    use HandlesApiErrors;
    protected $dataService;

    public function __construct(CompanySignatureService $dataService)
    {
        $this->middleware('check.permission:Super Admin')->only(['index', 'store', 'update', 'destroy']);
        $this->dataService = $dataService;
    }

    /**
     * Display a listing of the resource.
     */
        public function index(): JsonResponse
    {
        try {
        // Llama al servicio para obtener todas las firmas
        $companySignatures = $this->dataService->all();

        // Retorna la respuesta con los datos de las firmas
        return ApiResponseClass::sendResponse(CompanySignatureResource::collection($companySignatures), 200);
        } catch (\Exception $e) {
        // Maneja el error y retorna una respuesta apropiada
        return $this->handleError($e, 'Error retrieving company signatures');
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanySignatureRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            $dto = CompanySignatureDTO::fromArray($validatedData);

            $company_signature = $this->dataService->storeData($dto);
            return ApiResponseClass::sendSimpleResponse(new CompanySignatureResource($company_signature), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error storing company signature');
        }
    }

    /**
     * Display the specified resource.
     */
        public function show(string $uuid): JsonResponse
    {
        try {
        // Convierte el string en un objeto Uuid
        $uuidObject = Uuid::fromString($uuid);

        // Pasa el objeto Uuid en lugar del string
        $company_signature = $this->dataService->showData($uuidObject);

        return ApiResponseClass::sendSimpleResponse(new CompanySignatureResource($company_signature), 200);
        } catch (\Ramsey\Uuid\Exception\InvalidUuidStringException $e) {
        // Maneja el caso en que el UUID no sea válido
        return $this->handleError($e, 'Invalid UUID format');
        } catch (\Exception $e) {
        return $this->handleError($e, 'Error retrieving company signature');
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(CompanySignatureRequest $request, string $uuid): JsonResponse
    {
        try {
             
            $uuidObject = Uuid::fromString($uuid);

            $company_signature = $this->dataService->updateData(CompanySignatureDTO::fromArray($request->validated()), $uuidObject);
            return ApiResponseClass::sendSimpleResponse(new CompanySignatureResource($company_signature), 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error updating company signature');
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
            return ApiResponseClass::sendResponse('Company signature deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting company signature');
        }
    }
}