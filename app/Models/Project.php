<?php

namespace App\Models;

use App\Tenancy\BelongsToTeam;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Example tenant-owned resource. Copy this pattern for your own domain models.
 */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use BelongsToTeam, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
