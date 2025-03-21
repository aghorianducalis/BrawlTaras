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
use Illuminate\Validation\ValidationException;
use JsonException;

final readonly class BrawlerRepository implements BrawlerRepositoryInterface
{
    public const BRAWLER_RELATIONS = [
        'accessories',
        'gears',
        'starPowers',
        'players',
    ];

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

    public function createOrUpdateBrawlerFromDataArray(array $brawlerData): Brawler
    {
        // TODO: validation
        $validated = $brawlerData;

        return $this->createOrUpdateBrawlerFromValidatedArray(attributes: $validated);
    }

    public function createOrUpdateBrawlerFromDTO(BrawlerDTO $brawlerDTO): Brawler
    {
        $validated = [
            'ext_id' => $brawlerDTO->extId,
            'name'   => $brawlerDTO->name,
        ];

        return $this->createOrUpdateBrawlerFromValidatedArray(attributes: $validated);
    }

    /**
     * @throws ValidationException
     * @throws JsonException
     */
    public function createOrUpdateBrawlerFromDTOAndSyncRelations(BrawlerDTO $brawlerDTO): Brawler
    {
        $brawler = null;

        DB::transaction(function () use (&$brawler, $brawlerDTO) {
            $brawler = $this->createOrUpdateBrawlerFromDTO($brawlerDTO);

            $this->syncBrawlerRelations(
                brawler: $brawler,
                brawlerDTO: $brawlerDTO,
            );
        });

        if (!$brawler) {
            // todo try/catch here and continue with actual brawler (not null)
            throw ValidationException::withMessages(["Brawler with name $brawlerDTO->name has not been created from DTO: {$brawlerDTO->toJson()}."]);
        }

        $brawler->refresh();
        $brawler->load(self::BRAWLER_RELATIONS);

        return $brawler;
    }

    public function createOrUpdateBrawlersFromDTOs(array $brawlerDTOs): array
    {
        $brawlers = [];

        DB::transaction(function () use (&$brawlers, $brawlerDTOs) {
            // todo calls can lead to N+1 query issues. Consider bulk inserts/updates if the data size is significant.
            $brawlers = array_map(fn (BrawlerDTO $dto) => $this->createOrUpdateBrawlerFromDTO($dto), $brawlerDTOs);
        });

        return $brawlers;
    }

    public function createOrUpdateBrawlersFromDTOsAndSyncRelations(array $brawlerDTOs): array
    {
        $brawlers = [];

        DB::transaction(function () use (&$brawlers, $brawlerDTOs) {
            foreach ($brawlerDTOs as $brawlerDTO) {
                $brawler = $this->createOrUpdateBrawlerFromDTOAndSyncRelations(brawlerDTO: $brawlerDTO);
                $brawlers[] = $brawler;
            }
        });

        return $brawlers;
    }

    private function createOrUpdateBrawlerFromValidatedArray(array $attributes): Brawler
    {
        $brawler = $this->findBrawler([
            'ext_id' => $attributes['ext_id'],
        ]);

        if ($brawler) {
            $brawler->update(attributes: $attributes);
        } else {
            $brawler = Brawler::query()->create(attributes: $attributes);
        }

        return $brawler;
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
    private function syncBrawlerRelations(Brawler $brawler, BrawlerDTO $brawlerDTO): void
    {
        $accessoryIds = collect($brawlerDTO->accessories)
            ->map(fn (AccessoryDTO $dto) => $this->accessoryRepository->createOrUpdateAccessoryFromDTO($dto))
            ->pluck('id')
            ->toArray();

        // todo check logic

        // remove only those rows that are not used in `player_brawler_accessory`.`brawler_accessory_id`
//        $brawler->accessories()->whereNotIn('id', $accessoryIds)->detach();
        $brawler->accessories()->attach($accessoryIds);

        $starPowerIds = collect($brawlerDTO->starPowers)
            ->map(fn (StarPowerDTO $dto) => $this->starPowerRepository->createOrUpdateStarPowerFromDTO($dto))
            ->pluck('id')
            ->toArray();

        // remove only those rows that are not used in `player_brawler_star_power`.`brawler_star_power_id`
//        $brawler->starPowers()->whereNotIn('id', $starPowerIds)->detach();
        $brawler->starPowers()->attach($starPowerIds);
    }
}
