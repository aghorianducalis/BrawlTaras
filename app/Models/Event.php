<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $mode_id
 * @property string $map
 * @property int $ext_id
 * @property int $slot_id
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Mode $mode
 */
class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'mode_id',
        'map',
        'ext_id',
        'slot_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'mode_id'    => 'integer',
        'ext_id'     => 'integer',
        'slot_id'    => 'integer',
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    /**
     * Get the mode of the event.
     *
     * @return BelongsTo
     */
    public function mode(): BelongsTo
    {
        return $this->belongsTo(Mode::class, 'mode_id', 'id');
    }
}
