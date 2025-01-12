<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\AccessoryDTO;
use App\API\DTO\Response\BrawlerDTO;
use App\API\DTO\Response\StarPowerDTO;
use App\Models\Brawler;
use App\Services\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Services\Repositories\Contracts\BrawlerRepositoryInterface;
use App\Services\Repositories\Contracts\StarPowerRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class BrawlerRepository implements BrawlerRepositoryInterface
{
    public function __construct(
        private AccessoryRepositoryInterface $accessoryRepository,
        private StarPowerRepositoryInterface $starPowerRepository
    ) {}

    public function findBrawler(array $searchCriteria, mixed $relations = null): ?Brawler
    {
        // nice todo Optimize the findBrawler query to minimize redundant DB calls when processing multiple Brawlers.
        $query = Brawler::query();

        if (isset($searchCriteria['id'])) {
            $query->where('id', '=', $searchCriteria['id']);
        }

        if (isset($searchCriteria['ext_id'])) {
            $query->where('ext_id', '=', $searchCriteria['ext_id']);
        }

        if (isset($searchCriteria['name'])) {
            $query->where('name', 'like', "%{$searchCriteria['name']}%");
        }

        if ($relations) {
            $query->with($relations);
        }

        return $query->first();
    }

    public function createOrUpdateBrawler(BrawlerDTO $brawlerDTO): Brawler
    {
        $brawler = $this->findBrawler([
            'ext_id' => $brawlerDTO->extId,
        ]);
        $attributes = [
            'ext_id' => $brawlerDTO->extId,
            'name' => $brawlerDTO->name,
        ];

        DB::transaction(function () use (&$brawler, $brawlerDTO, $attributes) {
            if ($brawler) {
                $brawler->update(attributes: $attributes);
            } else {
                $brawler = Brawler::query()->create(attributes: $attributes);
            }

            $this->syncRelations($brawler, $brawlerDTO);
        });

        return $brawler->refresh();
    }

    public function createOrUpdateBrawlers(array $brawlerDTOs): array
    {
        // todo calls can lead to N+1 query issues. Consider bulk inserts/updates if the data size is significant.
        return array_map(fn (BrawlerDTO $dto) => $this->createOrUpdateBrawler($dto), $brawlerDTOs);
    }

    /**
     * Synchronize a Brawler's related entities: accessories, gears and star powers.
     * todo Ensure "sync relations" uses optimized queries, especially for many-to-many relationships.
     * NOTE: Lazy loading could cause performance bottlenecks here.
     *
     * @param Brawler $brawler
     * @param BrawlerDTO $brawlerDTO
     * @return void
     */
    private function syncRelations(Brawler $brawler, BrawlerDTO $brawlerDTO): void
    {
        $accessoryIds = collect($brawlerDTO->accessories)
            ->map(fn (AccessoryDTO $dto) => $this->accessoryRepository->createOrUpdateAccessory($dto))
            ->pluck('id')
            ->toArray();

        // todo check logic
        $brawler->accessories()->whereNotIn('id', $accessoryIds)->detach();
        $brawler->accessories()->attach($accessoryIds);

        $starPowerIds = collect($brawlerDTO->starPowers)
            ->map(fn (StarPowerDTO $dto) => $this->starPowerRepository->createOrUpdateStarPower($dto))
            ->pluck('id')
            ->toArray();

        // todo check logic
        $brawler->starPowers()->whereNotIn('id', $starPowerIds)->detach();
        $brawler->starPowers()->attach($starPowerIds);
    }
}
