<?php

declare(strict_types=1);

namespace App\Services\Repositories\Event;

use App\Models\EventRotationSlot;
use App\Services\Repositories\Contracts\Event\EventRotationSlotRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class EventRotationSlotRepository implements EventRotationSlotRepositoryInterface
{
    public function __construct() {}

    public function findEventRotationSlot(array $searchCriteria): ?EventRotationSlot
    {
        $query = EventRotationSlot::query();

        if (isset($searchCriteria['id'])) {
            $query->where('id', '=', $searchCriteria['id']);
        }

        if (isset($searchCriteria['position'])) {
            $query->where('position', '=', $searchCriteria['position']);
        }

        return $query->first();
    }

    public function createOrUpdateEventRotationSlot(int|string $slotPosition): EventRotationSlot
    {
        $slot = $this->findEventRotationSlot([
            'position' => $slotPosition,
        ]);
        $attributes = [
            'position' => $slotPosition,
        ];

        DB::transaction(function () use (&$slot, $attributes) {
            if ($slot) {
                $slot->update(attributes: $attributes);
            } else {
                $slot = EventRotationSlot::query()->create(attributes: $attributes);
            }
        });

        return $slot->refresh();
    }
}
