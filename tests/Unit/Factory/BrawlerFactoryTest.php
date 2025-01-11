<?php

declare(strict_types=1);

namespace Tests\Unit\Factory;

use Database\Factories\AccessoryFactory;
use Database\Factories\BrawlerFactory;
use Database\Factories\GearFactory;
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
#[CoversMethod(BrawlerFactory::class, 'withAccessories')]
#[CoversMethod(BrawlerFactory::class, 'withGears')]
#[CoversMethod(BrawlerFactory::class, 'withStarPowers')]
#[UsesClass(AccessoryFactory::class)]
#[UsesClass(GearFactory::class)]
#[UsesClass(StarPowerFactory::class)]
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
    #[TestDox('Factory successfully creates a brawler with accessories, gears and star powers.')]
    public function test_brawler_factory_creates_brawler_with_relations(): void
    {
        $accessoryCount = 2;
        $gearCount = 2;
        $starPowerCount = 2;

        // Create a brawler with related models
        $brawler = $this->createBrawlerWithRelations(
            accessoryCount: $accessoryCount,
            gearCount: $gearCount,
            starPowerCount: $starPowerCount,
        );

        $brawler->load([
            'accessories',
            'gears',
            'starPowers',
        ]);

        // Assertions for the Brawler model existence
        $this->assertTrue($brawler->exists, 'Brawler should exist in the database');
        $this->assertIsNumeric($brawler->id, 'Brawler ID should be numeric');
        $this->assertNotEmpty($brawler->name, 'Brawler name should not be empty');

        // Database assertion to verify the record
        $this->assertDatabaseHas($brawler->getTable(), $brawler->only([
            'id',
            'ext_id',
            'name',
        ]));

        /*
         * Assertions for the Brawler model relations.
         */

        $this->assertCount(
            $accessoryCount,
            $brawler->accessories,
            'Brawler should have the correct number of accessories.',
        );
        $this->assertCount(
            $gearCount,
            $brawler->gears,
            'Brawler should have the correct number of gears.',
        );
        $this->assertCount(
            $starPowerCount,
            $brawler->starPowers,
            'Brawler should have the correct number of star powers.',
        );

        /*
         * Database assertions to verify the related records.
         */

        foreach ($brawler->accessories as $accessory) {
            $this->assertDatabaseHas($accessory->getTable(), $accessory->only([
                'id',
                'ext_id',
                'name',
            ]));
        }

        foreach ($brawler->gears as $gear) {
            $this->assertDatabaseHas($gear->getTable(), $gear->only([
                'id',
                'ext_id',
                'name',
                'level',
            ]));
        }

        foreach ($brawler->starPowers as $starPower) {
            $this->assertDatabaseHas($starPower->getTable(), $starPower->only([
                'id',
                'ext_id',
                'name',
            ]));
        }
    }
}
