<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\StarPowerFactory;
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
 * @property-read Collection|Brawler[]|array $brawlers all brawlers who may have the current star power.
 */
class StarPower extends Model
{
    /** @use HasFactory<StarPowerFactory> */
    use HasFactory;

    protected $table = 'star_powers';

    protected $fillable = [
        'ext_id',
        'name',
    ];

    protected $casts = [
        'ext_id' => 'integer',
    ];

    /**
     * Get all brawlers who may have the current star power.
     *
     * @return BelongsToMany
     */
    public function brawlers(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Brawler::class,
            table: 'brawler_star_power',
            foreignPivotKey: 'star_power_id',
            relatedPivotKey: 'brawler_id',
        );
    }
}
