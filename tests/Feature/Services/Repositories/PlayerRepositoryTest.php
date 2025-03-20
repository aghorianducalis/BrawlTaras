<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Repositories;

use App\API\DTO\Response\PlayerDTO;
use App\Models\Accessory;
use App\Models\Brawler;
use App\Models\Club;
use App\Models\Gear;
use App\Models\Player;
use App\Models\PlayerBrawler;
use App\Models\StarPower;
use App\Services\Repositories\Contracts\PlayerRepositoryInterface;
use App\Services\Repositories\PlayerRepository;
use Database\Factories\ClubFactory;
use Database\Factories\PlayerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use JsonException;
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
#[CoversMethod(PlayerRepository::class, 'createOrUpdatePlayerFromDataArray')]
#[CoversMethod(PlayerRepository::class, 'createOrUpdatePlayerFromDTO')]
#[CoversMethod(PlayerRepository::class, 'createOrUpdatePlayerFromDTOAndSyncRelations')]
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
        $this->clubTable = (new Club())->getTable();
        $this->playerTable = (new Player())->getTable();
        $this->brawlerTable = (new Brawler())->getTable();
        $this->accessoryTable = (new Accessory())->getTable();
        $this->gearTable = (new Gear())->getTable();
        $this->starPowerTable = (new StarPower())->getTable();
        $this->playerBrawlerTable = (new PlayerBrawler())->getTable();
        $this->playerDTOData = static::providePlayerDTOData();
        $this->playerModelData = static::providePlayerModelData();
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
        $this->assertPlayerEloquentModelsAreEqual($memberCreated, $memberFound);
    }

    #[Test]
    #[TestDox('Fetch the club member (by club id) with relations successfully.')]
    public function test_find_club_member_by_club_id(): void
    {
        $this->assertDatabaseEmpty($this->clubTable);
        $this->assertDatabaseEmpty($this->playerTable);

        /** @var Player $memberCreated */
        $memberCreated = Player::factory()->withClub()->create();

        $this->assertDatabaseHas($this->playerTable, [
            'id'      => $memberCreated->id,
            'club_id' => $memberCreated->club_id,
        ]);

        $this->assertDatabaseHas($this->clubTable, [
            'id' => $memberCreated->club_id,
        ]);

        $memberFound = $this->repository->findPlayer(['club_id' => $memberCreated->club_id]);

        $this->assertNotNull($memberFound);
        $this->assertInstanceOf(Player::class, $memberFound);
        $this->assertPlayerEloquentModelsAreEqual($memberCreated, $memberFound);
    }

    /**
     * @throws ValidationException
     */
    #[Test]
    #[TestDox('Create successfully the player model from attributes array.')]
    #[DataProvider('providePlayerModelData')]
    public function test_create_player_from_data_array(array $attributes): void
    {
        $this->assertDatabaseEmpty($this->playerTable);

        $player = $this->repository->createOrUpdatePlayerFromDataArray(attributes: $attributes);

        $this->assertDatabaseHas($this->playerTable, [
            'id' => $player->id,
        ]);

        $this->assertInstanceOf(Player::class, $player);
        $this->assertPlayerEloquentModelMatchesDataArray(
            player: $player,
            playerData: $attributes,
            checkRelations: false,
        );
    }

    /**
     * @throws ValidationException
     */
    #[Test]
    #[TestDox('Create successfully the player model from DTO.')]
    public function test_create_player_from_dto(): void
    {
        $playerData = array_shift($this->playerDTOData['player']);
        $playerDTO = PlayerDTO::fromArray($playerData);

        $this->assertDatabaseEmpty($this->playerTable);

        $player = $this->repository->createOrUpdatePlayerFromDTO($playerDTO);

        $this->assertDatabaseHas($this->playerTable, [
            'id' => $player->id,
            'tag' => $playerDTO->tag,
        ]);

        $this->assertInstanceOf(Player::class, $player);
        $this->assertPlayerEloquentModelMatchesPlayerDTO(
            playerDTO: $playerDTO,
            player: $player,
            checkRelations: false,
        );
    }

    #[Test]
    #[TestDox('Create successfully the player model with related entities from attributes array.')]
    #[DataProvider('providePlayerModelData')]
    public function test_create_player_with_relations_from_data_array(array $attributes): void
    {
        // todo
    }

    /**
     * @throws ValidationException
     * @throws JsonException
     */
    #[Test]
    #[TestDox('Create successfully the player model with related entities from DTO.')]
    public function test_create_player_with_relations_from_dto(): void
    {
        $playerData = array_shift($this->playerDTOData['player with club and 1 brawler']);
        $playerDTO = PlayerDTO::fromArray($playerData);

        $this->assertDatabaseEmpty($this->playerTable);
        $this->assertDatabaseEmpty($this->clubTable);
        $this->assertDatabaseEmpty($this->brawlerTable);
        $this->assertDatabaseEmpty($this->accessoryTable);
        $this->assertDatabaseEmpty($this->gearTable);
        $this->assertDatabaseEmpty($this->starPowerTable);

        $player = $this->repository->createOrUpdatePlayerFromDTOAndSyncRelations($playerDTO);

        $this->assertDatabaseCount($this->playerTable, 1);
        $this->assertDatabaseHas($this->playerTable, [
            'id' => $player->id,
            'tag' => $playerDTO->tag,
        ]);
        $this->assertDatabaseCount($this->clubTable, 1);
        $this->assertDatabaseHas($this->clubTable, [
            'id' => $player->club_id,
        ]);
        $this->assertDatabaseCount($this->brawlerTable, sizeof($playerDTO->playerBrawlers));
        $this->assertDatabaseCount($this->playerBrawlerTable, sizeof($playerDTO->playerBrawlers));

        foreach ($playerDTO->playerBrawlers as $playerBrawlerDTO) {
            $this->assertDatabaseHas($this->playerBrawlerTable, [
                'player_id'        => $player->id,
//                'brawler_id'       => ,
                'power'            => $playerBrawlerDTO->power,
                'rank'             => $playerBrawlerDTO->rank,
                'trophies'         => $playerBrawlerDTO->trophies,
                'highest_trophies' => $playerBrawlerDTO->highestTrophies,
            ]);

            $this->assertDatabaseHas($this->brawlerTable, [
                'ext_id' => $playerBrawlerDTO->extId,
                'name'   => $playerBrawlerDTO->name,
            ]);

            foreach ($playerBrawlerDTO->accessories as $playerBrawlerAccessoryDTO) {
                $this->assertDatabaseHas($this->accessoryTable, [
                    'ext_id' => $playerBrawlerAccessoryDTO->extId,
                    'name'   => $playerBrawlerAccessoryDTO->name,
                ]);
            }
            foreach ($playerBrawlerDTO->gears as $playerBrawlerGearDTO) {
                $this->assertDatabaseHas($this->gearTable, [
                    'ext_id' => $playerBrawlerGearDTO->extId,
                    'name'   => $playerBrawlerGearDTO->name,
                ]);
                // todo also check DB pivots, like playerBrawlerGear
//                    'level'  => $playerBrawlerGearDTO->level,
            }
            foreach ($playerBrawlerDTO->starPowers as $playerBrawlerStarPowerDTO) {
                $this->assertDatabaseHas($this->starPowerTable, [
                    'ext_id' => $playerBrawlerStarPowerDTO->extId,
                    'name'   => $playerBrawlerStarPowerDTO->name,
                ]);
            }
        }
        foreach ($player->brawlers as $brawler) {
            $brawler->load([
                'accessories',
                'gears',
                'starPowers',
            ]);
        }

        $this->assertPlayerEloquentModelMatchesPlayerDTO(
            playerDTO: $playerDTO,
            player: $player,
            checkRelations: true,
        );
    }
}
