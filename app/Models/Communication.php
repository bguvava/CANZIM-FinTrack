<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Communication extends Model
{
    /** @use HasFactory<\Database\Factories\CommunicationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'communicable_id',
        'communicable_type',
        'type',
        'communication_date',
        'subject',
        'notes',
        'attachment_path',
        'next_action_date',
        'next_action_notes',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'communication_date' => 'datetime',
        'next_action_date' => 'date',
    ];

    /**
     * Get the parent communicable model (Donor, etc.).
     */
    public function communicable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the communication.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
