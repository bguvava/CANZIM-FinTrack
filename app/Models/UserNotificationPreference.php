<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'expense_approvals',
        'budget_alerts',
        'project_milestones',
        'comment_mentions',
        'report_generation',
        'email_frequency',
    ];

    protected $casts = [
        'expense_approvals' => 'boolean',
        'budget_alerts' => 'boolean',
        'project_milestones' => 'boolean',
        'comment_mentions' => 'boolean',
        'report_generation' => 'boolean',
    ];

    /**
     * Get the user that owns the preferences
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
