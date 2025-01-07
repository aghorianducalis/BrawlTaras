<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Repositories;

use App\API\DTO\Response\ClubPlayerDTO;
use App\Models\Club;
use App\Models\Player;
use App\Services\Repositories\Contracts\PlayerRepositoryInterface;
use App\Services\Repositories\PlayerRepository;
use Database\Factories\ClubFactory;
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
use Tests\Traits\CreatesPlayers;

#[Group('Repositories')]
#[CoversClass(PlayerRepository::class)]
#[CoversMethod(PlayerRepository::class, 'findPlayer')]
#[CoversMethod(PlayerRepository::class, 'createOrUpdatePlayer')]
#[UsesClass(Player::class)]
#[UsesClass(PlayerFactory::class)]
#[UsesClass(Club::class)]
#[UsesClass(ClubFactory::class)]
class PlayerRepositoryTest extends TestCase
{
    use CreatesPlayers;
    use RefreshDatabase;

    private PlayerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PlayerRepositoryInterface::class);
    }

    #[Test]
    #[TestDox('Fetch the player with relations successfully.')]
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
        $playerDTO = ClubPlayerDTO::fromEloquentModel(Player::factory()->make());

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
        // create DTO to store the new data for player with the same tag
        $playerDTO = ClubPlayerDTO::fromEloquentModel(Player::factory()->make(attributes: [
            'tag' => $player->tag,
        ]));

        $playerUpdated = $this->repository->createOrUpdatePlayer($playerDTO);

        $this->assertDatabaseHas($playerUpdated->getTable(), [
            'id' => $playerUpdated->id,
            'tag' => $playerDTO->tag,
        ]);

        $this->assertPlayerModelMatchesDTO($playerUpdated, $playerDTO);
    }
}
