<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Repositories;

use App\Models\Brawler;
use App\Services\Repositories\BrawlerRepository;
use App\Services\Repositories\Contracts\BrawlerRepositoryInterface;
use Database\Factories\BrawlerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
#[CoversMethod(BrawlerRepository::class, 'createOrUpdateBrawler')]
#[CoversMethod(BrawlerRepository::class, 'syncRelations')]
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
    }

    #[Test]
    #[TestDox('Create and fetch the brawler with relations successfully.')]
    #[TestWith(['ext_id', 123])]
    #[TestWith(['name', 'Shelly'])]
    public function test_find_brawler_by_criteria(string $property, int|string $value): void
    {
        $table = (new Brawler())->getTable();
        $this->assertDatabaseMissing($table, [$property => $value]);

        $brawlerCreated = $this->createBrawlerWithRelations(attributes: [$property => $value]);

        $this->assertDatabaseHas($table, [
            'id' => $brawlerCreated->id,
            $property => $value,
        ]);

        $brawlerFound = $this->repository->findBrawler([$property => $value]);

        $this->assertNotNull($brawlerFound);
        $this->assertInstanceOf(Brawler::class, $brawlerFound);
        $this->assertEqualBrawlerModels($brawlerCreated, $brawlerFound);
    }

    #[Test]
    #[TestDox('Create successfully the brawler with related entities.')]
    public function test_create_brawler(): void
    {
        $table = (new Brawler())->getTable();
        $brawlerDTO = $this->makeBrawlerDTOWithRelations();

        $this->assertDatabaseMissing($table, [
            'ext_id' => $brawlerDTO->extId,
            'name' => $brawlerDTO->name,
        ]);

        $brawler = $this->repository->createOrUpdateBrawler($brawlerDTO);

        $this->assertDatabaseHas($table, [
            'id' => $brawler->id,
            'ext_id' => $brawlerDTO->extId,
            'name' => $brawlerDTO->name,
        ]);

        $this->assertBrawlerModelMatchesDTO($brawler, $brawlerDTO);
    }

    #[Test]
    #[TestDox('Update successfully the brawler with related entities.')]
    public function test_update_existing_brawler(): void
    {
        $brawler = $this->createBrawlerWithRelations();
        // create DTO to store the new data for brawler with the same ext ID
        $brawlerDTO = $this->makeBrawlerDTOWithRelations([
            'ext_id' => $brawler->ext_id,
        ]);

        $brawlerUpdated = $this->repository->createOrUpdateBrawler($brawlerDTO);

        $this->assertDatabaseHas($brawler->getTable(), [
            'id' => $brawler->id,
            'ext_id' => $brawlerDTO->extId,
            'name' => $brawlerDTO->name,
        ]);

        $this->assertBrawlerModelMatchesDTO($brawlerUpdated, $brawlerDTO);
    }
}
