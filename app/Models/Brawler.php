<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\BrawlerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $ext_id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|Accessory[]|array $accessories
 * @property-read Collection|StarPower[]|array $starPowers
 * @property-read Collection|PlayerBrawler[]|array $playerBrawlers
 */
class Brawler extends Model
{
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
     * Get the accessories for the brawler.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accessories(): HasMany
    {
        return $this->hasMany(Accessory::class, 'brawler_id', 'id');
    }

    /**
     * Get the star powers for the brawler.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function starPowers(): HasMany
    {
        return $this->hasMany(StarPower::class, 'brawler_id', 'id');
    }

    /**
     * Get the player brawlers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function playerBrawlers(): HasMany
    {
        return $this->hasMany(PlayerBrawler::class, 'brawler_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return BrawlerFactory
     */
    protected static function newFactory(): BrawlerFactory
    {
        return BrawlerFactory::new();
    }
}
