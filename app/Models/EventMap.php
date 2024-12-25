<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EventMapFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|Event[]|array $events
 */
class EventMap extends Model
{
    /** @use HasFactory<EventMapFactory> */
    use HasFactory;

    protected $table = 'event_maps';

    protected $fillable = [
        'name',
    ];

    /**
     * Get the events of current map.
     *
     * @return HasMany
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'map_id', 'id');
    }
}
