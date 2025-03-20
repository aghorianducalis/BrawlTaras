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

        if (isset($searchCriteria['name'])) {
            $query->where('name', 'like', "%{$searchCriteria['name']}%");
        }

        return $query->first();
    }

    public function createOrUpdateStarPowerFromDataArray(array $starPowerData): StarPower
    {
        // TODO: validation
        $validated = $starPowerData;

        return $this->createOrUpdateStarPowerFromValidatedArray(attributes: $validated);
    }

    public function createOrUpdateStarPowerFromDTO(StarPowerDTO $starPowerDTO): StarPower
    {
        $validated = [
            'ext_id' => $starPowerDTO->extId,
            'name'   => $starPowerDTO->name,
        ];

        return $this->createOrUpdateStarPowerFromValidatedArray(attributes: $validated);
    }

    private function createOrUpdateStarPowerFromValidatedArray(array $attributes): StarPower
    {
        $starPower = $this->findStarPower([
            'ext_id' => $attributes['ext_id'],
        ]);

        if ($starPower) {
            $starPower->update(attributes: $attributes);
        } else {
            $starPower = StarPower::query()->create(attributes: $attributes);
        }

        return $starPower;
    }
}
