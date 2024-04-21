<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property-read Collection|Accessory[]|array $accessories
 * @property-read Collection|Gear[]|array $gears
 * @property-read Collection|StarPower[]|array $starPowers
 */
class PlayerBrawler extends Model
{
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
     * Get the related player entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'id');
    }

    /**
     * Get the related brawler entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brawler(): BelongsTo
    {
        return $this->belongsTo(Brawler::class, 'brawler_id', 'id');
    }

    /**
     * Get the accessories for the brawler.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accessories(): HasMany
    {
        // todo player_brawler_accessory brawler_accessory_id
        return $this->hasMany(Accessory::class, 'brawler_accessory_id', 'id');
    }

    /**
     * Get the accessories for the brawler.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function gears(): HasMany
    {
        // todo player_brawler_gear gear_id
        return $this->hasMany(Gear::class, 'gear_id', 'id');
    }

    /**
     * Get the star powers for the brawler.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function starPowers(): HasMany
    {
        // todo player_brawler_star_power brawler_star_power_id
        return $this->hasMany(StarPower::class, 'brawler_star_power_id', 'id');
    }
}
