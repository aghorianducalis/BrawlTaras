<?php

declare(strict_types=1);

namespace App\Services\Repositories\Event;

use App\Models\EventModifier;
use App\Services\Repositories\Contracts\Event\EventModifierRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class EventModifierRepository implements EventModifierRepositoryInterface
{
    public function __construct() {}

    public function findEventModifier(array $searchCriteria): ?EventModifier
    {
        $query = EventModifier::query();

        if (isset($searchCriteria['id'])) {
            $query->where('id', '=', $searchCriteria['id']);
        }

        if (isset($searchCriteria['name'])) {
            $query->where('name', '=', $searchCriteria['name']);
        }

        return $query->first();
    }

    public function createOrUpdateEventModifier(string $name): EventModifier
    {
        $modifier = $this->findEventModifier([
            'name' => $name,
        ]);
        $attributes = [
            'name' => $name,
        ];

        DB::transaction(function () use (&$modifier, $attributes) {
            if ($modifier) {
                $modifier->update(attributes: $attributes);
            } else {
                $modifier = EventModifier::query()->create(attributes: $attributes);
            }
        });

        return $modifier->refresh();
    }
}
