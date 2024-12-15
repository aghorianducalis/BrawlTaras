<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Repositories;

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
    #[TestWith(['name', 'Shield'])]
    #[TestWith(['ext_id', 123])]
    public function test_find_accessory_by_criteria(string $property, int|string $value): void
    {
        $this->assertDatabaseMissing((new Accessory())->getTable(), [$property => $value]);

        /** @var Accessory $accessoryCreated */
        $accessoryCreated = Accessory::factory()->create([$property => $value]);

        $this->assertDatabaseHas($accessoryCreated->getTable(), [
            'id' => $accessoryCreated->id,
            $property => $value,
        ]);

        $accessoryFound = $this->repository->findAccessory([$property => $value]);

        $this->assertNotNull($accessoryFound);
        $this->assertInstanceOf(Accessory::class, $accessoryCreated);
        $this->assertEquals($accessoryCreated->id, $accessoryFound->id);
        $this->assertEquals($accessoryCreated->ext_id, $accessoryFound->ext_id);
        $this->assertEquals($accessoryCreated->name, $accessoryFound->name);
        $this->assertEquals($accessoryCreated->brawler_id, $accessoryFound->brawler_id);
    }

    #[Test]
    #[TestDox('Create successfully an accessory for related brawler.')]
    public function test_create_accessory(): void
    {
        /** @var Brawler $brawler */
        $brawler = Brawler::factory()->create();
        /** @var Accessory $accessory */
        $accessory = Accessory::factory()->for($brawler)->make();

        $this->assertDatabaseMissing($accessory->getTable(), $accessory->only([
            'ext_id',
            'name',
            'brawler_id',
        ]));

        $dto = AccessoryDTO::fromArray([
            'id' => $accessory->ext_id,
            'name' => $accessory->name,
        ]);

        $accessoryCreated = $this->repository->createOrUpdateAccessory($dto, $accessory->brawler_id);
        $this->assertDatabaseHas($accessory->getTable(), $accessory->only([
            'ext_id',
            'name',
            'brawler_id',
        ]));
        $this->assertEquals($accessory->ext_id, $accessoryCreated->ext_id);
        $this->assertEquals($accessory->name, $accessoryCreated->name);
        $this->assertEquals($accessory->brawler_id, $accessoryCreated->brawler_id);
    }

    #[Test]
    #[TestDox('Update successfully an accessory for related brawler.')]
    public function test_update_existing_accessory(): void
    {
        /** @var Brawler $brawler */
        $brawler = Brawler::factory()->create();
        /** @var Accessory $accessory */
        $accessory = Accessory::factory()->for($brawler)->create();

        $this->assertDatabaseHas($accessory->getTable(), $accessory->only([
            'id',
            'ext_id',
            'name',
            'brawler_id',
        ]));

        $dto = AccessoryDTO::fromArray([
            'id' => $accessory->ext_id,
            'name' => 'new name of accessory',
        ]);

        $accessoryUpdated = $this->repository->createOrUpdateAccessory($dto, $accessory->brawler_id);
        $this->assertDatabaseHas($accessory->getTable(), [
            'id' => $accessory->id,
            'ext_id' => $dto->extId,
            'name' => $dto->name,
            'brawler_id' => $accessory->brawler_id,
        ]);
        $this->assertEquals($accessory->id, $accessoryUpdated->id);
        $this->assertEquals($accessory->ext_id, $accessoryUpdated->ext_id);
        $this->assertEquals($dto->name, $accessoryUpdated->name);
        $this->assertEquals($accessory->brawler_id, $accessoryUpdated->brawler_id);
    }
}
