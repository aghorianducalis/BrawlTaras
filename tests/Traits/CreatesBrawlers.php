<?php

declare(strict_types=1);

namespace Tests\Traits;

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
        /** @var Brawler $brawler */
        $brawler = Brawler::factory()
            ->has(Accessory::factory()->count($accessoryCount))
            ->has(StarPower::factory()->count($starPowerCount))
            ->create();

        $brawler->load(['accessories', 'starPowers']);

        return $brawler;
    }
}
