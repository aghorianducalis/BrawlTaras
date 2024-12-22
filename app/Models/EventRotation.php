<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EventRotationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * a.k.a. ScheduledEvent.
 *
 * @property int $id
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property int $event_id
 * @property int $slot_id unique identifier for position of the event rotation
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Event $event
 * @property-read EventRotationSlot $slot
 */
class EventRotation extends Model
{
    /** @use HasFactory<EventRotationFactory> */
    use HasFactory;

    protected $table = 'event_rotations';

    protected $fillable = [
        'start_time',
        'end_time',
        'event_id',
        'slot_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'event_id'   => 'integer',
        'slot_id'    => 'integer',
    ];

    /**
     * Get the actual event.
     *
     * @return BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    /**
     * Get the slot (position).
     *
     * @return BelongsTo
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(EventRotationSlot::class, 'slot_id', 'id');
    }
}
