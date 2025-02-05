<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $tag
 * @property string $name
 * @property string|null $description
 * @property string|null $type
 * @property int|null $badge_id
 * @property int|null $required_trophies
 * @property int|null $trophies
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|Player[]|array|null $members
 */
class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory;

    public const CLUB_TYPES = [
        'social',
        'competitive',
        'casual',
    ];

    public const CLUB_MEMBER_ROLES = [
        'president',
        'vice president',
        'senior',
        'member',
    ];

    protected $table = 'clubs';

    protected $fillable = [
        'tag',
        'name',
        'description',
        'type',
        'badge_id',
        'required_trophies',
        'trophies',
    ];

    protected $casts = [
        'badge_id'          => 'integer',
        'required_trophies' => 'integer',
        'trophies'          => 'integer',
    ];

    /**
     * Get the club's members.
     *
     * @return HasMany
     */
    public function members(): HasMany
    {
        return $this->hasMany(Player::class, 'club_id', 'id');
    }
}
