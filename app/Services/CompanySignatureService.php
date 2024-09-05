<?php

namespace App\Services;

use App\Interfaces\CompanySignatureRepositoryInterface;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Exception;
use Illuminate\Database\QueryException;
use App\Helpers\ImageHelper;

class CompanySignatureService
{
    protected $baseController;
    protected $companySignatureRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720; // Cache time in minutes

    public function __construct(
        CompanySignatureRepositoryInterface $companySignatureRepositoryInterface,
        BaseController $baseController
    ) {
        $this->companySignatureRepositoryInterface = $companySignatureRepositoryInterface;
        $this->baseController = $baseController;
    }

    // Método privado para obtener el userId dinámicamente
    private function getUserId()
    {
        return Auth::id();
    }

    public function all()
    {
        $userId = $this->getUserId();
        $this->cacheKey = 'company_signatures_' . $userId . '_total_list';

        return $this->cacheResult($this->cacheKey, function () {
            return $this->companySignatureRepositoryInterface->index();
        });
    }

    public function storeData(array $details)
    {
        return $this->handleTransaction(function () use ($details) {
            $userId = $this->getUserId();

            try {
                // Verificar si ya existe una firma registrada
                $existingSignature = $this->companySignatureRepositoryInterface->findFirst();

                if ($existingSignature) {
                    throw new Exception('A company signature already exists.');
                }

                // Asignar UUID y user_id
                $details['uuid'] = Uuid::uuid4()->toString();
                $details['user_id'] = $userId;

                // Almacenar la firma en S3 y obtener la URL
                $signatureUrl = ImageHelper::storeSignatureInS3($details['signature_path'], 'public/company_signatures');

                // Actualizar los detalles con la URL del archivo en S3
                $details['signature_path'] = $signatureUrl;

                // Crear la firma de la empresa
                $signature = $this->companySignatureRepositoryInterface->store($details);

                $this->updateCompanySignaturesCache();
                return $signature;
            } catch (QueryException $e) {
                Log::error('Database error occurred while storing company signature: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception('Database error occurred while storing company signature');
            } catch (Exception $e) {
                Log::error('Error occurred while storing company signature: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception('Error occurred while storing company signature');
            }
        });
    }

    public function updateData(array $updateDetails, string $uuid)
    {
        return $this->handleTransaction(function () use ($updateDetails, $uuid) {
            $userId = $this->getUserId();

            try {
                // Obtener la firma existente por UUID
                $existingSignature = $this->companySignatureRepositoryInterface->getByUuid($uuid);

                if (!$existingSignature) {
                    throw new Exception('Signature not found.');
                }

                // Verificar permisos del usuario
                if ($existingSignature->user_id !== $userId) {
                    throw new Exception('No Permission for update signature.');
                }

                // Manejar la firma nueva si se proporciona
                if (isset($updateDetails['signature_path'])) {
                    $newSignatureUrl = $this->replaceSignatureInS3($existingSignature, $updateDetails['signature_path']);
                    $updateDetails['signature_path'] = $newSignatureUrl;
                }

                // Actualizar la firma en la base de datos
                $signature = $this->companySignatureRepositoryInterface->update($updateDetails, $uuid);

                $this->updateCompanySignaturesCache();
                return $signature;
            } catch (QueryException $e) {
                Log::error('Database error occurred while updating company signature: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception('Database error occurred while updating company signature'. $e->getMessage());
            } catch (Exception $e) {
                Log::error('Error occurred while updating company signature: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception('Error occurred while updating company signature'. $e->getMessage());
            }
        });
    }

    public function showData(string $uuid)
    {
        $cacheKey = 'company_signature_' . $uuid;

        return $this->cacheResult($cacheKey, function () use ($uuid) {
            try {
                return $this->companySignatureRepositoryInterface->getByUuid($uuid);
            } catch (QueryException $e) {
                Log::error('Database error occurred while retrieving company signature: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception('Database error occurred while retrieving company signature');
            } catch (Exception $e) {
                Log::error('Error occurred while retrieving company signature: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception('Error occurred while retrieving company signature');
            }
        });
    }

    public function deleteData(string $uuid)
    {
        return $this->handleTransaction(function () use ($uuid) {
            $userId = $this->getUserId();

            try {
                // Obtener la firma existente por UUID
                $existingSignature = $this->companySignatureRepositoryInterface->getByUuid($uuid);

                if (!$existingSignature || $existingSignature->user_id !== $userId) {
                    throw new Exception('Signature not found or no permission to delete.');
                }

                // Eliminar la firma de la base de datos
                $this->companySignatureRepositoryInterface->delete($uuid);

                // Invalida la caché
                $this->baseController->invalidateCache('company_signature_' . $uuid);
                $this->updateCompanySignaturesCache();

                return $existingSignature;
            } catch (QueryException $e) {
                Log::error('Database error occurred while deleting company signature: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception('Database error occurred while deleting company signature');
            } catch (Exception $e) {
                Log::error('Error occurred while deleting company signature: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception('Error occurred while deleting company signature');
            }
        });
    }

    public function restoreSignature(string $uuid)
    {
        return $this->handleTransaction(function () use ($uuid) {
            try {
                $userId = $this->getUserId();

                // Restaurar la firma y actualizar caché
                $signature = $this->companySignatureRepositoryInterface->restore($uuid);

                $this->baseController->invalidateCache('company_signature_' . $uuid);
                $this->updateCompanySignaturesCache();

                return $signature;
            } catch (QueryException $e) {
                Log::error('Database error occurred while restoring company signature: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception('Database error occurred while restoring company signature');
            } catch (Exception $e) {
                Log::error('Error occurred while restoring company signature: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception('Error occurred while restoring company signature');
            }
        });
    }

    private function cacheResult(string $cacheKey, callable $callback)
    {
        try {
            return Cache::remember($cacheKey, $this->cacheTime, $callback);
        } catch (QueryException $e) {
            Log::error('Database error occurred while caching result: ' . $e->getMessage(), ['exception' => $e]);
            throw new Exception('Database error occurred while caching result');
        } catch (Exception $e) {
            Log::error('Error occurred while caching result: ' . $e->getMessage(), ['exception' => $e]);
            throw new Exception('Error occurred while caching result');
        }
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

    private function replaceSignatureInS3($existingSignature, string $newSignatureData): string
    {
        try {
            if (isset($existingSignature->signature_path)) {
                $this->deleteSignatureFromS3($existingSignature->signature_path);
            }
            return ImageHelper::storeSignatureInS3($newSignatureData, 'public/company_signatures');
        } catch (Exception $e) {
            Log::error('Error occurred while replacing signature in S3: ' . $e->getMessage(), ['exception' => $e]);
            throw new Exception('Error occurred while replacing signature in S3');
        }
    }

    private function deleteSignatureFromS3(string $signatureUrl)
    {
        try {
            if (!empty($signatureUrl)) {
                ImageHelper::deleteFileFromStorage($signatureUrl);
            }
        } catch (Exception $e) {
            Log::error('Error occurred while deleting signature from S3: ' . $e->getMessage(), ['exception' => $e]);
            throw new Exception('Error occurred while deleting signature from S3');
        }
    }

    private function updateCompanySignaturesCache()
    {
        $userId = $this->getUserId();
        $this->cacheKey = 'company_signatures_' . $userId . '_total_list';

        if (!empty($this->cacheKey)) {
            try {
                $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                    return $this->companySignatureRepositoryInterface->index();
                });
            } catch (Exception $e) {
                Log::error('Error occurred while updating company signatures cache: ' . $e->getMessage(), ['exception' => $e]);
                throw new Exception('Error occurred while updating company signatures cache');
            }
        } else {
            throw new Exception('Invalid cacheKey provided');
        }
    }
}
