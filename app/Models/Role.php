<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Role Model
 *
 * Represents user roles in the system
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 */
class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Get the users for the role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role is Programs Manager
     */
    public function isProgramsManager(): bool
    {
        return $this->slug === 'programs-manager';
    }

    /**
     * Check if role is Finance Officer
     */
    public function isFinanceOfficer(): bool
    {
        return $this->slug === 'finance-officer';
    }

    /**
     * Check if role is Project Officer
     */
    public function isProjectOfficer(): bool
    {
        return $this->slug === 'project-officer';
    }
}
