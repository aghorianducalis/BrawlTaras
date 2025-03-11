<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property int $id
 * @property int $player_brawler_id
 * @property int $brawler_star_power_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read StarPower $starPower
 */
class PlayerBrawlerStarPower extends Model
{
    protected $table = 'player_brawler_star_power';

    protected $fillable = [
        'player_brawler_id',
        'brawler_star_power_id',
    ];

    protected $casts = [
        'player_brawler_id'     => 'integer',
        'brawler_star_power_id' => 'integer',
    ];

    /**
     * Get the related star power.
     *
     * @return HasOneThrough
     */
    public function starPower(): HasOneThrough
    {
        return $this->hasOneThrough(
            StarPower::class,         // Final related model
            BrawlerStarPower::class,  // Intermediate model
            'id',                     // Foreign key on BrawlerStarPower (pivot ID)
            'id',                   // Foreign key on StarPower
            'brawler_star_power_id',  // Local key on PlayerBrawlerStarPower
            'star_power_id',    // Key on BrawlerStarPower pointing to StarPower
        );
    }
}
