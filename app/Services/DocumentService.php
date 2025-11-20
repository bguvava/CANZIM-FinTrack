<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Upload a new document
     */
    public function uploadDocument(array $data, UploadedFile $file, int $userId): Document
    {
        // Store the file
        $filePath = $this->storeFile($file);

        // Create document record
        $document = Document::create([
            'documentable_type' => $data['documentable_type'],
            'documentable_id' => $data['documentable_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'version_number' => 1,
            'uploaded_by' => $userId,
        ]);

        return $document;
    }

    /**
     * Replace document with new version
     */
    public function replaceDocument(Document $document, UploadedFile $file, int $userId): Document
    {
        // Archive current version
        DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => $document->version_number,
            'file_name' => $document->file_name,
            'file_path' => $document->file_path,
            'file_type' => $document->file_type,
            'file_size' => $document->file_size,
            'replaced_by' => $userId,
            'replaced_at' => now(),
        ]);

        // Store new file
        $filePath = $this->storeFile($file);

        // Update document
        $document->update([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'version_number' => $document->version_number + 1,
        ]);

        return $document->fresh();
    }

    /**
     * Update document metadata
     */
    public function updateMetadata(Document $document, array $data): Document
    {
        $document->update([
            'title' => $data['title'] ?? $document->title,
            'description' => $data['description'] ?? $document->description,
            'category' => $data['category'] ?? $document->category,
        ]);

        return $document->fresh();
    }

    /**
     * Soft delete document
     */
    public function deleteDocument(Document $document): bool
    {
        // Move file to archive (optional)
        $this->archiveFile($document->file_path);

        return $document->delete();
    }

    /**
     * Check if user has access to document
     */
    public function checkAccess(Document $document, int $userId): bool
    {
        // Check if user has access to the parent entity
        $documentable = $document->documentable;

        if (! $documentable) {
            return false;
        }

        // For Project documents
        if ($document->documentable_type === 'App\\Models\\Project') {
            return $documentable->users->contains($userId) ||
                $documentable->created_by === $userId;
        }

        // For Budget documents
        if ($document->documentable_type === 'App\\Models\\Budget') {
            return $documentable->project->users->contains($userId) ||
                $documentable->created_by === $userId;
        }

        // For Expense documents
        if ($document->documentable_type === 'App\\Models\\Expense') {
            return $documentable->submitted_by === $userId;
        }

        // For Donor documents - any authenticated user
        if ($document->documentable_type === 'App\\Models\\Donor') {
            return true;
        }

        return false;
    }

    /**
     * Store file in storage
     */
    private function storeFile(UploadedFile $file): string
    {
        $year = date('Y');
        $month = date('m');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs(
            "documents/{$year}/{$month}",
            $filename,
            'public'
        );
    }

    /**
     * Archive file (move to archive folder)
     */
    private function archiveFile(string $filePath): void
    {
        if (Storage::disk('public')->exists($filePath)) {
            $archivePath = str_replace('documents/', 'documents/archive/', $filePath);
            Storage::disk('public')->move($filePath, $archivePath);
        }
    }
}
