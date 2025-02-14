<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Repositories;

use App\API\DTO\Response\GearDTO;
use App\Models\Gear;
use App\Services\Repositories\Contracts\GearRepositoryInterface;
use App\Services\Repositories\GearRepository;
use Database\Factories\GearFactory;
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
#[CoversClass(GearRepository::class)]
#[CoversMethod(GearRepository::class, 'findGear')]
#[CoversMethod(GearRepository::class, 'createOrUpdateGear')]
#[UsesClass(Gear::class)]
#[UsesClass(GearFactory::class)]
#[UsesClass(GearDTO::class)]
class GearRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private GearRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(GearRepositoryInterface::class);
    }

    #[Test]
    #[TestDox('Fetch a gear successfully.')]
    #[TestWith(['ext_id', 123])]
    #[TestWith(['name', 'Quick load'])]
    public function test_find_gear_by_criteria(string $property, int|string $value): void
    {
        $table = (new Gear())->getTable();
        $this->assertDatabaseMissing($table, [$property => $value]);

        /** @var Gear $gearCreated */
        $gearCreated = Gear::factory()->create([$property => $value]);

        $this->assertDatabaseHas($table, [
            'id' => $gearCreated->id,
            $property => $value,
        ]);

        $gearFound = $this->repository->findGear([$property => $value]);

        $this->assertNotNull($gearFound);
        $this->assertInstanceOf(Gear::class, $gearCreated);
        $this->assertEquals($gearCreated->id, $gearFound->id);
        $this->assertEquals($gearCreated->ext_id, $gearFound->ext_id);
        $this->assertEquals($gearCreated->name, $gearFound->name);
    }

    #[Test]
    #[TestDox('Create successfully a gear.')]
    public function test_create_gear(): void
    {
        /** @var Gear $gearToCreate */
        $gearToCreate = Gear::factory()->make();
        $table = $gearToCreate->getTable();

        $this->assertDatabaseMissing($table, $gearToCreate->only([
            'ext_id',
            'name',
        ]));

        $dto = GearDTO::fromEloquentModel($gearToCreate);

        $gearCreated = $this->repository->createOrUpdateGear($dto);

        $this->assertEquals($gearToCreate->ext_id, $gearCreated->ext_id);
        $this->assertEquals($gearToCreate->name, $gearCreated->name);

        $this->assertDatabaseHas($table, $gearToCreate->only([
            'ext_id',
            'name',
        ]));
    }

    #[Test]
    #[TestDox('Update successfully a gear.')]
    public function test_update_existing_gear(): void
    {
        /** @var Gear $gear */
        $gear = Gear::factory()->create();
        $table = $gear->getTable();

        $this->assertDatabaseHas($table, $gear->only([
            'id',
            'ext_id',
            'name',
        ]));

        $gearToUpdate = Gear::factory()->make($gear->only(['id', 'ext_id']));
        $dto = GearDTO::fromEloquentModel($gearToUpdate);

        $gearUpdated = $this->repository->createOrUpdateGear($dto);

        $this->assertEquals($gearToUpdate->id, $gearUpdated->id);
        $this->assertEquals($gearToUpdate->ext_id, $gearUpdated->ext_id);
        $this->assertEquals($gearToUpdate->name, $gearUpdated->name);

        $this->assertDatabaseHas($table, $gearToUpdate->only([
            'id',
            'ext_id',
            'name',
        ]));
    }
}
