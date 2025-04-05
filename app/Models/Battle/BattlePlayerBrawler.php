<?php

declare(strict_types=1);

namespace App\Models\Battle;

use App\Models\Brawler;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $power
 * @property int $trophies
 * @property int|null $trophy_change
 * @property int $brawler_id
 * @property int $battle_player_id
 * @property int $battle_id
 * @property int $battle_result_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Brawler $brawler
 * @property-read BattlePlayer $battlePlayer
 * @property-read Battle $battle
 * @property-read BattleResult $battleResult
 */
class BattlePlayerBrawler extends Model
{
    protected $table = 'battle_player_brawlers';

    protected $fillable = [
        'power',
        'trophies',
        'trophy_change',
        'brawler_id',
        'battle_player_id',
        'battle_id',
        'battle_result_id',
    ];

    protected $casts = [
        'power'            => 'integer',
        'trophies'         => 'integer',
        'trophy_change'    => 'integer',
        'brawler_id'       => 'integer',
        'battle_player_id' => 'integer',
        'battle_id'        => 'integer',
        'battle_result_id' => 'integer',
    ];

    public function brawler(): BelongsTo
    {
        return $this->belongsTo(
            related: Brawler::class,
            foreignKey: 'brawler_id',
            ownerKey: 'id',
        );
    }

    public function battlePlayer(): BelongsTo
    {
        return $this->belongsTo(
            related: BattlePlayer::class,
            foreignKey: 'battle_player_id',
            ownerKey: 'id',
        );
    }

    public function battle(): BelongsTo
    {
        return $this->belongsTo(
            related: Battle::class,
            foreignKey: 'battle_id',
            ownerKey: 'id',
        );
    }

    public function battleResult(): BelongsTo
    {
        return $this->belongsTo(
            related: BattleResult::class,
            foreignKey: 'battle_result_id',
            ownerKey: 'id',
        );
    }
}
