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

    public function createOrUpdateBrawler(BrawlerDTO $brawlerDTO): Brawler
    {
        $brawler = $this->findBrawler([
            'ext_id' => $brawlerDTO->extId,
        ]);
        $newData = [
            'name' => $brawlerDTO->name,
            'ext_id' => $brawlerDTO->extId,
        ];

        DB::transaction(function () use (&$brawler, $brawlerDTO, $newData) {
            if ($brawler) {
                $brawler->update($newData);
            } else {
                $brawler = Brawler::query()->create($newData);
            }

            $this->syncBrawlerAccessories($brawler, $brawlerDTO->accessories);
            $this->syncBrawlerStarPowers($brawler, $brawlerDTO->starPowers);
            // todo gears?
        });

        return $brawler->refresh();
    }

    /**
     * todo this method: 1) either must be removed; 2) or covered with tests
     * @see self::createOrUpdateBrawler()
     *
     * @param BrawlerDTO[] $brawlerDTOs
     * @return Brawler[]
     */
    public function createOrUpdateBrawlers(array $brawlerDTOs): array
    {
        $brawlers = [];

        foreach ($brawlerDTOs as $brawlerDTO) {
            $brawlers[] = $this->createOrUpdateBrawler($brawlerDTO);
        }

        return $brawlers;
    }

    /**
     * @param Brawler $brawler
     * @param AccessoryDTO[] $accessoryDTOs
     * @return void
     */
    private function syncBrawlerAccessories(Brawler $brawler, array $accessoryDTOs): void
    {
        $accessoryRepository = AccessoryRepository::getInstance();

        $accessories = [];

        foreach ($accessoryDTOs as $accessoryDTO) {
            $accessories[] = $accessoryRepository->createOrUpdateAccessory($accessoryDTO, $brawler->id);
        }

        // delete old accessories
        $brawler->accessories()->whereNotIn('id', collect($accessories)->pluck('id')->toArray())->delete();
    }

    /**
     * @param Brawler $brawler
     * @param StarPowerDTO[] $starPowerDTOs
     * @return void
     */
    private function syncBrawlerStarPowers(Brawler $brawler, array $starPowerDTOs): void
    {
        $starPowerRepository = StarPowerRepository::getInstance();

        $starPowers = [];

        foreach ($starPowerDTOs as $starPowerDTO) {
            $starPowers[] = $starPowerRepository->createOrUpdateStarPower($starPowerDTO, $brawler->id);
        }

        // delete old star powers
        $brawler->starPowers()->whereNotIn('id', collect($starPowers)->pluck('id')->toArray())->delete();
    }

    public static function getInstance(): self
    {
        return app(BrawlerRepository::class);
    }
}
