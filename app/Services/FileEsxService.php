<?php // app/Services/FilesEsxService.php
namespace App\Services;

use App\Interfaces\FileEsxRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\FilesEsx;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Mail;
use App\Mail\FileEsxAssignmentNotification;
use App\Jobs\SendFileEsxAssignmentNotification;
use App\Models\User;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Helpers\ImageHelper;

class FileEsxService
{
    protected $baseController;
    protected $fileEsxRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;
    protected $userId;

    public function __construct(FileEsxRepositoryInterface $fileEsxRepositoryInterface, BaseController $baseController)
    {
        $this->fileEsxRepositoryInterface = $fileEsxRepositoryInterface;
        $this->baseController = $baseController;
    }

    public function all()
    {
        $this->userId = Auth::id();
        $this->cacheKey = 'filesEsx_' . $this->userId . '_total_list';

        return $this->baseController->getCachedData($this->cacheKey, $this->cacheTime, function () {
            return $this->fileEsxRepositoryInterface->getFileEsxByUser(auth()->user());
        });
    }



  public function storeFile(array $details)
{
    DB::beginTransaction();

    try {
        // Generar un UUID y asignar el usuario que sube el archivo
        $details['uuid'] = Uuid::uuid4()->toString();
        $details['uploaded_by'] = Auth::id();

        // Registrar información del archivo en el log
        $file = $details['file_path']; // Asumiendo que el archivo se encuentra en $details['file_path']
        //Log::info('Uploading file to S3:', [
            //'original_name' => $file->getClientOriginalName(),
            //'size' => $file->getSize(),
            //'mime_type' => $file->getMimeType(),
            //'extension' => $file->getClientOriginalExtension(),
        //]);

        // Subir el archivo a S3
        $filePath = ImageHelper::storeFile($file, 'public/xactimate_esx');
        $details['file_path'] = $filePath;

        // Almacenar los detalles en la base de datos
        $storedFile = $this->fileEsxRepositoryInterface->store($details);

        // Manejar las asignaciones 
        $this->handleAssignments($storedFile, $details);

        // Actualizar la caché de archivos
        $this->updateFilesCache();

        DB::commit();
        return $storedFile;
    } catch (Exception $ex) {
        DB::rollBack();
        Log::error('Error occurred while storing file: ' . $ex->getMessage());
        throw new Exception('Error occurred while storing file: ' . $ex->getMessage());
    }
}

public function updateFile(array $updateDetails, string $uuid)
{
    DB::beginTransaction();

    try {
        // Obtener el archivo existente usando el UUID
        $existingFile = $this->fileEsxRepositoryInterface->getByUuid($uuid);

        // Verificar si hay un nuevo archivo para reemplazar el anterior
        if (isset($updateDetails['file_path'])) {
            $newFile = $updateDetails['file_path'];

            // Eliminar el archivo anterior de S3 si existe
            if ($existingFile && $existingFile->file_path) {
                ImageHelper::deleteFileFromStorage($existingFile->file_path);
            }

            // Subir el nuevo archivo a S3 y obtener su URL
            $newFilePath = ImageHelper::storeFile($newFile, 'public/xactimate_esx');
            
            // Actualizar la ruta del archivo en los detalles para la base de datos
            $updateDetails['file_path'] = $newFilePath;
        }

        // Actualizar el archivo en la base de datos
        $file = $this->fileEsxRepositoryInterface->update($updateDetails, $uuid);

        // Actualizar la caché de archivos
        $this->updateFilesCache();

        DB::commit();
        return $file;
    } catch (Exception $ex) {
        DB::rollBack();
        Log::error('Error occurred while updating file: ' . $ex->getMessage());
        throw new Exception('Error occurred while updating file: ' . $ex->getMessage());
    }
}



    public function showFile(string $uuid)
    {
        $cacheKey = 'filesEsx_' . $uuid;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
            return $this->fileEsxRepositoryInterface->getByUuid($uuid);
        });
    }

    public function deleteFile(string $uuid)
{
    DB::beginTransaction();

    try {
        // Obtener el archivo existente usando el UUID
        $existingFile = $this->fileEsxRepositoryInterface->getByUuid($uuid);

        // Verificar si el archivo existe y si el usuario tiene permiso para eliminarlo
        if (!$existingFile || $existingFile->uploaded_by !== Auth::id()) {
            throw new Exception('No permission to delete this file or file not found.');
        }

        // Eliminar el archivo de S3
        if ($existingFile->file_path) {
            ImageHelper::deleteFileFromStorage($existingFile->file_path);
        }

        // Eliminar el registro de la base de datos
        $this->fileEsxRepositoryInterface->delete($uuid);

        // Invalidar la caché relacionada
        $this->baseController->invalidateCache('filesEsx_' . $uuid);

        // Actualizar la caché de la lista de archivos
        $this->updateFilesCache();

        DB::commit();

        return true; // Devolver true indicando que la operación fue exitosa
    } catch (Exception $ex) {
        DB::rollBack();
        Log::error('Error occurred while deleting file: ' . $ex->getMessage());
        throw new Exception('Error occurred while deleting file: ' . $ex->getMessage());
    }
}



    private function updateFilesCache()
    {
        $this->userId = Auth::id();
        $this->cacheKey = 'filesEsx_' . $this->userId . '_total_list';

        $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
            return FilesEsx::orderBy('id', 'DESC')->get();
        });
    }


    public function handleAssignments($storedFile, array $details)
{
    if (isset($details['public_adjuster_id'])) {
        // Usar la relación para hacer updateOrCreate
        $storedFile->assignments()->updateOrCreate(
            [
                'file_id' => $storedFile->id, // Esta condición es automática si se usa la relación
            ],
            [
                'public_adjuster_id' => $details['public_adjuster_id'], // El ID del public adjuster
                'assigned_by' => Auth::id(), // El usuario que asigna
            ]
        );
    }
}

}
