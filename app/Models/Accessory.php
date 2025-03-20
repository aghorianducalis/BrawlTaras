<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\AccessoryFactory;
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
 * @property-read Collection|Brawler[]|array $brawlers all brawlers who may have the current accessory.
 * @property-read BrawlerAccessory|null $brawler_accessory
 */
class Accessory extends Model
{
    /** @use HasFactory<AccessoryFactory> */
    use HasFactory;

    protected $table = 'accessories';

    protected $fillable = [
        'ext_id',
        'name',
    ];

    protected $casts = [
        'ext_id' => 'integer',
    ];

    /**
     * Get all brawlers who may have the current accessory.
     *
     * @return BelongsToMany
     */
    public function brawlers(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                related: Brawler::class,
                table: 'brawler_accessory',
                foreignPivotKey: 'accessory_id',
                relatedPivotKey: 'brawler_id',
            )
            ->using(BrawlerAccessory::class)
            ->withPivot([
                'id',
                'brawler_id',
                'accessory_id',
            ])
            ->as('brawler_accessory');
    }
}
