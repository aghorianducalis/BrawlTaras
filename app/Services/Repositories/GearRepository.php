<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\GearDTO;
use App\Models\Gear;
use App\Services\Repositories\Contracts\GearRepositoryInterface;

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

    public function createOrUpdateGearFromDataArray(array $gearData): Gear
    {
        // TODO: validation
        $validated = $gearData;

        return $this->createOrUpdateGearFromValidatedArray(attributes: $validated);
    }

    public function createOrUpdateGearFromDTO(GearDTO $gearDTO): Gear
    {
        $validated = [
            'ext_id' => $gearDTO->extId,
            'name'   => $gearDTO->name,
        ];

        return $this->createOrUpdateGearFromValidatedArray(attributes: $validated);
    }

    private function createOrUpdateGearFromValidatedArray(array $attributes): Gear
    {
        $gear = $this->findGear([
            'ext_id' => $attributes['ext_id'],
        ]);

        if ($gear) {
            $gear->update(attributes: $attributes);
        } else {
            $gear = Gear::query()->create(attributes: $attributes);
        }

        return $gear;
    }
}
