<?php

declare(strict_types=1);

namespace App\Models\Battle;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class BattleTeam extends Model
{
    protected $table = 'battle_teams';
}
