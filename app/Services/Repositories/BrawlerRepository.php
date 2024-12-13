<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\AccessoryDTO;
use App\API\DTO\Response\BrawlerDTO;
use App\API\DTO\Response\StarPowerDTO;
use App\Models\Brawler;
use Illuminate\Support\Facades\DB;

final readonly class BrawlerRepository
{
    public function __construct(
        private AccessoryRepository $accessoryRepository,
        private StarPowerRepository $starPowerRepository
    ) {}

    /**
     * Find a Brawler based on search criteria.
     *
     * @param array<string, mixed> $searchCriteria
     * @return Brawler|null
     */
    public function findBrawler(array $searchCriteria): ?Brawler
    {
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

    /**
     * Create or update a Brawler and sync related entities.
     *
     * @param BrawlerDTO $brawlerDTO
     * @return Brawler
     */
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

    /**
     * Bulk create or update Brawler.
     *
     * @param BrawlerDTO[] $brawlerDTOs
     * @return Brawler[]
     */
    public function createOrUpdateBrawlers(array $brawlerDTOs): array
    {
        return array_map(fn (BrawlerDTO $dto) => $this->createOrUpdateBrawler($dto), $brawlerDTOs);
    }

    /**
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
