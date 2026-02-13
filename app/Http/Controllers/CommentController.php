<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Comment Controller
 *
 * Handles commenting operations for projects, budgets, and expenses
 */
class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $commentService
    ) {}

    /**
     * Get comments for a specific entity
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
        ]);

        $comments = $this->commentService->getCommentsForEntity(
            $request->input('commentable_type'),
            (int) $request->input('commentable_id')
        );

        return response()->json([
            'status' => 'success',
            'data' => CommentResource::collection($comments),
        ]);
    }

    /**
     * Store a newly created comment
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $comment = $this->commentService->createComment(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Comment added successfully.',
            'data' => new CommentResource($comment->load(['user', 'attachments'])),
        ], 201);
    }

    /**
     * Update the specified comment
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $updatedComment = $this->commentService->updateComment(
            $comment,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Comment updated successfully.',
            'data' => new CommentResource($updatedComment->load(['user', 'attachments'])),
        ]);
    }

    /**
     * Remove the specified comment
     */
    public function destroy(Comment $comment, Request $request): JsonResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->deleteComment($comment, $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Comment deleted successfully.',
        ]);
    }

    /**
     * Download a comment attachment
     */
    public function downloadAttachment(int $attachmentId): mixed
    {
        return $this->commentService->downloadAttachment($attachmentId);
    }
}
