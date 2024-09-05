<?php

namespace App\Services;

use App\Interfaces\DocumentTemplateRepositoryInterface;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Exception;
use Illuminate\Database\QueryException;
use App\Helpers\ImageHelper;

class DocumentTemplateService
{
    protected $serviceData;
    protected $baseController;
    protected $cacheTime = 720; 

    public function __construct(
        DocumentTemplateRepositoryInterface $serviceData,
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
        $cacheKey = 'document_templates_total_list_' . $userId;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($userId) {
            return $this->serviceData->getDocumentTemplatesByUser(Auth::user());
        });
    }

    public function storeData(array $details)
    {
        return $this->handleTransaction(function () use ($details) {
            try {
                // Store the file in S3
                $storedFilePath = ImageHelper::storeFile($details['template_path'], 'public/document_templates');

                $signature = $this->serviceData->getCompanySignature();

                $details['uuid'] = Uuid::uuid4()->toString();
                $details['uploaded_by'] = $this->getUserId();
                $details['signature_path_id'] = $signature->id;
                $details['template_path'] = $storedFilePath;

                // Store the template information in the database
                $template = $this->serviceData->store($details);

                $this->updateDataCache();
                return $template;

            } catch (Exception $e) {
                $this->handleException($e, 'storing document template');
            }
        });
    }

    public function updateData(array $updateDetails, string $uuid)
 {
    return $this->handleTransaction(function () use ($updateDetails, $uuid) {
        try {
            $existingTemplate = $this->serviceData->getByUuid($uuid);
            Log::info('Existing Template:', ['template' => $existingTemplate]);

            if (isset($updateDetails['template_path'])) {
                // Delete the existing file from S3
                if ($existingTemplate && $existingTemplate->template_path) {
                    Log::info('Deleting old template path from S3:', ['path' => $existingTemplate->template_path]);
                    ImageHelper::deleteFileFromStorage($existingTemplate->template_path);
                }

                // Store the new file in S3
                $newFilePath = ImageHelper::storeFile($updateDetails['template_path'], 'public/document_templates/');
                $updateDetails['template_path'] = $newFilePath;
            }

            // Update the template information in the database
            $updatedTemplate = $this->serviceData->update($updateDetails, $uuid);
            Log::info('Updated Template:', ['template' => $updatedTemplate]);

            $this->updateDataCache();
            return $updatedTemplate;

        } catch (Exception $e) {
            $this->handleException($e, 'updating document template');
        }
    });
 }


    public function showData(string $uuid)
    {
        $cacheKey = 'document_template_' . $uuid;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
            try {
                return $this->serviceData->getByUuid($uuid);
            } catch (Exception $e) {
                $this->handleException($e, 'fetching document template');
                return null;
            }
        });
    }

    public function deleteData(string $uuid)
 {
    return $this->handleTransaction(function () use ($uuid) {
        try {
            $cacheKey = 'document_template_' . $uuid;
            $existingTemplate = $this->serviceData->getByUuid($uuid);

            if (!$existingTemplate) {
                throw new \Exception("Template not found");
            }

            // Delete the template from the database
            $this->serviceData->delete($uuid);

            // Delete the file from S3
            $deleted = ImageHelper::deleteFileFromStorage($existingTemplate->template_path);

            if (!$deleted) {
                throw new \Exception("Failed to delete file from S3");
            }

            // Invalidate the cache
            $this->baseController->invalidateCache($cacheKey);
            $this->updateDataCache();

        } catch (\Exception $e) {
            $this->handleException($e, 'deleting document template');
            // Optionally rethrow the exception if you need to handle it further up the call stack
            throw $e;
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
            throw $ex;
        }
    }

    private function handleException(Exception $e, string $context)
    {
        Log::error("Error occurred while {$context}: " . $e->getMessage(), [
            'exception' => $e,
            'stack_trace' => $e->getTraceAsString(),
            'user_id' => Auth::id(),
            'context' => $context
        ]);

        throw $e;
    }

    private function updateDataCache()
    {
        $userId = Auth::id();
        $cacheKey = 'document_templates_total_list_' . $userId;

        if (!empty($cacheKey)) {
            $this->baseController->refreshCache($cacheKey, $this->cacheTime, function () {
                return $this->serviceData->getDocumentTemplatesByUser(Auth::user());
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }

    
}
