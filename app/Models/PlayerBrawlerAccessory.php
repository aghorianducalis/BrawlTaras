<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $player_brawler_id
 * @property int $brawler_accessory_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Accessory $accessory
 */
class PlayerBrawlerAccessory extends Pivot
{
    protected $table = 'player_brawler_accessory';

    protected $fillable = [
        'player_brawler_id',
        'brawler_accessory_id',
    ];

    protected $casts = [
        'player_brawler_id'    => 'integer',
        'brawler_accessory_id' => 'integer',
    ];

    /**
     * Get the related accessory.
     *
     * @return HasOneThrough
     */
    public function accessory(): HasOneThrough
    {
        return $this->hasOneThrough(
            Accessory::class,         // Final related model
            BrawlerAccessory::class,  // Intermediate model
            'id',                     // Foreign key on BrawlerAccessory (pivot ID)
            'id',                   // Foreign key on Accessory
            'brawler_accessory_id',   // Local key on PlayerBrawlerAccessory
            'accessory_id',     // Key on BrawlerAccessory pointing to Accessory
        );
    }
}
