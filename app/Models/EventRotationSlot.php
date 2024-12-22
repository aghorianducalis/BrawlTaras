<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EventRotationSlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|EventRotation[]|array $eventRotations
 */
class EventRotationSlot extends Model
{
    /** @use HasFactory<EventRotationSlotFactory> */
    use HasFactory;

    protected $table = 'event_rotation_slots';

    protected $fillable = [
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Get the event rotations for the slot.
     *
     * @return HasMany
     */
    public function eventRotations(): HasMany
    {
        return $this->hasMany(EventRotation::class, 'slot_id', 'id');
    }
}
