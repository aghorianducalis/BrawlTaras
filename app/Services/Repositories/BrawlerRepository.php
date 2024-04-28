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

        /** @var Brawler $brawler */
        $brawler = $query->first();

        return $brawler;
    }

    public function createOrUpdateBrawler(BrawlerDTO $brawlerDTO): Brawler
    {
        $brawler = $this->findBrawler([
            'ext_id' => $brawlerDTO->extId,
        ]);
        $newData = [
            'name'       => $brawlerDTO->name,
            'ext_id'     => $brawlerDTO->extId,
        ];

        DB::transaction(function () use ($brawler, $brawlerDTO, $newData) {
            if ($brawler) {
                $brawler->update($newData);
            } else {
                $brawler = Brawler::query()->create($newData);
            }

            $this->syncBrawlerAccessories($brawler, $brawlerDTO->getAccessories());
            $this->syncBrawlerStarPowers($brawler, $brawlerDTO->getStarPowers());
            // todo gears?
        });

        return $brawler;
    }

    /**
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
     * @return Brawler
     */
    private function syncBrawlerAccessories(Brawler $brawler, array $accessoryDTOs): Brawler
    {
        $accessoryRepository = AccessoryRepository::getInstance();

        $accessories = [];

        foreach ($accessoryDTOs as $accessoryDTO) {
            $accessories[] = $accessoryRepository->createOrUpdateAccessory($accessoryDTO, $brawler->id);
        }

        // delete old accessories
        $brawler->accessories()->whereNotIn('id', collect($accessories)->pluck('id')->toArray())->delete();

        $brawler->refresh();

        return $brawler;
    }

    /**
     * @param Brawler $brawler
     * @param StarPowerDTO[] $starPowerDTOs
     * @return Brawler
     */
    private function syncBrawlerStarPowers(Brawler $brawler, array $starPowerDTOs): Brawler
    {
        $starPowerRepository = StarPowerRepository::getInstance();

        $starPowers = [];

        foreach ($starPowerDTOs as $starPowerDTO) {
            $starPowers[] = $starPowerRepository->createOrUpdateStarPower($starPowerDTO, $brawler->id);
        }

        // delete old star powers
        $brawler->starPowers()->whereNotIn('id', collect($starPowers)->pluck('id')->toArray())->delete();

        $brawler->refresh();

        return $brawler;
    }

    public static function getInstance(): self
    {
        return app(BrawlerRepository::class);
    }
}
