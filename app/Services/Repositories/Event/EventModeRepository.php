<?php

declare(strict_types=1);

namespace App\Services\Repositories\Event;

use App\Models\EventMode;
use App\Services\Repositories\Contracts\Event\EventModeRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class EventModeRepository implements EventModeRepositoryInterface
{
    public function __construct() {}

    public function findEventMode(array $searchCriteria): ?EventMode
    {
        $query = EventMode::query();

        if (isset($searchCriteria['id'])) {
            $query->where('id', '=', $searchCriteria['id']);
        }

        if (isset($searchCriteria['name'])) {
            $query->where('name', '=', $searchCriteria['name']);
        }

        return $query->first();
    }

    public function createOrUpdateEventMode(string $name): EventMode
    {
        $mode = $this->findEventMode([
            'name' => $name,
        ]);
        $attributes = [
            'name' => $name,
        ];

        DB::transaction(function () use (&$mode, $attributes) {
            if ($mode) {
                $mode->update(attributes: $attributes);
            } else {
                $mode = EventMode::query()->create(attributes: $attributes);
            }
        });

        return $mode->refresh();
    }
}
