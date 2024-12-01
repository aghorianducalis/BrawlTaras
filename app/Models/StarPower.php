<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\StarPowerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ext_id
 * @property string $name
 * @property int $brawler_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Brawler $brawler
 */
class StarPower extends Model
{
    use HasFactory;

    protected $table = 'star_powers';

    protected $fillable = [
        'ext_id',
        'name',
        'brawler_id',
    ];

    protected $casts = [
        'ext_id'     => 'integer',
        'brawler_id' => 'integer',
    ];

    /**
     * Get the brawler of a star power.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brawler(): BelongsTo
    {
        return $this->belongsTo(Brawler::class, 'brawler_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return StarPowerFactory
     */
    protected static function newFactory(): StarPowerFactory
    {
        return StarPowerFactory::new();
    }
}
