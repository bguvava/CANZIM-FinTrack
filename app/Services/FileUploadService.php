<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload a receipt file.
     */
    public function uploadReceipt(UploadedFile $file): array
    {
        // Validate file
        $this->validateFile($file);

        // Generate file path
        $year = date('Y');
        $month = date('m');
        $directory = "receipts/{$year}/{$month}";

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.$extension;

        // Store file
        $path = $file->storeAs($directory, $filename, 'public');

        return [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Delete a receipt file.
     */
    public function deleteReceipt(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        return Storage::disk('public')->delete($path);
    }

    /**
     * Validate uploaded file.
     */
    private function validateFile(UploadedFile $file): void
    {
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];

        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size must not exceed 5MB');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $allowedTypes)) {
            throw new \Exception('Only PDF, JPG, JPEG, and PNG files are allowed');
        }
    }
}
