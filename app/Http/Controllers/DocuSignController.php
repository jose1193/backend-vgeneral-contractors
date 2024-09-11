<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Classes\ApiResponseClass;
use App\Services\DocuSignService; 
use App\Traits\HandlesApiErrors; 
use App\Http\Requests\DocuSignRequest; 
use App\Http\Resources\DocuSignResource; 
class DocuSignController extends BaseController
{
    use HandlesApiErrors;
    protected $docuSignService;

    public function __construct(DocuSignService $docuSignService)
    {
        $this->middleware('check.permission:Super Admin')->only(['connectDocusign','validateDocument','allDocuments','checkDocumentStatus','index','show', 'store', 'update']);
        $this->docuSignService = $docuSignService;
    }

    /**
     * Connect DocuSign.
     */
    // public function connectDocusign(string $uuid)
    // {
    // Puedes agregar lógica adicional aquí si es necesario,
    // por ejemplo, validaciones o transformación de datos.

    // Llamamos al método del repositorio para obtener la URL de autenticación y el claim UUID
    // return $this->serviceData->connectDocusign($uuid);
    // }

    public function connectDocusign(): JsonResponse
    {
        try {
            // Llama al servicio de DocuSign
            $response = $this->docuSignService->connectDocusign();

            // Devuelve la respuesta formateada
            return ApiResponseClass::sendResponse(new DocuSignResource($response), 200);
        } catch (Exception $e) {
            // Devuelve una respuesta con el error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function callbackDocusign(DocuSignRequest $request): JsonResponse
{
    try {
        $validated = $request->validated();
        $code = $validated['code'];

        // Pasar los parámetros individuales a `callbackData`
        $callback = $this->docuSignService->callbackData($code);

        // Enviar la respuesta
        return ApiResponseClass::sendSimpleResponse(new DocuSignResource($callback), 200);
    } catch (\Exception $e) {
        return $this->handleError($e, 'Error Callback DocuSign');
    }
}


    /**
     * Store a newly created DocuSign resource in storage.
     */
    
    public function validateDocument(DocuSignRequest $request): JsonResponse
    {
    try {
        // Obtener los datos validados como un array
        $validatedData = $request->validated();
        
        $response = $this->docuSignService->validateDocument($validatedData);
        
        return ApiResponseClass::sendSimpleResponse(new DocuSignResource($response), 200);
    } catch (\Exception $e) {
        return $this->handleError($e, 'Error Validate DocuSign Document');
    }
    
    }



    /**
     * Display a listing of the DocuSign resources.
     */
    public function index(): JsonResponse
    {
        try {
            $documents = $this->docuSignService->all();
            return ApiResponseClass::sendResponse(DocuSignResource::collection($documents), 200);
            
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving DocuSign documents');
        }
    }

    public function allDocuments(): JsonResponse
    {
        try {
            $response = $this->docuSignService->getAllDocuments();
            return ApiResponseClass::sendResponse(new DocuSignResource($response), 200);
            
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error retrieving DocuSign documents');
        }
    }

    

     



    /**
     * Display the specified DocuSign resource.
     */
    public function checkDocumentStatus(DocuSignRequest $request): JsonResponse
    {
    try {
        // Obtener los datos validados
        $validated = $request->validated();

        // Llama al servicio con el envelope_id validado
        $document = $this->docuSignService->checkDocumentStatusData($validated['envelope_id']);

        // Devuelve una respuesta exitosa con los datos del documento
        return ApiResponseClass::sendSimpleResponse($document, 200);
    } catch (\Exception $e) {
        // Manejo del error y devolución de la respuesta
        return $this->handleError($e, 'Error retrieving DocuSign document');
    }
    }

    /**
     * Update the specified DocuSign resource in storage.
     */

    /**
     * Remove the specified DocuSign resource from storage.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->docuSignService->deleteData($uuid);
            return ApiResponseClass::sendResponse('DocuSign document deleted successfully', '', 200);
        } catch (\Exception $e) {
            return $this->handleError($e, 'Error deleting DocuSign document');
        }
    }


    
}
