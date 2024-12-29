<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Repositories;

use App\API\DTO\Response\PlayerDTO;
use App\Models\Player;
use App\Services\Repositories\Contracts\PlayerRepositoryInterface;
use App\Services\Repositories\PlayerRepository;
use Database\Factories\PlayerFactory;
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
#[CoversClass(PlayerRepository::class)]
#[CoversMethod(PlayerRepository::class, 'findPlayer')]
#[CoversMethod(PlayerRepository::class, 'createOrUpdatePlayer')]
#[UsesClass(Player::class)]
#[UsesClass(PlayerFactory::class)]
class PlayerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PlayerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PlayerRepositoryInterface::class);
    }

    #[Test]
    #[TestDox('Create and fetch the player with relations successfully.')]
    #[TestWith(['ext_id', 20251212])]
    #[TestWith(['tag', '#abcd1234'])]
    #[TestWith(['name', 'Taras Shevchenko'])]
    public function test_find_player_by_criteria(string $property, int|string $value): void
    {
        $this->assertDatabaseMissing((new Player())->getTable(), [$property => $value]);

        /** @var Player $playerCreated */
        $playerCreated = Player::factory()->create(attributes: [$property => $value]);

        $this->assertDatabaseHas($playerCreated->getTable(), [
            'id' => $playerCreated->id,
            $property => $value,
        ]);

        $playerFound = $this->repository->findPlayer([$property => $value]);

        $this->assertEqualPlayerModels($playerCreated, $playerFound);
    }

    #[Test]
    #[TestDox('Create successfully the player with related entities.')]
    public function test_create_player_with_relations(): void
    {
        $playerDTO = PlayerDTO::fromEloquentModel(Player::factory()->make());

        $this->assertDatabaseMissing((new Player())->getTable(), [
            'tag' => $playerDTO->tag,
        ]);

        $player = $this->repository->createOrUpdatePlayer($playerDTO);

        $this->assertDatabaseHas($player->getTable(), [
            'id' => $player->id,
            'tag' => $playerDTO->tag,
        ]);

        $this->assertPlayerModelMatchesDTO($player, $playerDTO);
    }

    #[Test]
    #[TestDox('Update successfully the player with related entities.')]
    public function test_update_existing_player(): void
    {
        $player = Player::factory()->create();
        // create DTO to store the new data for player with the same ext ID
        $playerDTO = PlayerDTO::fromEloquentModel(Player::factory()->make());

        $playerUpdated = $this->repository->createOrUpdatePlayer($playerDTO);

        $this->assertDatabaseHas($playerUpdated->getTable(), [
            'id' => $playerUpdated->id,
            'tag' => $playerDTO->tag,
        ]);

        $this->assertPlayerModelMatchesDTO($playerUpdated, $playerDTO);
    }

    private function assertEqualPlayerModels(Player $playerExpected, ?Player $playerActual): void
    {
        $this->assertNotNull($playerActual);
        $this->assertInstanceOf(Player::class, $playerActual);
        $this->assertSame($playerExpected->id, $playerActual->id);
        $this->assertSame($playerExpected->ext_id, $playerActual->ext_id);
        $this->assertSame($playerExpected->tag, $playerActual->tag);
        $this->assertSame($playerExpected->name, $playerActual->name);
        $this->assertSame($playerExpected->club_id, $playerActual->club_id);
        $this->assertTrue($playerExpected->created_at->equalTo($playerActual->created_at));

        // compare the player's relations
        $playerExpected->load([
            'club',
        ]);
        $playerActual->load([
            'club',
        ]);

        $this->assertEquals(
            $playerExpected->club->toArray(),
            $playerActual->club->toArray()
        );
    }

    private function assertPlayerModelMatchesDTO(Player $player, PlayerDTO $playerDTO): void
    {
        $this->assertSame($player->tag, $playerDTO->tag);
        $this->assertSame($player->name, $playerDTO->name);
        $this->assertSame($player->name_color, $playerDTO->nameColor);
//        $this->assertSame($player->role, $playerDTO->role);//todo
        $this->assertSame($player->trophies, $playerDTO->trophies);
        $this->assertSame($player->icon_id, $playerDTO->icon['id']);//todo
    }
}
