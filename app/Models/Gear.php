<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\GearFactory;
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
 * @property-read Collection|Brawler[]|array $brawlers all brawlers who may have the current gear.
 * @property-read BrawlerGear|null $brawler_gear
 */
class Gear extends Model
{
    /** @use HasFactory<GearFactory> */
    use HasFactory;

    protected $table = 'gears';

    protected $fillable = [
        'ext_id',
        'name',
    ];

    protected $casts = [
        'ext_id' => 'integer',
    ];

    /**
     * Get all brawlers who may have the current gear.
     *
     * @return BelongsToMany
     */
    public function brawlers(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                related: Brawler::class,
                table: 'brawler_gear',
                foreignPivotKey: 'gear_id',
                relatedPivotKey: 'brawler_id',
            )
            ->using(BrawlerGear::class)
            ->withPivot([
                'id',
                'brawler_id',
                'gear_id',
            ])
            ->as('brawler_gear');
    }
}
