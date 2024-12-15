<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\StarPowerDTO;
use App\Models\StarPower;
use App\Services\Repositories\Contracts\StarPowerRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class StarPowerRepository implements StarPowerRepositoryInterface
{
    public function findStarPower(array $searchCriteria): ?StarPower
    {
        $query = StarPower::query();

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

    public function createOrUpdateStarPower(StarPowerDTO $starPowerDTO, int $brawlerId): StarPower
    {
        $starPower = $this->findStarPower([
            'ext_id' => $starPowerDTO->extId,
            'brawler_id' => $brawlerId,
        ]);
        $newData = [
            'name' => $starPowerDTO->name,
            'ext_id' => $starPowerDTO->extId,
            'brawler_id' => $brawlerId,
        ];

        DB::transaction(function () use (&$starPower, $newData) {
            if ($starPower) {
                $starPower->update($newData);
            } else {
                $starPower = StarPower::query()->create($newData);
            }
        });

        return $starPower;
    }
}
