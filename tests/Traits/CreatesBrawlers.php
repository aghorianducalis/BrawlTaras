<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\AccessoryDTO;
use App\API\DTO\Response\BrawlerDTO;
use App\API\DTO\Response\StarPowerDTO;
use App\Models\Accessory;
use App\Models\Brawler;
use App\Models\StarPower;
use Database\Factories\BrawlerFactory;
use PHPUnit\Framework\Attributes\UsesClass;

#[UsesClass(Brawler::class)]
#[UsesClass(BrawlerFactory::class)]
trait CreatesBrawlers
{
    public const BRAWLER_RELATIONS = [
        'accessories',
        'gears',
        'starPowers',
    ];

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
     * Make a DTO for brawler with related entities: accessories, gears and star powers.
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

    public function assertEqualBrawlerModels(Brawler $brawlerExpected, Brawler $brawlerActual): void
    {
        $this->assertSame($brawlerExpected->ext_id, $brawlerActual->ext_id);
        $this->assertSame($brawlerExpected->name, $brawlerActual->name);

        if ($brawlerExpected->exists && $brawlerActual->exists) {
            $this->assertSame($brawlerExpected->id, $brawlerActual->id);
            $this->assertTrue($brawlerExpected->created_at->equalTo($brawlerActual->created_at));

            // compare the brawler's relations
            $brawlerExpected->load(self::BRAWLER_RELATIONS);
            $brawlerActual->load(self::BRAWLER_RELATIONS);

            $this->assertEquals(
                $brawlerExpected->accessories->pluck('name', 'ext_id')->all(),
                $brawlerActual->accessories->pluck('name', 'ext_id')->all()
            );

            $this->assertEquals(
                $brawlerExpected->gears->pluck('name', 'ext_id')->all(),
                $brawlerActual->gears->pluck('name', 'ext_id')->all()
            );

            $this->assertEquals(
                $brawlerExpected->starPowers->pluck('name', 'ext_id')->all(),
                $brawlerActual->starPowers->pluck('name', 'ext_id')->all()
            );
        }
    }

    public function assertBrawlerModelMatchesDTO(Brawler $brawler, BrawlerDTO $brawlerDTO): void
    {
        $this->assertSame($brawler->ext_id, $brawlerDTO->extId);
        $this->assertSame($brawler->name, $brawlerDTO->name);

        if ($brawler->exists) {
            $brawler->load(self::BRAWLER_RELATIONS);

            $this->assertEquals(
                $brawler->accessories->transform(fn(Accessory $accessory) => $accessory->only(['ext_id', 'name']))->toArray(),
                collect($brawlerDTO->accessories)->transform(fn(AccessoryDTO $accessory) => [
                    'ext_id' => $accessory->extId,
                    'name' => $accessory->name,
                ])->toArray()
            );
            $this->assertEquals(
                $brawler->starPowers->transform(fn(StarPower $starPower) => $starPower->only(['ext_id', 'name']))->toArray(),
                collect($brawlerDTO->starPowers)->transform(fn(StarPowerDTO $starPower) => [
                    'ext_id' => $starPower->extId,
                    'name' => $starPower->name,
                ])->toArray()
            );
        }
    }
}
