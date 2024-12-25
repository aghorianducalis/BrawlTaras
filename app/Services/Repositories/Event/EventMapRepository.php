<?php

declare(strict_types=1);

namespace App\Services\Repositories\Event;

use App\Models\EventMap;
use App\Services\Repositories\Contracts\Event\EventMapRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class EventMapRepository implements EventMapRepositoryInterface
{
    public function __construct() {}

    public function findEventMap(array $searchCriteria): ?EventMap
    {
        $query = EventMap::query();

        if (isset($searchCriteria['id'])) {
            $query->where('id', '=', $searchCriteria['id']);
        }

        if (isset($searchCriteria['name'])) {
            $query->where('name', '=', $searchCriteria['name']);
        }

        return $query->first();
    }

    public function createOrUpdateEventMap(string $name): EventMap
    {
        $map = $this->findEventMap([
            'name' => $name,
        ]);
        $attributes = [
            'name' => $name,
        ];

        DB::transaction(function () use (&$map, $attributes) {
            if ($map) {
                $map->update(attributes: $attributes);
            } else {
                $map = EventMap::query()->create(attributes: $attributes);
            }
        });

        return $map->refresh();
    }
}
