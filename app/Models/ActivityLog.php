<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Activity Log Model
 *
 * Tracks user activities within the system
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $activity_type
 * @property string $description
 * @property array|null $properties
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read User|null $user
 */
class ActivityLog extends Model
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'properties',
        'created_at',
    ];

    /**
     * Get the user that owns the activity log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new activity log entry
     */
    public static function log(?int $userId, string $type, string $description, array $properties = []): void
    {
        static::create([
            'user_id' => $userId,
            'activity_type' => $type,
            'description' => $description,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}
