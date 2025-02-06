<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $tag
 * @property string $name
 * @property string $name_color
 * @property int $icon_id
 * @property int $trophies
 * @property int|null $highest_trophies
 * @property int|null $highest_power_play_points
 * @property int|null $exp_level
 * @property int|null $exp_points
 * @property bool|null $is_qualified_from_championship_league
 * @property int|null $solo_victories
 * @property int|null $duo_victories
 * @property int|null $trio_victories
 * @property int|null $best_time_robo_rumble
 * @property int|null $best_time_as_big_brawler
 * @property int|null $club_id
 * @property string|null $club_role
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Club|null $club
 * @property-read Collection|PlayerBrawler[]|array $playerBrawlers
 */
class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory;

    protected $table = 'players';

    protected $fillable = [
        'tag',
        'name',
        'name_color',
        'icon_id',
        'trophies',
        'highest_trophies',
        'highest_power_play_points',
        'exp_level',
        'exp_points',
        'is_qualified_from_championship_league',
        'solo_victories',
        'duo_victories',
        'trio_victories',
        'best_time_robo_rumble',
        'best_time_as_big_brawler',
        'club_id',
        'club_role',
    ];

    protected $casts = [
        'icon_id'                               => 'integer',
        'trophies'                              => 'integer',
        'highest_trophies'                      => 'integer',
        'highest_power_play_points'             => 'integer',
        'exp_level'                             => 'integer',
        'exp_points'                            => 'integer',
        'is_qualified_from_championship_league' => 'bool',
        'solo_victories'                        => 'integer',
        'duo_victories'                         => 'integer',
        'trio_victories'                        => 'integer',
        'best_time_robo_rumble'                 => 'integer',
        'best_time_as_big_brawler'              => 'integer',
        'club_id'                               => 'integer',
    ];

    /**
     * Get the club that player belongs to.
     *
     * @return BelongsTo
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_id', 'id');
    }

    /**
     * Get the player brawlers.
     *
     * @return HasMany
     */
    public function playerBrawlers(): HasMany
    {
        return $this->hasMany(PlayerBrawler::class, 'player_id', 'id');
    }
}
