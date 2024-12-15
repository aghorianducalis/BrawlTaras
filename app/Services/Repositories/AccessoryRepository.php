<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\AccessoryDTO;
use App\Models\Accessory;
use App\Services\Repositories\Contracts\AccessoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

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

        if (isset($searchCriteria['brawler_id'])) {
            $query->where('brawler_id', '=', $searchCriteria['brawler_id']);
        }

        if (isset($searchCriteria['name'])) {
            $query->where('name', 'like', "%{$searchCriteria['name']}%");
        }

        return $query->first();
    }

    public function createOrUpdateAccessory(AccessoryDTO $accessoryDTO, int $brawlerId): Accessory
    {
        $accessory = $this->findAccessory([
            'ext_id' => $accessoryDTO->extId,
            'brawler_id' => $brawlerId,
        ]);
        $newData = [
            'name' => $accessoryDTO->name,
            'ext_id' => $accessoryDTO->extId,
            'brawler_id' => $brawlerId,
        ];

        DB::transaction(function () use (&$accessory, $newData) {
            if ($accessory) {
                $accessory->update($newData);
            } else {
                $accessory = Accessory::query()->create($newData);
            }
        });

        return $accessory;
    }
}
