<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ext_id
 * @property string $name
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
    ];

    protected $casts = [
        'ext_id' => 'integer',
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
}
