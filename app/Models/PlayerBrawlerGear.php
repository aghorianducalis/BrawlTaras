<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Repositories\Contracts\GearRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $player_brawler_id
 * @property int $brawler_gear_id
 * @property int $level
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Gear|null $gear
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
     * @return Attribute
     */
    protected function gear(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => app(GearRepositoryInterface::class)->findGear(['brawler_gear_id' => $this->brawler_gear_id]),
        )->shouldCache();
    }
}
