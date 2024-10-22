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
}