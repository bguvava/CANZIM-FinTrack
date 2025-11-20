<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'parent_id',
        'content',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the parent commentable model (project, budget, expense).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that created the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->with(['user', 'attachments', 'replies'])
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get all attachments for this comment.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(CommentAttachment::class);
    }

    /**
     * Check if the comment has been edited.
     */
    public function isEdited(): bool
    {
        return $this->created_at->ne($this->updated_at);
    }

    /**
     * Scope to get only root comments (not replies).
     */
    public function scopeRootOnly($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get comments for a specific entity.
     */
    public function scopeForEntity($query, string $type, int $id)
    {
        // Support both FQCN and short class names
        $shortName = class_basename($type);

        return $query->where(function ($q) use ($type, $shortName) {
            $q->where('commentable_type', $type)
                ->orWhere('commentable_type', $shortName);
        })->where('commentable_id', $id);
    }
}
