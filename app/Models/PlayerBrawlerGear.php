<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property int $id
 * @property int $player_brawler_id
 * @property int $brawler_gear_id
 * @property int $level
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Gear $gear
 */
class PlayerBrawlerGear extends Model
{
    protected $table = 'player_brawler_gear';

    protected $fillable = [
        'player_brawler_id',
        'brawler_gear_id',
        'level',
    ];

    protected $casts = [
        'player_brawler_id' => 'integer',
        'brawler_gear_id'   => 'integer',
        'level'             => 'integer',
    ];

    /**
     * Get the related gear.
     *
     * @return HasOneThrough
     */
    public function gear(): HasOneThrough
    {
        return $this->hasOneThrough(
            Gear::class,         // Final related model
            BrawlerGear::class,  // Intermediate model
            'id',                // Foreign key on BrawlerGear (pivot ID)
            'id',              // Foreign key on Gear
            'brawler_gear_id',   // Local key on PlayerBrawlerGear
            'gear_id',     // Key on BrawlerGear pointing to Gear
        );
    }
}
