<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $player_id
 * @property int $brawler_id
 * @property int $power
 * @property int $rank
 * @property int $trophies
 * @property int $highest_trophies
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Player $player
 * @property-read Brawler $brawler
 * @property-read Collection|PlayerBrawlerAccessory[]|array $playerBrawlerAccessories
 * @property-read Collection|PlayerBrawlerGear[]|array $playerBrawlerGears
 * @property-read Collection|PlayerBrawlerStarPower[]|array $playerBrawlerStarPowers
 */
class PlayerBrawler extends Pivot
{
    // todo
    use HasFactory;

    protected $table = 'player_brawlers';

    protected $fillable = [
        'player_id',
        'brawler_id',
        'power',
        'rank',
        'trophies',
        'highest_trophies',
    ];

    protected $casts = [
        'player_id'        => 'integer',
        'brawler_id'       => 'integer',
        'power'            => 'integer',
        'rank'             => 'integer',
        'trophies'         => 'integer',
        'highest_trophies' => 'integer',
    ];

    /**
     * Get the related player.
     *
     * @return BelongsTo
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'id');
    }

    /**
     * Get the related brawler.
     *
     * @return BelongsTo
     */
    public function brawler(): BelongsTo
    {
        return $this->belongsTo(Brawler::class, 'brawler_id', 'id');
    }

    /**
     * Get the accessories for the player's brawler.
     *
     * @return HasMany
     */
    public function playerBrawlerAccessories(): HasMany
    {
        return $this->hasMany(
            related: PlayerBrawlerAccessory::class,
            foreignKey: 'player_brawler_id',
            localKey: 'id',
        );
    }

    /**
     * Get the gears for the player's brawler.
     *
     * @return HasMany
     */
    public function playerBrawlerGears(): HasMany
    {
        return $this->hasMany(
            related: PlayerBrawlerGear::class,
            foreignKey: 'player_brawler_id',
            localKey: 'id',
        );
    }

    /**
     * Get the star powers for the player's brawler.
     *
     * @return HasMany
     */
    public function playerBrawlerStarPowers(): HasMany
    {
        return $this->hasMany(
            related: PlayerBrawlerStarPower::class,
            foreignKey: 'player_brawler_id',
            localKey: 'id',
        );
    }
}
