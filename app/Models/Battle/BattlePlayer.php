<?php

declare(strict_types=1);

namespace App\Models\Battle;

use App\Models\Player;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $player_id
 * @property int|null $battle_team_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Player $player
 * @property-read BattleTeam|null $battleTeam
 */
class BattlePlayer extends Model
{
    protected $table = 'battle_players';

    protected $fillable = [
        'player_id',
        'battle_team_id',
    ];

    protected $casts = [
        'player_id'      => 'integer',
        'battle_team_id' => 'integer',
    ];

    /**
     * Get the team of battle player.
     *
     * @return BelongsTo
     */
    public function battleTeam(): BelongsTo
    {
        return $this->belongsTo(
            related: BattleTeam::class,
            foreignKey: 'battle_team_id',
            ownerKey: 'id',
        );
    }
}
