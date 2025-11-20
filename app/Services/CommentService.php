<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\CommentAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommentService
{
    /**
     * Create a new comment.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function createComment(array $data, User $user): Comment
    {
        try {
            return DB::transaction(function () use ($data, $user) {
                // Normalize commentable_type to short class name for consistency
                $commentableType = class_basename($data['commentable_type']);

                // Create the comment
                $comment = Comment::create([
                    'commentable_type' => $commentableType,
                    'commentable_id' => $data['commentable_id'],
                    'user_id' => $user->id,
                    'parent_id' => $data['parent_id'] ?? null,
                    'content' => $data['content'],
                ]);

                // Attach files if provided
                if (! empty($data['attachments']) && is_array($data['attachments'])) {
                    $this->attachFiles($comment, $data['attachments']);
                }

                // Parse and notify mentioned users
                $this->parseAndNotifyMentions($data['content'], $comment);

                // Load relationships
                $comment->load(['user', 'attachments', 'replies']);

                return $comment;
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to create comment: '.$e->getMessage());
        }
    }

    /**
     * Update an existing comment.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function updateComment(Comment $comment, array $data): Comment
    {
        try {
            return DB::transaction(function () use ($comment, $data) {
                // Update the comment content
                $comment->update([
                    'content' => $data['content'],
                ]);

                // Parse and notify mentioned users in updated content
                $this->parseAndNotifyMentions($data['content'], $comment);

                // Reload relationships
                $comment->load(['user', 'attachments', 'replies']);

                return $comment;
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to update comment: '.$e->getMessage());
        }
    }

    /**
     * Delete a comment (soft delete).
     *
     * @throws \Exception
     */
    public function deleteComment(Comment $comment): bool
    {
        try {
            return DB::transaction(function () use ($comment) {
                // Delete all attachments from storage
                foreach ($comment->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment->file_path);
                    $attachment->delete();
                }

                // Soft delete the comment
                return $comment->delete();
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete comment: '.$e->getMessage());
        }
    }

    /**
     * Parse comment content for @mentions and send notifications.
     */
    public function parseAndNotifyMentions(string $content, Comment $comment): void
    {
        // Extract @mentions from content (pattern: @username)
        preg_match_all('/@(\w+)/', $content, $matches);

        if (empty($matches[1])) {
            return;
        }

        $mentionedUsernames = array_unique($matches[1]);

        // Find users by name (case-insensitive)
        $mentionedUsers = User::whereIn('name', $mentionedUsernames)
            ->orWhere(function ($query) use ($mentionedUsernames) {
                foreach ($mentionedUsernames as $username) {
                    $query->orWhereRaw('LOWER(name) = ?', [strtolower($username)]);
                }
            })
            ->get();

        // Send notifications to mentioned users (excluding the comment author)
        $usersToNotify = $mentionedUsers->filter(function ($user) use ($comment) {
            return $user->id !== $comment->user_id;
        });

        if ($usersToNotify->isNotEmpty()) {
            // TODO: Implement CommentMentionNotification when ready
            // Notification::send($usersToNotify, new CommentMentionNotification($comment));
        }
    }

    /**
     * Attach uploaded files to a comment.
     *
     * @param  array<UploadedFile>  $files
     *
     * @throws \Exception
     */
    public function attachFiles(Comment $comment, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            try {
                // Generate file path
                $year = date('Y');
                $month = date('m');
                $directory = "attachments/{$year}/{$month}";

                // Generate unique filename
                $extension = $file->getClientOriginalExtension();
                $filename = Str::uuid().'.'.$extension;

                // Store file
                $path = $file->storeAs($directory, $filename, 'public');

                // Create attachment record
                CommentAttachment::create([
                    'comment_id' => $comment->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getMimeType(),
                ]);
            } catch (\Exception $e) {
                // Log error but continue with other files
                Log::error('Failed to attach file to comment: '.$e->getMessage());
            }
        }
    }

    /**
     * Get paginated comments for a specific entity.
     */
    public function getCommentsForEntity(string $type, int $id, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Comment::with(['user', 'attachments', 'replies.user', 'replies.attachments'])
            ->forEntity($type, $id)
            ->rootOnly()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
