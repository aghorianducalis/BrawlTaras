<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Repositories;

use App\API\DTO\Response\StarPowerDTO;
use App\Models\StarPower;
use App\Services\Repositories\Contracts\StarPowerRepositoryInterface;
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
        $this->repository = app(StarPowerRepositoryInterface::class);
    }

    #[Test]
    #[TestDox('Fetch the star power successfully.')]
    #[TestWith(['ext_id', 123])]
    #[TestWith(['name', 'Magic wings'])]
    public function test_find_star_power_by_criteria(string $property, int|string $value): void
    {
        $table = (new StarPower())->getTable();
        $this->assertDatabaseMissing($table, [$property => $value]);

        /** @var StarPower $starPowerCreated */
        $starPowerCreated = StarPower::factory()->create([$property => $value]);

        $this->assertDatabaseHas($table, [
            'id' => $starPowerCreated->id,
            $property => $value,
        ]);

        $starPowerFound = $this->repository->findStarPower([$property => $value]);

        $this->assertNotNull($starPowerFound);
        $this->assertInstanceOf(StarPower::class, $starPowerCreated);
        $this->assertEquals($starPowerCreated->id, $starPowerFound->id);
        $this->assertEquals($starPowerCreated->ext_id, $starPowerFound->ext_id);
        $this->assertEquals($starPowerCreated->name, $starPowerFound->name);
    }

    #[Test]
    #[TestDox('Create successfully a star power.')]
    public function test_create_star_power(): void
    {
        /** @var StarPower $starPowerToCreate */
        $starPowerToCreate = StarPower::factory()->make();
        $table = $starPowerToCreate->getTable();

        $this->assertDatabaseMissing($table, $starPowerToCreate->only([
            'ext_id',
            'name',
        ]));

        $dto = StarPowerDTO::fromEloquentModel($starPowerToCreate);

        $starPowerCreated = $this->repository->createOrUpdateStarPower($dto);

        $this->assertEquals($starPowerToCreate->ext_id, $starPowerCreated->ext_id);
        $this->assertEquals($starPowerToCreate->name, $starPowerCreated->name);

        $this->assertDatabaseHas($table, $starPowerToCreate->only([
            'ext_id',
            'name',
        ]));
    }

    #[Test]
    #[TestDox('Update successfully a star power.')]
    public function test_update_existing_star_power(): void
    {
        /** @var StarPower $starPower */
        $starPower = StarPower::factory()->create();
        $table = $starPower->getTable();

        $this->assertDatabaseHas($table, $starPower->only([
            'id',
            'ext_id',
            'name',
        ]));

        $starPowerToUpdate = StarPower::factory()->make($starPower->only(['id', 'ext_id']));
        $dto = StarPowerDTO::fromEloquentModel($starPowerToUpdate);

        $starPowerUpdated = $this->repository->createOrUpdateStarPower($dto);

        $this->assertEquals($starPowerToUpdate->id, $starPowerUpdated->id);
        $this->assertEquals($starPowerToUpdate->ext_id, $starPowerUpdated->ext_id);
        $this->assertEquals($starPowerToUpdate->name, $starPowerUpdated->name);

        $this->assertDatabaseHas($table, $starPowerToUpdate->only([
            'id',
            'ext_id',
            'name',
        ]));
    }
}
