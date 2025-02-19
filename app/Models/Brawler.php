<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\BrawlerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $ext_id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Accessory[]|array|Collection $accessories
 * @property-read Gear[]|array|Collection $gears
 * @property-read StarPower[]|array|Collection $starPowers
 * @property-read Player[]|array|Collection $players
 * @property-read BrawlerAccessory|null $brawler_accessory
 * @property-read BrawlerGear|null $brawler_gear
 * @property-read BrawlerStarPower|null $brawler_star_power
 * @property-read PlayerBrawler|null $player_brawler
 */
class Brawler extends Model
{
    /** @use HasFactory<BrawlerFactory> */
    use HasFactory;

    protected $table = 'brawlers';

    protected $fillable = [
        'ext_id',
        'name',
    ];

    protected $casts = [
        'ext_id' => 'integer',
    ];

    /**
     * Get the accessories that current brawler can have.
     *
     * @return BelongsToMany
     */
    public function accessories(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                related: Accessory::class,
                table: 'brawler_accessory',
                foreignPivotKey: 'brawler_id',
                relatedPivotKey: 'accessory_id',
            )
            ->using(BrawlerAccessory::class)
            ->withPivot([
                'id',
                'brawler_id',
                'accessory_id',
            ])
            ->as('brawler_accessory');
    }

    /**
     * Get the gears that current brawler can have.
     *
     * @return BelongsToMany
     */
    public function gears(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                related: Gear::class,
                table: 'brawler_gear',
                foreignPivotKey: 'brawler_id',
                relatedPivotKey: 'gear_id',
            )
            ->using(BrawlerGear::class)
            ->withPivot([
                'id',
                'brawler_id',
                'gear_id',
            ])
            ->as('brawler_gear');
    }

    /**
     * Get the star powers that current brawler can have.
     *
     * @return BelongsToMany
     */
    public function starPowers(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                related: StarPower::class,
                table: 'brawler_star_power',
                foreignPivotKey: 'brawler_id',
                relatedPivotKey: 'star_power_id',
            )
            ->using(BrawlerStarPower::class)
            ->withPivot([
                'id',
                'brawler_id',
                'star_power_id',
            ])
            ->as('brawler_star_power');
    }

    /**
     * Get the players that have this brawler.
     *
     * @return BelongsToMany
     */
    public function players(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                related: Player::class,
                table: 'player_brawlers',
                foreignPivotKey: 'brawler_id',
                relatedPivotKey: 'player_id',
            )
            ->using(PlayerBrawler::class)
            ->withPivot([
                'id',
                'power',
                'rank',
                'trophies',
                'highest_trophies',
            ])
            ->withTimestamps()
            ->as('player_brawler');
    }
}
