<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\AccessoryDTO;
use App\Models\Accessory;
use App\Services\Repositories\Contracts\AccessoryRepositoryInterface;

final readonly class AccessoryRepository implements AccessoryRepositoryInterface
{
    public function findAccessory(array $searchCriteria): ?Accessory
    {
        $query = Accessory::query();

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

    public function createOrUpdateAccessoryFromDataArray(array $accessoryData): Accessory
    {
        // TODO: validation
        $validated = $accessoryData;

        return $this->createOrUpdateAccessoryFromValidatedArray(attributes: $validated);
    }

    public function createOrUpdateAccessoryFromDTO(AccessoryDTO $accessoryDTO): Accessory
    {
        $validated = [
            'ext_id' => $accessoryDTO->extId,
            'name'   => $accessoryDTO->name,
        ];

        return $this->createOrUpdateAccessoryFromValidatedArray(attributes: $validated);
    }

    private function createOrUpdateAccessoryFromValidatedArray(array $attributes): Accessory
    {
        $accessory = $this->findAccessory([
            'ext_id' => $attributes['ext_id'],
        ]);

        if ($accessory) {
            $accessory->update(attributes: $attributes);
        } else {
            $accessory = Accessory::query()->create(attributes: $attributes);
        }

        return $accessory;
    }
}
