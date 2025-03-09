<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Repositories;

use App\API\DTO\Response\PlayerDTO;
use App\Models\Club;
use App\Models\Player;
use App\Services\Repositories\Contracts\PlayerRepositoryInterface;
use App\Services\Repositories\PlayerRepository;
use Database\Factories\ClubFactory;
use Database\Factories\PlayerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;
use Tests\Traits\TestPlayers;

#[Group('Repositories')]
#[CoversClass(PlayerRepository::class)]
#[CoversMethod(PlayerRepository::class, 'findPlayer')]
#[CoversMethod(PlayerRepository::class, 'createOrUpdatePlayerFromDTO')]
#[UsesClass(Player::class)]
#[UsesClass(PlayerFactory::class)]
#[UsesClass(Club::class)]
#[UsesClass(ClubFactory::class)]
class PlayerRepositoryTest extends TestCase
{
    use TestPlayers;
    use RefreshDatabase;

    private PlayerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PlayerRepositoryInterface::class);
        $this->playerTable = (new Player())->getTable();
    }

    #[Test]
    #[TestDox('Fetch the club member with relations successfully.')]
    #[TestWith(['id', '123'])]
    #[TestWith(['tag', '#abcd1234'])]
    #[TestWith(['name', 'Taras Shevchenko'])]
    #[TestWith(['club_role', 'President'])]
    public function test_find_club_member_by_criteria(string $property, int|string $value): void
    {
        $this->assertDatabaseMissing($this->playerTable, [$property => $value]);

        /** @var Player $memberCreated */
        $memberCreated = Player::factory()->withClub()->create(attributes: [$property => $value]);

        $this->assertDatabaseHas($this->playerTable, [
            'id' => $memberCreated->id,
            $property => $value,
        ]);

        $memberFound = $this->repository->findPlayer([$property => $value]);

        $this->assertNotNull($memberFound);
        $this->assertInstanceOf(Player::class, $memberFound);
        $this->assertEqualPlayerModels($memberCreated, $memberFound);
    }

    #[Test]
    #[TestDox('Create successfully the player with related entities.')]
    #[DataProvider('providePlayerDTOData')]
    public function test_create_player_from_dto(array $playerData): void
    {
        $playerDTO = PlayerDTO::fromArray($playerData);

        $this->assertDatabaseMissing($this->playerTable, [
            'tag' => $playerDTO->tag,
        ]);

        $player = $this->repository->createOrUpdatePlayerFromDTO($playerDTO);

        $this->assertDatabaseHas($this->playerTable, [
            'id' => $player->id,
            'tag' => $playerDTO->tag,
        ]);

        $this->assertPlayerDTOMatchesEloquentModel(
            playerDTO: $playerDTO,
            player: $player,
            checkRelations: false,
        );
    }
}
