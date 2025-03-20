<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Repositories;

use App\Models\Brawler;
use App\Services\Repositories\BrawlerRepository;
use App\Services\Repositories\Contracts\BrawlerRepositoryInterface;
use Database\Factories\BrawlerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;
use Tests\Traits\CreatesBrawlers;

#[Group('Repositories')]
#[CoversClass(BrawlerRepository::class)]
#[CoversMethod(BrawlerRepository::class, 'findBrawler')]
#[CoversMethod(BrawlerRepository::class, 'createOrUpdateBrawlerFromDTO')]
#[CoversMethod(BrawlerRepository::class, 'createOrUpdateBrawlerFromDTOAndSyncRelations')]
#[CoversMethod(BrawlerRepository::class, 'syncBrawlerRelations')]
#[UsesClass(Brawler::class)]
#[UsesClass(BrawlerFactory::class)]
class BrawlerRepositoryTest extends TestCase
{
    use CreatesBrawlers;
    use RefreshDatabase;

    private BrawlerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(BrawlerRepositoryInterface::class);
        $this->brawlerTable = (new Brawler())->getTable();
    }

    #[Test]
    #[TestDox('Create and fetch the brawler with relations successfully.')]
    #[TestWith(['ext_id', 123])]
    #[TestWith(['name', 'Shelly'])]
    public function test_find_brawler_by_criteria(string $property, int|string $value): void
    {
        $this->assertDatabaseEmpty($this->brawlerTable);

        $brawlerCreated = $this->createBrawlerWithRelations(attributes: [$property => $value]);

        $this->assertDatabaseHas($this->brawlerTable, [
            'id' => $brawlerCreated->id,
            $property => $value,
        ]);

        $brawlerFound = $this->repository->findBrawler([$property => $value]);

        $this->assertNotNull($brawlerFound);
        $this->assertInstanceOf(Brawler::class, $brawlerFound);
        $this->assertEqualBrawlerModels($brawlerCreated, $brawlerFound);
    }

    #[Test]
    #[TestDox('Create successfully the brawler.')]
    public function test_create_brawler_from_dto(): void
    {
        $brawlerDTO = $this->makeBrawlerDTOWithRelations();

        $this->assertDatabaseEmpty($this->brawlerTable);

        $brawler = $this->repository->createOrUpdateBrawlerFromDTO($brawlerDTO);

        $this->assertDatabaseHas($this->brawlerTable, [
            'id'     => $brawler->id,
            'ext_id' => $brawlerDTO->extId,
            'name'   => $brawlerDTO->name,
        ]);

        $this->assertBrawlerModelMatchesDTO(
            brawler: $brawler,
            brawlerDTO: $brawlerDTO,
            checkRelations: false,
        );
    }

    /**
     * @throws ValidationException
     * @throws JsonException
     */
    #[Test]
    #[TestDox('Create successfully the brawler with related entities.')]
    public function test_create_brawler_from_dto_and_sync_relations(): void
    {
        $brawlerDTO = $this->makeBrawlerDTOWithRelations();

        $this->assertDatabaseEmpty($this->brawlerTable);

        $brawler = $this->repository->createOrUpdateBrawlerFromDTOAndSyncRelations($brawlerDTO);

        $this->assertDatabaseHas($this->brawlerTable, [
            'id'     => $brawler->id,
            'ext_id' => $brawlerDTO->extId,
            'name'   => $brawlerDTO->name,
        ]);

        $this->assertBrawlerModelMatchesDTO(
            brawler: $brawler,
            brawlerDTO: $brawlerDTO,
            checkRelations: true,
        );
    }

    #[Test]
    #[TestDox('Update successfully the brawler with related entities.')]
    public function test_update_existing_brawler_from_dto(): void
    {
        $brawler = $this->createBrawlerWithRelations();
        // create DTO to store the new data for brawler with the same ext ID
        $brawlerDTO = $this->makeBrawlerDTOWithRelations([
            'ext_id' => $brawler->ext_id,
        ]);

        $this->assertDatabaseCount($this->brawlerTable, 1);
        $this->assertDatabaseHas($this->brawlerTable, [
            'id'     => $brawler->id,
            'ext_id' => $brawler->ext_id,
            'name'   => $brawler->name,
        ]);

        $brawlerUpdated = $this->repository->createOrUpdateBrawlerFromDTO($brawlerDTO);

        $this->assertDatabaseCount($this->brawlerTable, 1);
        $this->assertDatabaseHas($this->brawlerTable, [
            'id'     => $brawler->id,
            'ext_id' => $brawlerDTO->extId,
            'name'   => $brawlerDTO->name,
        ]);
        $this->assertBrawlerModelMatchesDTO(
            brawler: $brawlerUpdated,
            brawlerDTO: $brawlerDTO,
            checkRelations: false,
        );
    }

    /**
     * @throws ValidationException
     * @throws JsonException
     */
    #[Test]
    #[TestDox('Update successfully the brawler with related entities.')]
    public function test_update_existing_brawler_from_dto_and_sync_relations(): void
    {
        $brawler = $this->createBrawlerWithRelations();
        // create DTO to store the new data for brawler with the same ext ID
        $brawlerDTO = $this->makeBrawlerDTOWithRelations([
            'ext_id' => $brawler->ext_id,
        ]);

        $this->assertDatabaseCount($this->brawlerTable, 1);
        $this->assertDatabaseHas($this->brawlerTable, [
            'id'     => $brawler->id,
            'ext_id' => $brawler->ext_id,
            'name'   => $brawler->name,
        ]);

        $brawlerUpdated = $this->repository->createOrUpdateBrawlerFromDTOAndSyncRelations($brawlerDTO);

        $this->assertDatabaseCount($this->brawlerTable, 1);
        $this->assertDatabaseHas($this->brawlerTable, [
            'id'     => $brawler->id,
            'ext_id' => $brawlerDTO->extId,
            'name'   => $brawlerDTO->name,
        ]);
        $this->assertBrawlerModelMatchesDTO($brawlerUpdated, $brawlerDTO);
    }

    // todo test createOrUpdateBrawlerFromDataArray
    // todo use data provider to provide data for brawler with relations
}
