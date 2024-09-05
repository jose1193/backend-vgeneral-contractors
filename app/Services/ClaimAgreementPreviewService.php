<?php

namespace App\Services;

use App\Interfaces\ClaimAgreementPreviewRepositoryInterface;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Exception;
use Illuminate\Database\QueryException;
use PDF;
use App\Helpers\ImageHelper;

use PhpOffice\PhpWord\TemplateProcessor;
use Dompdf\Dompdf;
use Dompdf\Options;
use TCPDF;


class ClaimAgreementPreviewService
{
    protected $serviceData;
    protected $baseController;
    protected $cacheTime = 720; 

    public function __construct(
        ClaimAgreementPreviewRepositoryInterface $serviceData,
        BaseController $baseController
    ) {
        $this->serviceData = $serviceData;
        $this->baseController = $baseController;
    }

    private function getUserId()
    {
        return Auth::id();
    }

    public function all()
    {
    $userId = $this->getUserId();
    $cacheKey = 'claim_agreement_previews_total_list_' . $userId;

    return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($userId) {
        return $this->serviceData->getClaimAgreementByUser(Auth::user());
    });
    }

       // FUNCTION STORE DATA

        public function storeData(array $details)
    {
        return $this->handleTransaction(function () use ($details) {
        $filePaths = [];
        try {
            $existingClaim = $this->serviceData->getClaimByUuid($details['claim_uuid']);
            $filePaths = $this->generateDocumentAndStore($existingClaim);

            $preview = $this->storePreviewInDatabase($existingClaim, $filePaths['s3']);
            $this->updateDataCache();

            return $preview;
        } catch (ClaimNotFoundException $e) {
            $this->handleException($e, 'Claim not found');
        } catch (DocumentProcessingException $e) {
            $this->handleException($e, 'Error processing document');
        } catch (\Exception $e) {
            $this->handleException($e, 'Unexpected error');
        } finally {
            $this->cleanUpTempFiles($filePaths['local'] ?? null, $filePaths['processed'] ?? null);
        }
        });
    }


    private function generateDocumentAndStore($existingClaim): array
    {
        $clientNamesFile = $this->getClientNamesFile($existingClaim);
        $primaryCustomerData = $this->getPrimaryCustomerData($existingClaim);
        $fileName = $this->generateFileName($clientNamesFile);

        $localTempPath = $this->downloadTemplateFromS3();
        $processedWordPath = $this->processWordTemplate($localTempPath, $existingClaim, $clientNamesFile, $primaryCustomerData);

        $s3Path = $this->storeProcessedDocument($processedWordPath, $fileName);
        
        return ['local' => $localTempPath, 'processed' => $processedWordPath, 's3' => $s3Path];
    }

    private function getClientNamesFile($existingClaim): string
    {
        return collect($this->getClientNamesArray($existingClaim))->implode(' & ');
    }

    private function getPrimaryCustomerData($existingClaim): array
    {
        $primaryCustomerProperty = $existingClaim->property->customerProperties->first(fn($customerProperty) => $customerProperty->isOwner());

        return $primaryCustomerProperty ? [
            'cell_phone' => $primaryCustomerProperty->customer->cell_phone,
            'home_phone' => $primaryCustomerProperty->customer->home_phone,
            'email' => $primaryCustomerProperty->customer->email,
            'occupation' => $primaryCustomerProperty->customer->occupation,
        ] : [
            'cell_phone' => null,
            'home_phone' => null,
            'email' => null,
            'occupation' => null,
        ];
    }

    private function generateFileName(string $clientNamesFile): string
    {
        return 'agreement-' . str_replace(' ', '_', strtolower($clientNamesFile)) . '-' . now()->format('Y-m-d') . '.docx';
    }

    
    private function downloadTemplateFromS3(): string
    {
    // Consulta el modelo DocumentTemplate utilizando el repository y filtra por el tipo de plantilla
    $documentTemplate = $this->serviceData->getByTemplateType('Agreement');

    // Obtiene la URL del template desde el modelo
    $s3Url = $documentTemplate->template_path;

    // Define la ruta local donde se guardará temporalmente la plantilla descargada
    $localTempPath = storage_path('app/temp_template.docx');

    // Descarga el archivo desde la URL y lo guarda en la ruta temporal
    file_put_contents($localTempPath, file_get_contents($s3Url));

    // Retorna la ruta local del archivo descargado
    return $localTempPath;
    }


   private function processWordTemplate(string $localTempPath, $existingClaim, string $clientNamesFile, array $primaryCustomerData): string
    {
    $templateProcessor = new TemplateProcessor($localTempPath);

    // Preparamos los valores que se van a insertar en la plantilla
    $values = $this->prepareTemplateValues($existingClaim, $clientNamesFile, $primaryCustomerData);

    foreach ($values as $key => $value) {
        if ($key === 'signature_image') { 
            // Descargar la imagen de la firma si el campo actual es 'signature_image'
            $signatureImagePath = $this->downloadImageFromUrl($value);
            if ($signatureImagePath) {
                // Insertar la imagen en la plantilla si se ha descargado correctamente
                $templateProcessor->setImageValue('signature_image', [
                    'path' => $signatureImagePath,
                    'width' => 100,
                    'height' => 100,
                    'ratio' => true
                ]);
            } else {
                // Manejo del error si no se puede descargar la imagen
                error_log("Error: no se pudo descargar la imagen de la firma desde la URL: $value");
            }
        } else {
            // Insertar los valores normales (texto) en la plantilla
            $templateProcessor->setValue($key, $value);
        }
    }

    // Guardar el documento procesado
    $processedWordPath = storage_path('app/temp_processed.docx');
    $templateProcessor->saveAs($processedWordPath);

    return $processedWordPath;
    }

    // Función para descargar la imagen desde la URL
    private function downloadImageFromUrl(string $url): ?string
    {
    // Especificar la ruta local donde se guardará la imagen temporalmente
    $localImagePath = storage_path('app/temp_signature.png');
    
    // Descargar la imagen
    $imageContents = file_get_contents($url);

    if ($imageContents !== false) {
        // Guardar la imagen descargada en la ruta local
        file_put_contents($localImagePath, $imageContents);
        return $localImagePath; // Retorna la ruta si la descarga fue exitosa
    }

    return null; // Retorna null si la descarga falla
    }


    private function prepareTemplateValues($existingClaim, string $clientNamesFile, array $primaryCustomerData): array
    {
        return [
            'claim_id' => $existingClaim->id,
            'claim_names' => implode(', ', $this->getClientNamesArray($existingClaim)),
            'property_address' => $existingClaim->property->property_address,
            'property_state' => $existingClaim->property->property_state,
            'property_city' => $existingClaim->property->property_city,
            'postal_code' => $existingClaim->property->property_postal_code,
            'property_country' => $existingClaim->property->property_country,
            'claim_date' => $existingClaim->created_at->format('Y-m-d'),
            'insurance_company' => $existingClaim->insuranceCompanyAssignment->insuranceCompany->insurance_company_name,
            'policy_number' => $existingClaim->policy_number,
            'date_of_loss' => $existingClaim->date_of_loss,
            'claim_number' => $existingClaim->claim_number,
            'cell_phone' => $primaryCustomerData['cell_phone'],
            'home_phone' => $primaryCustomerData['home_phone'],
            'email' => $primaryCustomerData['email'],
            'signature_image' => $existingClaim->signature->signature_path,
            'signature_name' => $existingClaim->signature->user->name . ' ' . $existingClaim->signature->user->last_name,
            'company_name' => $existingClaim->signature->company_name,
            'company_name_uppercase' => strtoupper($existingClaim->signature->company_name),
            'company_address' => $existingClaim->signature->address,
            'company_email' => $existingClaim->signature->email,
            'date' => now()->format('Y-m-d'),
        ];
    }

    private function getClientNamesArray($existingClaim): array
    {
        return $existingClaim->property->customers->map(function ($customer) {
            return ucwords(strtolower($this->sanitizeClientName($customer->name . ' ' . $customer->last_name)));
        })->toArray();
    }

    private function storeProcessedDocument(string $processedWordPath, string $fileName): string
    {
        return ImageHelper::storePDFAgreement(file_get_contents($processedWordPath), 'public/claim_agreement_previews/' . $fileName);
    }

    private function storePreviewInDatabase($existingClaim, string $s3Path)
    {
        return $this->serviceData->store([
            'uuid' => Uuid::uuid4()->toString(),
            'claim_id' => $existingClaim->id,
            'preview_pdf_path' => $s3Path,
            'generated_by' => $this->getUserId(),
        ]);
    }

    private function cleanUpTempFiles(?string $localTempPath, ?string $processedWordPath): void
    {
        if ($localTempPath && file_exists($localTempPath)) {
            unlink($localTempPath);
        }
        if ($processedWordPath && file_exists($processedWordPath)) {
            unlink($processedWordPath);
        }
    }

    private function sanitizeClientName(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($name)));
    }

    // END STORE DATA

    public function updateData(array $updateDetails, string $uuid)
    {
        return $this->handleTransaction(function () use ($updateDetails, $uuid) {
            try {
                $existingPreview = $this->serviceData->getByUuid($uuid);

                $updateDetails['preview_pdf_path'] = $updateDetails['preview_pdf_path'] ?? $existingPreview->preview_pdf_path;
                $updateDetails['generated_by'] = $updateDetails['generated_by'] ?? $this->getUserId();
                
                $updatedPreview = $this->serviceData->update($updateDetails, $uuid);
                
                $this->updateDataCache();
                return $updatedPreview;

            } catch (Exception $e) {
                $this->handleException($e, 'updating claim agreement preview');
            }
        });
    }

       public function showData(string $uuid)
    {
    $cacheKey = 'claim_agreement_preview_' . $uuid;

    // Intentar obtener datos de la caché o calcular el resultado si no está en caché
    return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
        try {
            return $this->serviceData->getByUuid($uuid);
        } catch (Exception $e) {
            $this->handleException($e, 'fetching claim agreement preview');
            return null; // Si hay un error, devolvemos null
        }
    });
    }


    public function deleteData(string $uuid)
    {
        return $this->handleTransaction(function () use ($uuid) {
            try {
                $cacheKey = 'claim_agreement_preview_' . $uuid;
                $existingPreview = $this->serviceData->getByUuid($uuid);

                $this->serviceData->delete($uuid);
                $this->deleteSignatureFromS3($existingPreview->preview_pdf_path);
                $this->baseController->invalidateCache($cacheKey);
                $this->updateDataCache();
            } catch (Exception $e) {
                $this->handleException($e, 'deleting claim agreement preview');
            }
        });
    }

    private function handleTransaction(callable $callback)
    {
         DB::beginTransaction();
         try {
        $result = $callback();
        DB::commit();
        return $result;
        } catch (Exception $ex) {
        DB::rollBack();
        $this->handleException($ex, 'transaction');
        // Re-lanzar la misma excepción sin modificar el stack trace.
        throw $ex;
         }
    }

    private function handleException(Exception $e, string $context)
    {
        // Registro detallado del error
        Log::error("Error occurred while {$context}: " . $e->getMessage(), [
        'exception' => $e,
        'stack_trace' => $e->getTraceAsString(), // Agregar el stack trace completo al log
        'user_id' => Auth::id(), // Registrar el ID del usuario si está disponible
        'context' => $context // Incluir el contexto del error
        ]);
    
        // Lanza la misma excepción sin modificarla, para mantener el stack trace original
        throw $e;
    }


    private function updateDataCache()
    {
        $userId = Auth::id();
        $cacheKey = 'claim_agreement_previews_total_list_' . $userId;

    if (!empty($cacheKey)) {
        // Refresca el caché usando la clave y el tiempo de caché definidos
        $this->baseController->refreshCache($cacheKey, $this->cacheTime, function () {
            return $this->serviceData->getClaimAgreementByUser(Auth::user());
        });
    } else {
        throw new \Exception('Invalid cacheKey provided');
    }
    }


    private function deleteSignatureFromS3(string $path)
    {
    try {
        if (!empty($path)) {
            ImageHelper::deleteFileFromStorage($path);
        }
    } catch (Exception $e) {
        Log::error('Error occurred while deleting claim agreement preview from S3: ' . $e->getMessage(), ['exception' => $e]);
        throw new Exception('Error occurred while deleting claim agreement preview from S3.');
    }
    }
}
