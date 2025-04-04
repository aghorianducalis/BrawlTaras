<?php

declare(strict_types=1);

namespace App\Models\Battle;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $mode
 * @property string $type
 * @property string|null $result
 * @property int|null $duration
 * @property int|null $trophy_change
 * @property int|null $rank
 * @property int $battle_id
// * @property int|null $star_player_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Battle $battle
 */
class BattleResult extends Model
{
    protected $table = 'battle_results';

    protected $fillable = [
        'mode',
        'type',
        'result',
        'duration',
        'trophy_change',
        'rank',
        'battle_id',
        'star_player_id',
    ];

    protected $casts = [
        'duration'       => 'integer',
        'trophy_change'  => 'integer',
        'rank'           => 'integer',
        'battle_id'      => 'integer',
        'star_player_id' => 'integer',
    ];

    /**
     * Get the battle result.
     *
     * @return BelongsTo
     */
    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class, 'battle_id', 'id');
    }
}
