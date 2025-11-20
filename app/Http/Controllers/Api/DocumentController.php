<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentService $documentService
    ) {}

    /**
     * Display a listing of documents.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Document::with(['uploader', 'documentable'])
            ->withCount('versions');

        // Filter by documentable type and id
        if ($request->has('documentable_type') && $request->has('documentable_id')) {
            $query->where('documentable_type', $request->documentable_type)
                ->where('documentable_id', $request->documentable_id);
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // File type filter
        if ($request->has('file_type')) {
            $fileType = $request->file_type;
            $query->where('file_type', 'like', "%{$fileType}%");
        }

        // Date range filter
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $documents = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => DocumentResource::collection($documents),
            'meta' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
            ],
        ]);
    }

    /**
     * Store a newly created document.
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $document = $this->documentService->uploadDocument(
            $request->validated(),
            $request->file('file'),
            $request->user()->id
        );

        // Log activity
        ActivityLog::log(
            $request->user()->id,
            'document_upload',
            "Uploaded document: {$document->title}",
            ['document_id' => $document->id, 'file_name' => $document->file_name]
        );

        return response()->json([
            'message' => 'Document uploaded successfully',
            'data' => new DocumentResource($document->load(['uploader', 'versions'])),
        ], 201);
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $document->load(['uploader', 'versions.replacer', 'documentable']);

        return response()->json([
            'data' => new DocumentResource($document),
        ]);
    }

    /**
     * Update the specified document metadata.
     */
    public function update(UpdateDocumentRequest $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        $document = $this->documentService->updateMetadata(
            $document,
            $request->validated()
        );

        // Log activity
        ActivityLog::log(
            $request->user()->id,
            'document_update',
            "Updated document metadata: {$document->title}",
            ['document_id' => $document->id]
        );

        return response()->json([
            'message' => 'Document updated successfully',
            'data' => new DocumentResource($document->load(['uploader', 'versions'])),
        ]);
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Request $request, Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        $this->documentService->deleteDocument($document);

        // Log activity
        ActivityLog::log(
            $request->user()->id,
            'document_delete',
            "Deleted document: {$document->title}",
            ['document_id' => $document->id, 'file_name' => $document->file_name]
        );

        return response()->json([
            'message' => 'Document deleted successfully',
        ]);
    }

    /**
     * View document (inline in browser).
     */
    public function view(Document $document): BinaryFileResponse
    {
        $this->authorize('view', $document);

        $path = Storage::disk('public')->path($document->file_path);

        if (! file_exists($path)) {
            abort(404, 'File not found');
        }

        return response()->file($path, [
            'Content-Type' => $document->file_type,
            'Content-Disposition' => 'inline; filename="' . $document->file_name . '"',
        ]);
    }

    /**
     * Download document.
     */
    public function download(Document $document): BinaryFileResponse
    {
        $this->authorize('download', $document);

        $path = Storage::disk('public')->path($document->file_path);

        if (! file_exists($path)) {
            abort(404, 'File not found');
        }

        // Log activity
        ActivityLog::log(
            $document->uploaded_by,
            'document_download',
            "Downloaded document: {$document->title}",
            ['document_id' => $document->id, 'file_name' => $document->file_name]
        );

        return response()->download($path, $document->file_name, [
            'Content-Type' => $document->file_type,
        ]);
    }

    /**
     * Replace document with new version.
     */
    public function replace(Request $request, Document $document): JsonResponse
    {
        $this->authorize('replace', $document);

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:5120'],
        ]);

        $document = $this->documentService->replaceDocument(
            $document,
            $request->file('file'),
            $request->user()->id
        );

        // Log activity
        ActivityLog::log(
            $request->user()->id,
            'document_replace',
            "Replaced document with version {$document->version_number}: {$document->title}",
            ['document_id' => $document->id, 'version_number' => $document->version_number]
        );

        return response()->json([
            'message' => 'Document replaced successfully',
            'data' => new DocumentResource($document->load(['uploader', 'versions'])),
        ]);
    }

    /**
     * Get document versions.
     */
    public function versions(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $versions = $document->versions()->with('replacer')->get();

        return response()->json([
            'data' => $versions,
        ]);
    }

    /**
     * Get document categories.
     */
    public function categories(): JsonResponse
    {
        $categories = DocumentCategory::active()->ordered()->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Manage document categories (Programs Manager only).
     */
    public function manageCategories(Request $request): JsonResponse
    {
        if ($request->user()->role->slug !== 'programs-manager') {
            abort(403, 'Only Programs Manager can manage categories');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $category = DocumentCategory::create($request->all());

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }
}
