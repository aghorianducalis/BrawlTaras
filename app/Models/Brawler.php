<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $ext_id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|Accessory[]|array $accessories
 * @property-read Collection|Gear[]|array $gears
 * @property-read Collection|StarPower[]|array $starPowers
 * @property-read Collection|PlayerBrawler[]|array $playerBrawlers
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
        return $this->belongsToMany(
            related: Accessory::class,
            table: 'brawler_accessory',
            foreignPivotKey: 'brawler_id',
            relatedPivotKey: 'accessory_id',
        );
    }

    /**
     * Get the gears that current brawler can have.
     *
     * @return BelongsToMany
     */
    public function gears(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Gear::class,
            table: 'brawler_gear',
            foreignPivotKey: 'brawler_id',
            relatedPivotKey: 'gear_id',
        );
    }

    /**
     * Get the star powers that current brawler can have.
     *
     * @return BelongsToMany
     */
    public function starPowers(): BelongsToMany
    {
        return $this->belongsToMany(
            related: StarPower::class,
            table: 'brawler_star_power',
            foreignPivotKey: 'brawler_id',
            relatedPivotKey: 'star_power_id',
        );
    }

    /**
     * Get the player brawlers.
     *
     * @return HasMany
     */
    public function playerBrawlers(): HasMany
    {
        return $this->hasMany(PlayerBrawler::class, 'brawler_id', 'id');
    }
}
