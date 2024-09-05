<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ServiceRequestRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Services\ServiceRequestService;
use App\Classes\ApiResponseClass;

class ServiceRequestController extends BaseController
{
    protected $service;

    public function __construct(ServiceRequestService $service)
    {
        // Middleware para permisos, ajusta según sea necesario
        $this->middleware('check.permission:Super Admin')->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $serviceRequests = $this->service->all();
        
        return ApiResponseClass::sendResponse(ServiceRequestResource::collection($serviceRequests), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequestRequest $request): JsonResponse
    {
        $serviceRequest = $this->service->storeData($request->validated());
        
        return ApiResponseClass::sendSimpleResponse(new ServiceRequestResource($serviceRequest), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid): JsonResponse
    {
        $serviceRequest = $this->service->showData($uuid);
        
        return ApiResponseClass::sendSimpleResponse(new ServiceRequestResource($serviceRequest), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceRequestRequest $request, string $uuid): JsonResponse
    {
        $serviceRequest = $this->service->updateData($request->validated(), $uuid);
        
        return ApiResponseClass::sendSimpleResponse(new ServiceRequestResource($serviceRequest), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->service->deleteData($uuid);
        
        return ApiResponseClass::sendResponse('Service request deleted successfully', '', 200);
    }
}