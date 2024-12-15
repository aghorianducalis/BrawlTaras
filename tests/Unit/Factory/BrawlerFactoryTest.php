<?php

declare(strict_types=1);

namespace Tests\Unit\Factory;

use Database\Factories\AccessoryFactory;
use Database\Factories\BrawlerFactory;
use Database\Factories\StarPowerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
#[UsesClass(AccessoryFactory::class)]
#[UsesClass(StarPowerFactory::class)]
class BrawlerFactoryTest extends TestCase
{
    use CreatesBrawlers;
    use RefreshDatabase;

    /**
     * Tests the functionality of the BrawlerFactory's definition method
     * by ensuring that it can create a Brawler with specified relations.
     */
    #[Test]
    #[TestDox('Factory successfully creates a brawler with accessories and star powers')]
    public function test_brawler_factory_creates_brawler_with_relations(): void
    {
        $accessoryCount = 2;
        $starPowerCount = 2;

        // Using the custom trait method to create a brawler with related models
        $brawler = $this->createBrawlerWithRelations(accessoryCount: $accessoryCount, starPowerCount: $starPowerCount);

        // Assertions for the Brawler model existence and relations
        $this->assertTrue($brawler->exists, 'Brawler should exist in the database');
        $this->assertIsNumeric($brawler->id, 'Brawler ID should be numeric');
        $this->assertNotEmpty($brawler->name, 'Brawler name should not be empty');
        $this->assertCount($accessoryCount, $brawler->accessories, 'Brawler should have the correct number of accessories');
        $this->assertCount($starPowerCount, $brawler->starPowers, 'Brawler should have the correct number of star powers');

        // Database assertions to verify the records
        $this->assertDatabaseHas($brawler->getTable(), ['id' => $brawler->id]);

        foreach ($brawler->accessories as $accessory) {
            $this->assertDatabaseHas($accessory->getTable(), $accessory->only([
                'id',
                'name',
                'brawler_id',
            ]));
        }

        foreach ($brawler->starPowers as $starPower) {
            $this->assertDatabaseHas($starPower->getTable(), $starPower->only([
                'id',
                'name',
                'brawler_id',
            ]));
        }
    }
}
