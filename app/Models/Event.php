<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $ext_id
 * @property int $map_id
 * @property int $mode_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read EventMap $map
 * @property-read EventMode $mode
 * @property-read Collection|EventRotation[]|array $eventRotations
 * @property-read Collection|EventModifier[]|array $modifiers todo
 */
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'ext_id',
        'map_id',
        'mode_id',
    ];

    protected $casts = [
        'ext_id'  => 'integer',
        'map_id'  => 'integer',
        'mode_id' => 'integer',
    ];

    /**
     * Get the event's map.
     *
     * @return BelongsTo
     */
    public function map(): BelongsTo
    {
        return $this->belongsTo(EventMap::class, 'map_id', 'id');
    }

    /**
     * Get the event's mode.
     *
     * @return BelongsTo
     */
    public function mode(): BelongsTo
    {
        return $this->belongsTo(EventMode::class, 'mode_id', 'id');
    }

    /**
     * Get the event's rotations.
     *
     * @return HasMany
     */
    public function rotations(): HasMany
    {
        return $this->hasMany(EventRotation::class, 'event_id', 'id');
    }
}
