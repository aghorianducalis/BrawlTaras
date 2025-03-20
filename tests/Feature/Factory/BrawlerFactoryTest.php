<?php

declare(strict_types=1);

namespace Tests\Feature\Factory;

use App\Models\Accessory;
use App\Models\Brawler;
use App\Models\BrawlerAccessory;
use App\Models\BrawlerGear;
use App\Models\BrawlerStarPower;
use App\Models\Gear;
use App\Models\Player;
use App\Models\PlayerBrawler;
use App\Models\StarPower;
use Database\Factories\BrawlerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;
use Tests\Traits\CreatesBrawlers;

#[Group('Factories')]
#[CoversClass(BrawlerFactory::class)]
#[CoversMethod(BrawlerFactory::class, 'definition')]
#[CoversMethod(BrawlerFactory::class, 'withAccessories')]
#[CoversMethod(BrawlerFactory::class, 'withGears')]
#[CoversMethod(BrawlerFactory::class, 'withStarPowers')]
#[CoversMethod(BrawlerFactory::class, 'withPlayers')]
#[UsesClass(Brawler::class)]
class BrawlerFactoryTest extends TestCase
{
    use CreatesBrawlers;
    use RefreshDatabase;

    /**
     * Tests the functionality of the factory's methods
     * by ensuring that it can create a brawler with specified relations.
     *
     * @return void
     */
    #[Test]
    #[TestDox('Factory successfully creates a brawler with accessories, gears, star powers and players.')]
    public function test_factory_creates_brawler_with_relations(): void
    {
        $accessoryCount = 2;
        $gearCount = 2;
        $starPowerCount = 2;
        $playerCount = 5;

        $brawler = $this->createBrawlerWithRelations(
            accessoryCount: $accessoryCount,
            gearCount: $gearCount,
            starPowerCount: $starPowerCount,
            playerCount: $playerCount,
        );

        $this->assertInstanceOf(Brawler::class, $brawler);
        $this->assertTrue($brawler->exists, 'Brawler should exist in the database');
        $this->assertIsNumeric($brawler->id, 'Brawler ID should be numeric');
        $this->assertNotEmpty($brawler->name, 'Brawler name should not be empty');

        $this->assertDatabaseHas($brawler->getTable(), $brawler->only([
            'id',
            'ext_id',
            'name',
        ]));

        $this->assertCount(
            $accessoryCount,
            $brawler->accessories,
            "Brawler should have the correct number ({$accessoryCount}) of accessories.",
        );
        $this->assertCount(
            $gearCount,
            $brawler->gears,
            "Brawler should have the correct number ({$gearCount}) of gears.",
        );
        $this->assertCount(
            $starPowerCount,
            $brawler->starPowers,
            "Brawler should have the correct number ({$starPowerCount}) of star powers.",
        );
        $this->assertCount(
            $playerCount,
            $brawler->players,
            "Brawler should have the correct number ({$playerCount}) of players.",
        );

        foreach ($brawler->accessories as $accessory) {
            $this->assertInstanceOf(Accessory::class, $accessory);
            $this->assertDatabaseHas($accessory->getTable(), $accessory->only([
                'id',
                'ext_id',
                'name',
            ]));

            $pivot = $accessory->brawler_accessory;
            $this->assertInstanceOf(BrawlerAccessory::class, $pivot);
            $this->assertIsInt($pivot->id);
            $this->assertEquals($brawler->id, $pivot->brawler_id);
            $this->assertEquals($accessory->id, $pivot->accessory_id);
            $this->assertDatabaseHas($pivot->getTable(), $pivot->only([
                'id',
                'brawler_id',
                'accessory_id',
            ]));
        }

        foreach ($brawler->gears as $gear) {
            $this->assertInstanceOf(Gear::class, $gear);
            $this->assertDatabaseHas($gear->getTable(), $gear->only([
                'id',
                'ext_id',
                'name',
            ]));

            $pivot = $gear->brawler_gear;
            $this->assertInstanceOf(BrawlerGear::class, $pivot);
            $this->assertIsInt($pivot->id);
            $this->assertEquals($brawler->id, $pivot->brawler_id);
            $this->assertEquals($gear->id, $pivot->gear_id);
            $this->assertDatabaseHas($pivot->getTable(), $pivot->only([
                'id',
                'brawler_id',
                'gear_id',
            ]));
        }

        foreach ($brawler->starPowers as $starPower) {
            $this->assertInstanceOf(StarPower::class, $starPower);
            $this->assertDatabaseHas($starPower->getTable(), $starPower->only([
                'id',
                'ext_id',
                'name',
            ]));

            $pivot = $starPower->brawler_star_power;
            $this->assertInstanceOf(BrawlerStarPower::class, $pivot);
            $this->assertIsInt($pivot->id);
            $this->assertEquals($brawler->id, $pivot->brawler_id);
            $this->assertEquals($starPower->id, $pivot->star_power_id);
            $this->assertDatabaseHas($pivot->getTable(), $pivot->only([
                'id',
                'brawler_id',
                'star_power_id',
            ]));
        }

        foreach ($brawler->players as $player) {
            $this->assertInstanceOf(Player::class, $player);
            $this->assertDatabaseHas($player->getTable(), $player->only([
                'id',
                'tag',
                'name',
                'trophies',
            ]));

            $pivot = $player->player_brawler;
            $this->assertInstanceOf(PlayerBrawler::class, $pivot);
            $this->assertIsInt($pivot->id);
            $this->assertEquals($brawler->id, $pivot->brawler_id);
            $this->assertEquals($player->id, $pivot->player_id);
            $this->assertIsInt($pivot->power);
            $this->assertIsInt($pivot->rank);
            $this->assertIsInt($pivot->trophies);
            $this->assertIsInt($pivot->highest_trophies);
            $this->assertInstanceOf(Carbon::class, $pivot->created_at);
            $this->assertInstanceOf(Carbon::class, $pivot->updated_at);
            $this->assertDatabaseHas($pivot->getTable(), $pivot->only([
                'id',
                'brawler_id',
                'player_id',
                'power',
                'rank',
                'trophies',
                'highest_trophies',
            ]));
        }
    }
}
