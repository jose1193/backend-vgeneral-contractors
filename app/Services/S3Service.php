<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class S3Service
{
    // Stores and resizes an image, returns the URL of the stored file
    public function storeAndResize($image, $storagePath)
    {
        $resizedImagePath = $this->resizeAndStoreTempImage($image);
        $uniqueFileName = $this->generateUniqueFileName();
        $photoPath = $this->storeFileInS3($resizedImagePath, $storagePath, $uniqueFileName);
        unlink($resizedImagePath);
        return $photoPath;
    }

    // Deletes a file from S3
    public function deleteFileFromStorage($fullUrl)
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

    // Stores and resizes a profile picture, returns the relative path of the stored file, handles exceptions
    public function storeAndResizeProfilePhoto($image, $storagePath)
    {
        try {
            $photoPath = $this->storeImage($image, $storagePath);
            $this->resizeImage(storage_path('app/' . $photoPath));
            return 'app/' . $photoPath;
        } catch (\Exception $e) {
            Log::error('Failed to store or resize image: ' . $e->getMessage());
            return null;
        }
    }
    

    
    // Stores a file directly to S3
    public function storeFile($file, $storagePath)
    {
        $uniqueFileName = $this->generateUniqueFileName() . '.' . $file->getClientOriginalExtension();
        $s3Path = $storagePath . '/' . $uniqueFileName;
        Storage::disk('s3')->put($s3Path, fopen($file->getRealPath(), 'r+'));
        return Storage::disk('s3')->url($s3Path);
    }

    // Stores a signature image in S3
    public function storeSignatureInS3($signatureData, $storagePath)
    {
        $imageData = base64_decode($signatureData);
        $image = Image::make($imageData);
        $resizedImagePath = $this->resizeAndStoreTempImage($image);
        $uniqueFileName = $this->generateUniqueFileName() . '.png';
        $s3Path = $storagePath . '/' . $uniqueFileName;
        Storage::disk('s3')->put($s3Path, file_get_contents($resizedImagePath));
        unlink($resizedImagePath);
        return Storage::disk('s3')->url($s3Path);
    }


    
    // Stores a PDF agreement in S3
    public function storeFileS3($pdfContent, $storagePath)
    {
        $s3Path = $storagePath;
        Storage::disk('s3')->put($s3Path, $pdfContent);
        return Storage::disk('s3')->url($s3Path);
    }

    // Resizes and stores a temporary image
    private function resizeAndStoreTempImage($image)
    {
        $image = Image::make($image);
        $originalWidth = $image->width();
        $originalHeight = $image->height();

        if ($originalWidth > 700 || $originalHeight > 700) {
            $scaleFactor = min(700 / $originalWidth, 700 / $originalHeight);
            $newWidth = $originalWidth * $scaleFactor;
            $newHeight = $originalHeight * $scaleFactor;
            $image->resize($newWidth, $newHeight);
        }

        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.jpg';
        $image->save($tempPath);
        return $tempPath;
    }

    // Generates a unique file name
    private function generateUniqueFileName()
    {
        return Str::random(40);
    }

     
    // Stores a file to S3 and returns its URL
    private function storeFileInS3($filePath, $storagePath, $fileName = null)
    {
        $fileName = $fileName ?: $this->generateUniqueFileName();
        $s3Path = $storagePath . '/' . $fileName;
        Storage::disk('s3')->put($s3Path, fopen($filePath, 'r+'));
        return Storage::disk('s3')->url($s3Path);
    }

    // Resizes an image located at the given path
    private function resizeImage($imagePath)
    {
        $image = Image::make($imagePath);
        $image->resize(700, 700, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image->save($imagePath);
    }

    // Extracts the relative path from a full URL
    private function getRelativePath($fullUrl)
    {
        $parsedUrl = parse_url($fullUrl);
        $relativePath = ltrim($parsedUrl['path'], '/');
        $bucketName = env('AWS_BUCKET');
        return preg_replace("/^{$bucketName}\//", '', $relativePath);
    }
}
