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
 * @property int|null $ext_id
 * @property string $tag
 * @property string $name
 * @property string $name_color
 * @property int|null $icon_id
 * @property int $trophies
 * @property int $highest_trophies
 * @property int $highest_power_play_points
 * @property int $exp_level
 * @property int $exp_points
 * @property bool $is_qualified_from_championship_league
 * @property int $solo_victories
 * @property int $duo_victories
 * @property int $trio_victories
 * @property int $best_time_robo_rumble
 * @property int $best_time_as_big_brawler
 * @property int|null $club_id
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
        'ext_id',
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
    ];

    protected $casts = [
        'ext_id'                                => 'integer',
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
