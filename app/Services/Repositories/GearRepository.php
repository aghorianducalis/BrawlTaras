<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\GearDTO;
use App\Models\Gear;
use App\Services\Repositories\Contracts\GearRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class GearRepository implements GearRepositoryInterface
{
    public function findGear(array $searchCriteria): ?Gear
    {
        $query = Gear::query();

        if (isset($searchCriteria['id'])) {
            $query->where('id', '=', $searchCriteria['id']);
        }

        if (isset($searchCriteria['ext_id'])) {
            $query->where('ext_id', '=', $searchCriteria['ext_id']);
        }

        if (isset($searchCriteria['name'])) {
            $query->where('name', 'like', "%{$searchCriteria['name']}%");
        }

        return $query->first();
    }

    public function createOrUpdateGear(GearDTO $gearDTO): Gear
    {
        $gear = $this->findGear([
            'ext_id' => $gearDTO->extId,
        ]);
        $newData = [
            'ext_id' => $gearDTO->extId, // unnecessary since 'ext_id' remains unchanged
            'name' => $gearDTO->name,
        ];

        DB::transaction(function () use (&$gear, $newData) {
            if ($gear) {
                $gear->update($newData);
            } else {
                $gear = Gear::query()->create($newData);
            }
        });

        return $gear;
    }
}
