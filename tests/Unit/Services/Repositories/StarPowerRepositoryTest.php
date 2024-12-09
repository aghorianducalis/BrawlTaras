<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Repositories;

use App\API\DTO\Response\StarPowerDTO;
use App\Models\StarPower;
use App\Models\Brawler;
use App\Services\Repositories\StarPowerRepository;
use Database\Factories\StarPowerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[Group('Repositories')]
#[CoversClass(StarPowerRepository::class)]
#[CoversMethod(StarPowerRepository::class, 'getInstance')]
#[CoversMethod(StarPowerRepository::class, 'findStarPower')]
#[CoversMethod(StarPowerRepository::class, 'createOrUpdateStarPower')]
#[UsesClass(StarPower::class)]
#[UsesClass(StarPowerFactory::class)]
class StarPowerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private StarPowerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = StarPowerRepository::getInstance();
    }

    #[Test]
    #[TestDox('Fetch the star power successfully.')]
    #[TestWith(['name', 'Magic wings'])]
    #[TestWith(['ext_id', 123])]
    public function test_find_star_power_by_criteria(string $property, int|string $value): void
    {
        $this->assertDatabaseMissing((new StarPower())->getTable(), [$property => $value]);

        /** @var StarPower $starPowerCreated */
        $starPowerCreated = StarPower::factory()->create([$property => $value]);

        $this->assertDatabaseHas($starPowerCreated->getTable(), [
            'id' => $starPowerCreated->id,
            $property => $value,
        ]);

        $starPowerFound = $this->repository->findStarPower([$property => $value]);

        $this->assertNotNull($starPowerFound);
        $this->assertInstanceOf(StarPower::class, $starPowerCreated);
        $this->assertEquals($starPowerCreated->id, $starPowerFound->id);
        $this->assertEquals($starPowerCreated->ext_id, $starPowerFound->ext_id);
        $this->assertEquals($starPowerCreated->name, $starPowerFound->name);
        $this->assertEquals($starPowerCreated->brawler_id, $starPowerFound->brawler_id);
    }

    #[Test]
    #[TestDox('Create successfully a star power for related brawler.')]
    public function test_create_star_power(): void
    {
        /** @var Brawler $brawler */
        $brawler = Brawler::factory()->create();
        /** @var StarPower $starPower */
        $starPower = StarPower::factory()->for($brawler)->make();

        $this->assertDatabaseMissing($starPower->getTable(), $starPower->only([
            'ext_id',
            'name',
            'brawler_id',
        ]));

        $dto = StarPowerDTO::fromArray([
            'id' => $starPower->ext_id,
            'name' => $starPower->name,
        ]);

        $starPowerCreated = $this->repository->createOrUpdateStarPower($dto, $starPower->brawler_id);
        $this->assertDatabaseHas($starPower->getTable(), $starPower->only([
            'ext_id',
            'name',
            'brawler_id',
        ]));
        $this->assertEquals($starPower->ext_id, $starPowerCreated->ext_id);
        $this->assertEquals($starPower->name, $starPowerCreated->name);
        $this->assertEquals($starPower->brawler_id, $starPowerCreated->brawler_id);
    }

    #[Test]
    #[TestDox('Update successfully a star power for related brawler.')]
    public function test_update_existing_star_power(): void
    {
        /** @var Brawler $brawler */
        $brawler = Brawler::factory()->create();
        /** @var StarPower $starPower */
        $starPower = StarPower::factory()->for($brawler)->create();

        $this->assertDatabaseHas($starPower->getTable(), $starPower->only([
            'id',
            'ext_id',
            'name',
            'brawler_id',
        ]));

        $dto = StarPowerDTO::fromArray([
            'id' => $starPower->ext_id,
            'name' => 'new name of star power',
        ]);

        $starPowerUpdated = $this->repository->createOrUpdateStarPower($dto, $starPower->brawler_id);
        $this->assertDatabaseHas($starPower->getTable(), [
            'id' => $starPower->id,
            'ext_id' => $dto->extId,
            'name' => $dto->name,
            'brawler_id' => $starPower->brawler_id,
        ]);
        $this->assertEquals($starPower->id, $starPowerUpdated->id);
        $this->assertEquals($starPower->ext_id, $starPowerUpdated->ext_id);
        $this->assertEquals($dto->name, $starPowerUpdated->name);
        $this->assertEquals($starPower->brawler_id, $starPowerUpdated->brawler_id);
    }
}
