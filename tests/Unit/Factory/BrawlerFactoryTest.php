<?php

declare(strict_types=1);

namespace Tests\Unit\Factory;

use App\Models\Accessory;
use App\Models\Brawler;
use App\Models\StarPower;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesBrawlers;

class BrawlerFactoryTest extends TestCase
{
    use CreatesBrawlers;
    use RefreshDatabase;

    public function test_brawler_factory(): void
    {
        $brawler = $this->createBrawlerModelWithRelations(2, 2);

        $this->assertTrue($brawler->exists);
        $this->assertIsNumeric($brawler->id);
        $this->assertNotEmpty($brawler->name);
        $this->assertCount(2, $brawler->accessories);
        $this->assertCount(2, $brawler->starPowers);

        /*
         * Testing the database.
         */

        $this->assertDatabaseHas((new Brawler())->getTable(), ['id' => $brawler->id]);

        foreach ($brawler->accessories as $accessory) {
            $this->assertDatabaseHas((new Accessory())->getTable(), [
                'id' => $accessory->id,
                'name' => $accessory->name,
                'brawler_id' => $accessory->brawler_id,
            ]);
        }

        foreach ($brawler->starPowers as $starPower) {
            $this->assertDatabaseHas((new StarPower())->getTable(), [
                'id' => $starPower->id,
                'name' => $starPower->name,
                'brawler_id' => $starPower->brawler_id,
            ]);
        }
    }
}
