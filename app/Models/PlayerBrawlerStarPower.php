<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Repositories\Contracts\StarPowerRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $player_brawler_id
 * @property int $brawler_star_power_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read StarPower|null $starPower
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
     * @return Attribute
     */
    protected function starPower(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => app(StarPowerRepositoryInterface::class)->findStarPower(['brawler_star_power_id' => $this->brawler_star_power_id]),
        )->shouldCache();
    }
}
