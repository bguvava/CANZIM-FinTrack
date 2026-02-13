<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\CommentAttachment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    /**
     * CommentController constructor.
     */
    public function __construct(
        protected CommentService $commentService
    ) {
        // Policy-based authorization handled per method
    }

    /**
     * Display a listing of comments for a specific entity.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Comment::class);

        try {
            // Validate required query parameters
            $validated = $request->validate([
                'commentable_type' => ['required', 'string'],
                'commentable_id' => ['required', 'integer'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]);

            $comments = $this->commentService->getCommentsForEntity(
                $validated['commentable_type'],
                $validated['commentable_id'],
                $validated['per_page'] ?? 20
            );

            return response()->json([
                'data' => CommentResource::collection($comments),
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving comments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created comment.
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $this->authorize('create', Comment::class);

        try {
            $data = $request->validated();

            // Handle file uploads
            if ($request->hasFile('attachments')) {
                $data['attachments'] = $request->file('attachments');
            }

            $comment = $this->commentService->createComment($data, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Comment created successfully',
                'data' => new CommentResource($comment),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating comment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified comment with its replies.
     */
    public function show(Comment $comment): JsonResponse
    {
        $this->authorize('view', $comment);

        try {
            $comment->load(['user', 'attachments', 'replies.user', 'replies.attachments']);

            return response()->json([
                'data' => new CommentResource($comment),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving comment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified comment.
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        try {
            $data = $request->validated();
            $updatedComment = $this->commentService->updateComment($comment, $data);

            return response()->json([
                'success' => true,
                'message' => 'Comment updated successfully',
                'data' => new CommentResource($updatedComment),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating comment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified comment (soft delete).
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        try {
            $this->commentService->deleteComment($comment);

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting comment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a comment attachment.
     */
    public function downloadAttachment(CommentAttachment $attachment)
    {
        $this->authorize('view', $attachment->comment);

        if (! Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }

        return response()->download(
            Storage::disk('public')->path($attachment->file_path),
            $attachment->file_name
        );
    }
}
