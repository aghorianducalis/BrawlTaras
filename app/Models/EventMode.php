<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EventModeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name is one of the [ soloShowdown, duoShowdown, heist, bounty, siege, gemGrab, brawlBall, bigGame, bossFight, roboRumble, takedown, loneStar, presentPlunder, hotZone, superCityRampage, knockout, volleyBrawl, basketBrawl, holdTheTrophy, trophyThieves, duels, wipeout, payload, botDrop, hunters, lastStand, snowtelThieves, pumpkinPlunder, trophyEscape, wipeout5V5, knockout5V5, gemGrab5V5, brawlBall5V5, godzillaCitySmash, paintBrawl, trioShowdown, zombiePlunder, jellyfishing, unknown ]
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|Event[]|array $events
 */
class EventMode extends Model
{
    /** @use HasFactory<EventModeFactory> */
    use HasFactory;

    protected $table = 'event_modes';

    protected $fillable = [
        'name',
    ];

    /**
     * Get the events of current mode.
     *
     * @return HasMany
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'mode_id', 'id');
    }
}
