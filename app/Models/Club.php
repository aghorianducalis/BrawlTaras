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
 * @property string $description
 * @property string $type todo enum or ClubType model
 * @property int $badge_id
 * @property int $required_trophies
 * @property int $trophies
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|Player[]|array $members
 */
class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory;

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
