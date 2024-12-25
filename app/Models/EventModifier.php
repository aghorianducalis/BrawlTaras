<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EventModifierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name is one of the [ angryRobo, ... ]
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|Event[]|array $events
 */
class EventModifier extends Model
{
    /** @use HasFactory<EventModifierFactory> */
    use HasFactory;

    protected $table = 'event_modifiers';

    protected $fillable = [
        'name',
    ];

    /**
     * The events that have the modifier.
     *
     * @return BelongsToMany
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Event::class,
            table: 'event_modifier_event',
            foreignPivotKey: 'modifier_id',
            relatedPivotKey: 'event_id',
        );
    }
}
