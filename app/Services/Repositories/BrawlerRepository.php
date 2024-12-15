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

    public function findBrawler(array $searchCriteria): ?Brawler
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

        return $query->first();
    }

    public function createOrUpdateBrawler(BrawlerDTO $brawlerDTO): Brawler
    {
        $brawler = $this->findBrawler([
            'ext_id' => $brawlerDTO->extId,
        ]);
        $attributes = [
            'name' => $brawlerDTO->name,
            'ext_id' => $brawlerDTO->extId,
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
     * todo Ensure syncRelations uses optimized queries, especially for many-to-many relationships.
     * todo Lazy loading could cause performance bottlenecks here.
     *
     * Sync Brawler accessories and star powers.
     *
     * @param Brawler $brawler
     * @param BrawlerDTO $brawlerDTO
     * @return void
     */
    private function syncRelations(Brawler $brawler, BrawlerDTO $brawlerDTO): void
    {
        $this->syncRelation(
            $brawler,
            'accessories',
            $brawlerDTO->accessories,
            fn (AccessoryDTO $dto) => $this->accessoryRepository->createOrUpdateAccessory($dto, $brawler->id)
        );

        $this->syncRelation(
            $brawler,
            'starPowers',
            $brawlerDTO->starPowers,
            fn (StarPowerDTO $dto) => $this->starPowerRepository->createOrUpdateStarPower($dto, $brawler->id)
        );
    }

    /**
     * Synchronize a Brawler's related entities.
     *
     * @template T
     * @param Brawler $brawler
     * @param string $relation
     * @param T[] $items
     * @param callable(T): mixed $createOrUpdateCallback
     * @return void
     */
    private function syncRelation(Brawler $brawler, string $relation, array $items, callable $createOrUpdateCallback): void
    {
        $newEntities = collect($items)
            ->map(fn ($dto) => $createOrUpdateCallback($dto))
            ->pluck('id')
            ->toArray();

        $brawler->{$relation}()->whereNotIn('id', $newEntities)->delete();
    }
}
