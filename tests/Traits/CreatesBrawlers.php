<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\AccessoryDTO;
use App\API\DTO\Response\BrawlerDTO as BrawlerResponseDTO;
use App\API\DTO\Response\StarPowerDTO;
use App\Models\Accessory;
use App\Models\Brawler;
use App\Models\StarPower;

trait CreatesBrawlers
{
    /**
     * Create a brawler with associated accessories and star powers.
     *
     * @param int $accessoryCount
     * @param int $starPowerCount
     * @return Brawler
     */
    public function createBrawlerWithRelations(int $accessoryCount = 2, int $starPowerCount = 2): Brawler
    {
        return Brawler::factory()
            ->has(Accessory::factory()->count($accessoryCount))
            ->has(StarPower::factory()->count($starPowerCount))
            ->create();
    }

    public function assertBrawlerModelMatchesDTO(Brawler $brawler, BrawlerResponseDTO $brawlerDTO): void
    {
        $this->assertSame($brawler->ext_id, $brawlerDTO->extId);
        $this->assertSame($brawler->name, $brawlerDTO->name);

        $this->assertEquals(
            $brawler->accessories->toArray(),
            collect($brawlerDTO->accessories)->transform(fn(AccessoryDTO $accessory) => [
                'id' => $accessory->extId,
                'name' => $accessory->name,
            ])->toArray()
        );
        $this->assertEquals(
            $brawler->starPowers->toArray(),
            collect($brawlerDTO->starPowers)->transform(fn(StarPowerDTO $starPower) => [
                'id' => $starPower->extId,
                'name' => $starPower->name,
            ])->toArray()
        );
    }
}
