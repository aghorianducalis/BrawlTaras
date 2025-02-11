<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Repositories;

use App\API\DTO\Response\AccessoryDTO;
use App\Models\Accessory;
use App\Services\Repositories\AccessoryRepository;
use App\Services\Repositories\Contracts\AccessoryRepositoryInterface;
use Database\Factories\AccessoryFactory;
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
#[CoversClass(AccessoryRepository::class)]
#[CoversMethod(AccessoryRepository::class, 'findAccessory')]
#[CoversMethod(AccessoryRepository::class, 'createOrUpdateAccessory')]
#[UsesClass(Accessory::class)]
#[UsesClass(AccessoryFactory::class)]
#[UsesClass(AccessoryDTO::class)]
class AccessoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AccessoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(AccessoryRepositoryInterface::class);
    }

    #[Test]
    #[TestDox('Fetch the accessory successfully.')]
    #[TestWith(['ext_id', 123])]
    #[TestWith(['name', 'Shield'])]
    public function test_find_accessory_by_criteria(string $property, int|string $value): void
    {
        $table = (new Accessory())->getTable();
        $this->assertDatabaseMissing($table, [$property => $value]);

        /** @var Accessory $accessoryCreated */
        $accessoryCreated = Accessory::factory()->create([$property => $value]);

        $this->assertDatabaseHas($table, [
            'id' => $accessoryCreated->id,
            $property => $value,
        ]);

        $accessoryFound = $this->repository->findAccessory([$property => $value]);

        $this->assertNotNull($accessoryFound);
        $this->assertInstanceOf(Accessory::class, $accessoryCreated);
        $this->assertEquals($accessoryCreated->id, $accessoryFound->id);
        $this->assertEquals($accessoryCreated->ext_id, $accessoryFound->ext_id);
        $this->assertEquals($accessoryCreated->name, $accessoryFound->name);
    }

    #[Test]
    #[TestDox('Create successfully an accessory.')]
    public function test_create_accessory(): void
    {
        /** @var Accessory $accessoryToCreate */
        $accessoryToCreate = Accessory::factory()->make();
        $table = $accessoryToCreate->getTable();

        $this->assertDatabaseMissing($table, $accessoryToCreate->only([
            'ext_id',
            'name',
        ]));

        $dto = AccessoryDTO::fromEloquentModel($accessoryToCreate);

        $accessoryCreated = $this->repository->createOrUpdateAccessory($dto);

        $this->assertEquals($accessoryToCreate->ext_id, $accessoryCreated->ext_id);
        $this->assertEquals($accessoryToCreate->name, $accessoryCreated->name);

        $this->assertDatabaseHas($table, $accessoryToCreate->only([
            'ext_id',
            'name',
        ]));
    }

    #[Test]
    #[TestDox('Update successfully an accessory.')]
    public function test_update_existing_accessory(): void
    {
        /** @var Accessory $accessory */
        $accessory = Accessory::factory()->create();
        $table = $accessory->getTable();

        $this->assertDatabaseHas($table, $accessory->only([
            'id',
            'ext_id',
            'name',
        ]));

        $accessoryToUpdate = Accessory::factory()->make($accessory->only(['id', 'ext_id']));
        $dto = AccessoryDTO::fromEloquentModel($accessoryToUpdate);

        $accessoryUpdated = $this->repository->createOrUpdateAccessory($dto);

        $this->assertEquals($accessoryToUpdate->id, $accessoryUpdated->id);
        $this->assertEquals($accessoryToUpdate->ext_id, $accessoryUpdated->ext_id);
        $this->assertEquals($accessoryToUpdate->name, $accessoryUpdated->name);

        $this->assertDatabaseHas($table, $accessoryToUpdate->only([
            'id',
            'ext_id',
            'name',
        ]));
    }
}
