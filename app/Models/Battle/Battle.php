<?php

declare(strict_types=1);

namespace App\Models\Battle;

use App\Models\Event;
use Carbon\Carbon;
use Database\Factories\Battle\BattleFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property Carbon $battle_time
 * @property int $event_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Event $event
 * @property-read BattleResult $battleResult
 */
class Battle extends Model
{
    /** @use HasFactory<BattleFactory> */
    use HasFactory;

    protected $table = 'battles';

    protected $fillable = [
        'battle_time',
        'event_id',
    ];

    protected $casts = [
        'battle_time' => 'datetime',
        'event_id'    => 'integer',
    ];


    /**
     * Interact with the battle time.
     *
     * @return Attribute
     */
    protected function battleTime(): Attribute
    {
        return Attribute::make(
            set: fn($value) => is_string($value)
                ? Carbon::createFromFormat('Ymd\THis.v\Z', $value)
                : $value,
        );
    }

    /**
     * Get the battle event.
     *
     * @return BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(
            related: Event::class,
            foreignKey: 'event_id',
            ownerKey: 'id'
        );
    }

    /**
     * Get the battle result.
     *
     * @return HasOne
     */
    public function battleResult(): HasOne
    {
        return $this->hasOne(
            related: BattleResult::class,
            foreignKey: 'battle_id',
            localKey: 'id'
        );
    }
}
