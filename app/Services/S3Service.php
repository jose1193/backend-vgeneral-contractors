<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class S3Service
{
    private const MAX_IMAGE_DIMENSION = 700;
    private const ALLOWED_DOCUMENT_TYPES = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
    ];
    
    public function storeAndResize($image, string $storagePath): string
    {
        try {
            $resizedImagePath = $this->resizeAndStoreTempImage($image);
            $uniqueFileName = $this->generateUniqueFileName();
            $photoPath = $this->storeFileInS3($resizedImagePath, $storagePath, $uniqueFileName);
            
            if (file_exists($resizedImagePath)) {
                unlink($resizedImagePath);
            }
            
            return $photoPath;
        } catch (\Exception $e) {
            Log::error('Error en storeAndResize: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteFileFromStorage($fullUrl): bool
    {
        $relativePath = $this->getRelativePath($fullUrl);
        try {
            if (Storage::disk('s3')->exists($relativePath)) {
                return Storage::disk('s3')->delete($relativePath);
            } else {
                Log::warning("El archivo no existe en S3: {$relativePath}");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error al eliminar archivo de S3: {$relativePath}. Error: " . $e->getMessage());
            return false;
        }
    }

    public function storeSignatureInS3($signatureData, string $storagePath): string
    {
        try {
            // Verificar si es un PNG o una cadena base64
            if (preg_match('#^data:image/\w+;base64,#i', $signatureData)) {
                // Si es base64, lo procesamos
                $base64Image = preg_replace('#^data:image/\w+;base64,#i', '', $signatureData);
                $imageData = base64_decode($base64Image);

                if ($imageData === false) {
                    throw new \Exception('Invalid base64 image data');
                }

                // Crear un archivo temporal
                $tempFile = tempnam(sys_get_temp_dir(), 'signature');
                file_put_contents($tempFile, $imageData);
                $imagePath = $tempFile;
            } else {
                // Si no es base64, asumimos que es una ruta de archivo
                $imagePath = $signatureData;
            }

            // Usar el método storeAndResize para almacenar y redimensionar
            $result = $this->storeAndResize($imagePath, $storagePath);

            // Limpiar archivo temporal si existe
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error en storeSignatureInS3: ' . $e->getMessage());
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw $e;
        }
    }

    public function storeFile($file, string $storagePath): string
    {
        try {
            if ($file instanceof UploadedFile) {
                $uniqueFileName = $this->generateUniqueFileName() . '.' . $file->getClientOriginalExtension();
                $s3Path = $storagePath . '/' . $uniqueFileName;
                Storage::disk('s3')->put($s3Path, fopen($file->getRealPath(), 'r+'));
                return Storage::disk('s3')->url($s3Path);
            }

            if (is_string($file)) {
                if (preg_match('#^data:image/\w+;base64,#i', $file)) {
                    // Manejar base64
                    return $this->storeSignatureInS3($file, $storagePath);
                }
                if (file_exists($file)) {
                    // Manejar archivo físico
                    return $this->storeAndResize($file, $storagePath);
                }
            }

            throw new \InvalidArgumentException('Unsupported file type or invalid file');
        } catch (\Exception $e) {
            Log::error("Error storing file in S3: " . $e->getMessage());
            throw $e;
        }
    }

    public function storeFileS3($pdfContent, string $storagePath): string
    {
        $s3Path = $storagePath;
        Storage::disk('s3')->put($s3Path, $pdfContent);
        return Storage::disk('s3')->url($s3Path);
    }

    private function resizeAndStoreTempImage($image): string
    {
        $image = Image::make($image);
        $originalWidth = $image->width();
        $originalHeight = $image->height();

        if ($originalWidth > self::MAX_IMAGE_DIMENSION || $originalHeight > self::MAX_IMAGE_DIMENSION) {
            $scaleFactor = min(
                self::MAX_IMAGE_DIMENSION / $originalWidth,
                self::MAX_IMAGE_DIMENSION / $originalHeight
            );
            $newWidth = (int)($originalWidth * $scaleFactor);
            $newHeight = (int)($originalHeight * $scaleFactor);
            $image->resize($newWidth, $newHeight);
        }

        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.jpg';
        $image->save($tempPath);
        return $tempPath;
    }

    private function generateUniqueFileName(): string
    {
        return Str::random(40);
    }

    private function storeFileInS3(string $filePath, string $storagePath, ?string $fileName = null): string
    {
        $fileName = $fileName ?: $this->generateUniqueFileName();
        $s3Path = $storagePath . '/' . $fileName;
        Storage::disk('s3')->put($s3Path, fopen($filePath, 'r+'));
        return Storage::disk('s3')->url($s3Path);
    }

    private function getRelativePath(string $fullUrl): string
    {
        $parsedUrl = parse_url($fullUrl);
        $relativePath = ltrim($parsedUrl['path'] ?? '', '/');
        $bucketName = env('AWS_BUCKET');
        return preg_replace("/^{$bucketName}\//", '', $relativePath);
    }

    private function storeAndResizeProfilePhoto($image, string $storagePath): ?string
    {
        try {
            $photoPath = $this->storeFile($image, $storagePath);
            return $photoPath;
        } catch (\Exception $e) {
            Log::error('Failed to store or resize image: ' . $e->getMessage());
            return null;
        }
    }

   public function storeAgreementFile($file, string $storagePath): string 
{
    try {
        if (!$file instanceof UploadedFile) {
            throw new \InvalidArgumentException('File must be an instance of UploadedFile');
        }

        // Validar el tipo MIME del archivo
        $mimeType = $file->getMimeType();
        if (!array_key_exists($mimeType, self::ALLOWED_DOCUMENT_TYPES)) {
            Log::error('Invalid document type', [
                'mime_type' => $mimeType,
                'allowed_types' => array_keys(self::ALLOWED_DOCUMENT_TYPES)
            ]);
            throw new \InvalidArgumentException('Invalid document type. Allowed types are: doc, docx, pdf');
        }

        // Obtener el nombre original y su extensión
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $nameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);

        // Generar timestamp
        $timestamp = now()->format('m-d-Y-His');

        // Crear nuevo nombre con timestamp y limpiar caracteres especiales
        $newName =  $nameWithoutExtension.'-'.$timestamp;
        $safeName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $newName) . '.' . $extension;

        // Construir la ruta completa en S3
        $s3Path = $storagePath . '/' . $safeName;

        // Registrar información del archivo antes de guardarlo
        Log::info('Storing agreement file', [
            'original_name' => $originalName,
            'safe_name' => $safeName,
            'timestamp' => $timestamp,
            'mime_type' => $mimeType,
            's3_path' => $s3Path
        ]);

        // Almacenar el archivo en S3
        Storage::disk('s3')->put($s3Path, fopen($file->getRealPath(), 'r+'));

        // Obtener y validar la URL del archivo almacenado
        $fileUrl = Storage::disk('s3')->url($s3Path);

        Log::info('Agreement file stored successfully', [
            'file_url' => $fileUrl
        ]);

        return $fileUrl;
    } catch (\Exception $e) {
        Log::error('Error storing agreement file', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}
    
    public function deleteFromStorageAgreement($fullUrl): bool 
    {
        // Si el URL está vacío, retornamos false
        if (empty($fullUrl)) {
        Log::warning("URL vacío proporcionado para eliminación de acuerdo");
        return false;
        }

        try {
        // Obtener el nombre del archivo del URL
        $fileName = basename($fullUrl);
        
        // Asegurarnos que el nombre del archivo esté correctamente formateado
        $sanitizedFileName = str_replace(' ', '-', $fileName);
        $sanitizedFileName = preg_replace('/[^A-Za-z0-9\-._]/', '', $sanitizedFileName);
        
        // Log para debugging
        Log::info("Intentando eliminar acuerdo", [
            'original_name' => $fileName,
            'sanitized_name' => $sanitizedFileName
        ]);

        // Primero intentamos con el nombre original
        if (Storage::disk('s3')->exists($fileName)) {
            $result = Storage::disk('s3')->delete($fileName);
            Log::info("Archivo de acuerdo eliminado con nombre original", [
                'success' => $result,
                'filename' => $fileName
            ]);
            return $result;
        }
        
        // Si no existe, intentamos con el nombre sanitizado
        if (Storage::disk('s3')->exists($sanitizedFileName)) {
            $result = Storage::disk('s3')->delete($sanitizedFileName);
            Log::info("Archivo de acuerdo eliminado con nombre sanitizado", [
                'success' => $result,
                'filename' => $sanitizedFileName
            ]);
            return $result;
        }

        // Si llegamos aquí, el archivo no existe
        Log::warning("El archivo de acuerdo no existe en S3", [
            'original_name' => $fileName,
            'sanitized_name' => $sanitizedFileName
        ]);
        
        return false;

        } catch (\Exception $e) {
        Log::error("Error al eliminar archivo de acuerdo de S3", [
            'filename' => $fullUrl,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return false;
        }
    }

}