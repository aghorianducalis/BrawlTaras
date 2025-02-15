<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Repositories\Contracts\AccessoryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $player_brawler_id
 * @property int $brawler_accessory_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Accessory|null $accessory
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
     * @return Attribute
     */
    protected function accessory(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => app(AccessoryRepositoryInterface::class)->findAccessory(['brawler_accessory_id' => $this->brawler_accessory_id]),
        )->shouldCache();
    }
}
