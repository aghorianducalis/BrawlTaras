<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\AccessoryDTO;
use App\API\DTO\Response\BrawlerDTO;
use App\API\DTO\Response\StarPowerDTO;
use App\Models\Accessory;
use App\Models\Brawler;
use App\Models\StarPower;

trait CreatesBrawlers
{
    /**
     * Create a brawler with associated accessories, gears and star powers.
     *
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     * @param int $accessoryCount
     * @param int $gearCount
     * @param int $starPowerCount
     * @return Brawler
     */
    public function createBrawlerWithRelations(
        array|callable $attributes = [],
        int            $accessoryCount = 2,
        int            $gearCount = 2,
        int            $starPowerCount = 2,
    ) : Brawler {
        return Brawler::factory()
            ->withAccessories($accessoryCount)
            ->withGears($gearCount)
            ->withStarPowers($starPowerCount)
            ->create($attributes);
    }

    /**
     * Create a brawler DTO with accessories and star powers.
     *
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     * @param int $accessoryCount
     * @param int $starPowerCount
     * @return BrawlerDTO
     */
    public function makeBrawlerDTOWithRelations(
        array|callable $attributes = [],
        int            $accessoryCount = 2,
        int            $starPowerCount = 2
    ) : BrawlerDTO {
        /** @var Brawler $brawler */
        $brawler = Brawler::factory()->make($attributes);

        $accessories = Accessory::factory()
            ->count($accessoryCount)
            ->make()
            ->transform(fn (Accessory $accessory) => [
                'id' => $accessory->ext_id,
                'name' => $accessory->name,
            ])
            ->toArray();

        $starPowers = StarPower::factory()
            ->count($starPowerCount)
            ->make()
            ->transform(fn (StarPower $starPower) => [
                'id' => $starPower->ext_id,
                'name' => $starPower->name,
            ])
            ->toArray();

        return BrawlerDTO::fromArray([
            'id' => $brawler->ext_id,
            'name' => $brawler->name,
            'gadgets' => $accessories,
            'starPowers' => $starPowers,
        ]);
    }

    public function assertEqualBrawlerModels(Brawler $brawlerExpected, ?Brawler $brawlerActual): void
    {
        $this->assertNotNull($brawlerActual);
        $this->assertInstanceOf(Brawler::class, $brawlerActual);
        $this->assertSame($brawlerExpected->id, $brawlerActual->id);
        $this->assertSame($brawlerExpected->ext_id, $brawlerActual->ext_id);
        $this->assertSame($brawlerExpected->name, $brawlerActual->name);
        $this->assertTrue($brawlerExpected->created_at->equalTo($brawlerActual->created_at));

        // compare the brawler's relations
        $brawlerExpected->load(['accessories', 'starPowers']);
        $brawlerActual->load(['accessories', 'starPowers']);

        $this->assertEquals(
            $brawlerExpected->accessories->toArray(),
            $brawlerActual->accessories->toArray()
        );
        $this->assertEquals(
            $brawlerExpected->starPowers->toArray(),
            $brawlerActual->starPowers->toArray()
        );
    }

    public function assertBrawlerModelMatchesDTO(Brawler $brawler, BrawlerDTO $brawlerDTO): void
    {
        $this->assertSame($brawler->ext_id, $brawlerDTO->extId);
        $this->assertSame($brawler->name, $brawlerDTO->name);

        $brawler->load(['accessories', 'starPowers']);

        $this->assertEquals(
            $brawler->accessories->transform(fn(Accessory $accessory) => $accessory->only(['ext_id', 'name']))->toArray(),
            collect($brawlerDTO->accessories)->transform(fn(AccessoryDTO $accessory) => $accessory->toArray())->toArray()
        );
        $this->assertEquals(
            $brawler->starPowers->transform(fn(StarPower $starPower) => $starPower->only(['ext_id', 'name']))->toArray(),
            collect($brawlerDTO->starPowers)->transform(fn(StarPowerDTO $starPower) => $starPower->toArray())->toArray()
        );
    }
}
