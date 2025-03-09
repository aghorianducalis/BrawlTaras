<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Repositories;

use App\API\DTO\Response\AccessoryDTO;
use App\Models\Accessory;
use App\Models\Brawler;
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
        $this->accessoryTable = (new Accessory())->getTable();
    }

    #[Test]
    #[TestDox('Fetch the accessory successfully.')]
    #[TestWith(['ext_id', 123])]
    #[TestWith(['name', 'Shield'])]
    public function test_find_accessory_by_criteria(string $property, int|string $value): void
    {
        $table = $this->accessoryTable;
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
    #[TestDox('Fetch the accessory via relations successfully.')]
    public function test_find_accessory_by_related_brawler(): void
    {
        // todo
        return;
        $brawlerCount = 1;
        /** @var Accessory $accessoryCreated */
        $accessoryCreated = Accessory::factory()
            ->withBrawlers(count: $brawlerCount)
            ->create();
        /** @var Brawler $brawler */
        $brawler = $accessoryCreated->brawlers->first();

        $this->assertDatabaseHas($this->accessoryTable, [
            'id' => $accessoryCreated->id,
        ]);
        $this->assertDatabaseHas($brawler->getTable(), [
            'id' => $brawler->id,
        ]);

        $accessoryFound = $this->repository->findAccessory(['brawler_id' => $brawler->id]);
        $accessoryFound->load(['brawlers']);

        $this->assertNotNull($accessoryFound);
        $this->assertInstanceOf(Accessory::class, $accessoryFound);
        $this->assertEquals($accessoryCreated->id, $accessoryFound->id);
        $this->assertEquals($accessoryCreated->ext_id, $accessoryFound->ext_id);
        $this->assertEquals($accessoryCreated->name, $accessoryFound->name);
        $this->assertCount(
            $brawlerCount,
            $accessoryFound->brawlers,
            "Accessory should have the correct number ({$brawlerCount}) of brawlers.",
        );
    }

    #[Test]
    #[TestDox('Create successfully an accessory.')]
    public function test_create_accessory(): void
    {
        /** @var Accessory $accessoryToCreate */
        $accessoryToCreate = Accessory::factory()->make();

        $this->assertDatabaseMissing($this->accessoryTable, $accessoryToCreate->only([
            'ext_id',
            'name',
        ]));

        $dto = AccessoryDTO::fromEloquentModel($accessoryToCreate);

        $accessoryCreated = $this->repository->createOrUpdateAccessory($dto);

        $this->assertEquals($accessoryToCreate->ext_id, $accessoryCreated->ext_id);
        $this->assertEquals($accessoryToCreate->name, $accessoryCreated->name);

        $this->assertDatabaseHas($this->accessoryTable, $accessoryToCreate->only([
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

        $this->assertDatabaseHas($this->accessoryTable, $accessory->only([
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

        $this->assertDatabaseHas($this->accessoryTable, $accessoryToUpdate->only([
            'id',
            'ext_id',
            'name',
        ]));
    }
}
