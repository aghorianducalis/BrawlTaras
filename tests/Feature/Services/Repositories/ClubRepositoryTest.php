<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Repositories;

use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\ClubMemberDTO;
use App\Models\Club;
use App\Models\Player;
use App\Services\Repositories\ClubRepository;
use App\Services\Repositories\Contracts\ClubRepositoryInterface;
use Database\Factories\ClubFactory;
use Database\Factories\PlayerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;
use Tests\Traits\TestClubs;
use Tests\Traits\TestPlayers;

#[Group('Repositories')]
#[CoversClass(ClubRepository::class)]
#[CoversMethod(ClubRepository::class, 'findClub')]
#[CoversMethod(ClubRepository::class, 'createOrUpdateClubFromArray')]
#[CoversMethod(ClubRepository::class, 'createOrUpdateClubFromDTO')]
#[CoversMethod(ClubRepository::class, 'createOrUpdateClubFromTag')]
#[CoversMethod(ClubRepository::class, 'syncClubMembers')]
#[UsesClass(Club::class)]
#[UsesClass(ClubDTO::class)]
#[UsesClass(ClubFactory::class)]
#[UsesClass(Player::class)]
#[UsesClass(ClubMemberDTO::class)]
#[UsesClass(PlayerFactory::class)]
class ClubRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use TestClubs;
    use TestPlayers;

    private ClubRepository $repository;

    private string $clubTable;

    private string $playerTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(ClubRepositoryInterface::class);
        $this->clubTable = (new Club())->getTable();
        $this->playerTable = (new Player())->getTable();
    }

    #[Test]
    #[TestDox('Fetch the club with relations successfully.')]
    #[TestWith(['id', '123'])]
    #[TestWith(['tag', '#abcd1234'])]
    #[TestWith(['name', 'Ukraina Vavilon'])]
    public function test_find_club_by_criteria(string $property, int|string $value): void
    {
        $this->assertDatabaseEmpty($this->clubTable);

        /** @var Club $clubCreated */
        $clubCreated = Club::factory()->create(attributes: [$property => $value]);

        $this->assertDatabaseHas($this->clubTable, [
            'id' => $clubCreated->id,
            $property => $value,
        ]);

        $clubFound = $this->repository->findClub([$property => $value]);

        $this->assertNotNull($clubFound);
        $this->assertInstanceOf(Club::class, $clubFound);
        $this->assertEqualClubModels($clubCreated, $clubFound);
    }

    #[Test]
    #[TestDox('Create successfully the club with related players.')]
    #[DataProvider('provideClubModelData')]
    public function test_create_club_from_attributes_array(array $attributes): void
    {
        $this->assertDatabaseEmpty($this->clubTable);
//        $rules = $this->repository::getClubRules();

        $club = $this->repository->createOrUpdateClubFromArray($attributes);

        $this->assertDatabaseHas($this->clubTable, [
            'id' => $club->id,
            'tag' => $club->tag,
        ]);
        $club->refresh();
        $club->load(['members']);

        $this->assertClubEloquentModelMatchesDataArray(
            club: $club,
            clubData: $attributes,
            checkMembers: false,
        );
    }

    public function test_create_club_from_dto(): void
    {
        self::assertTrue(true);
    }

    public function test_create_club_from_tag(): void
    {
        self::assertTrue(true);
    }


    #[Test]
    #[TestDox('Create successfully the club with related players.')]
    #[DataProvider('provideClubDTOData')]
    public function test_create_club_with_members(array $clubData): void
    {
        $clubDTO = ClubDTO::fromDataArray($clubData);

        $this->assertDatabaseMissing($this->clubTable, [
            'tag' => $clubDTO->tag,
        ]);

        foreach ($clubDTO->members ?? [] as $player) {
            $this->assertDatabaseMissing($this->playerTable, [
                'tag' => $player->tag,
            ]);
        }

        $club = $this->repository->createOrUpdateClubFromDTOAndSyncClubMembers($clubDTO);

        $this->assertDatabaseHas($this->clubTable, [
            'id' => $club->id,
            'tag' => $club->tag,
        ]);

        // assert that all members belong to club (DB relation check)
        foreach ($club->members as $player) {
            $this->assertDatabaseHas($this->playerTable, [
                'id' => $player->id,
                'tag' => $player->tag,
                'club_id' => $club->id,
                'club_role' => $player->club_role,
            ]);
        }

        $this->assertClubDTOMatchesEloquentModel($clubDTO, $club);
    }

    #[Test]
    #[TestDox('Update successfully the club with related players.')]
    #[DataProvider('provideClubDTOData')]
    public function test_update_existing_club_with_members(array $clubData): void
    {
        $club = $this->createClubWithMembers();
        // list of all old members of that club
        $oldMembers = $club->members;

        // assert that all members belong to club (DB relation check)
        foreach ($oldMembers as $player) {
            $this->assertDatabaseHas($this->playerTable, [
                'id' => $player->id,
                'tag' => $player->tag,
                'club_id' => $club->id,
                'club_role' => $player->club_role,
            ]);
        }

        // create DTO to store the new data for club with the same tag
        $clubData['tag'] = $club->tag;
        $clubDTO = ClubDTO::fromDataArray($clubData);

        $clubUpdated = $this->repository->createOrUpdateClubFromDTOAndSyncClubMembers($clubDTO);

        // ensure there is only 1 club stored in DB
        $this->assertDatabaseCount($this->clubTable, 1);
        $this->assertDatabaseHas($this->clubTable, [
            'id' => $club->id,
            'tag' => $club->tag,
        ]);

        // if new members have been set
        if ($clubDTO->members) {
            // assert that all old members have been detached from club
            foreach ($oldMembers as $player) {
                $this->assertDatabaseHas($this->playerTable, [
                    'id' => $player->id,
                    'tag' => $player->tag,
                    'club_id' => null,
                    'club_role' => null,
                ]);
            }

            // assert that all new members belong to club
            foreach ($clubUpdated->members as $i => $player) {
                $this->assertDatabaseHas($this->playerTable, [
                    'id' => $player->id,
                    'tag' => $player->tag,
                    'club_id' => $club->id,
                    'club_role' => $player->club_role,
                ]);
                $this->assertClubMemberDTOMatchesEloquentModel($clubDTO->members[$i], $player);
            }
        }
    }

    #[Test]
    #[TestDox('Sync club players successfully. After sync the old members are detached from club and new members attached.')]
    public function test_sync_club_members(): void
    {
        $club = $this->createClubWithMembers();

        $this->assertDatabaseHas($this->clubTable, [
            'id' => $club->id,
            'tag' => $club->tag,
        ]);

        $oldMembers = $club->members;

        // assert that all members belong to club (DB relation check)
        foreach ($oldMembers as $player) {
            $this->assertDatabaseHas($this->playerTable, [
                'id' => $player->id,
                'tag' => $player->tag,
                'club_id' => $club->id,
                'club_role' => $player->club_role,
            ]);
        }

        /** @var ClubMemberDTO[] $newMemberDTOs */
        $newMemberDTOs = Player::factory()
            ->count(5)
            ->make(['club_role' => Arr::random(Club::CLUB_MEMBER_ROLES)])
            ->transform(fn (Player $player) => ClubMemberDTO::fromEloquentModel($player))
            ->all();

        $club = $this->repository->syncClubMembers($club, $newMemberDTOs);

        // assert that all old members have been detached from club
        foreach ($oldMembers as $player) {
            $this->assertDatabaseHas($this->playerTable, [
                'id' => $player->id,
                'tag' => $player->tag,
                'club_id' => null,
                'club_role' => null,
            ]);
        }

        $club->load(['members']);

        // assert that all new members belong to club
        foreach ($club->members as $i => $player) {
            $this->assertDatabaseHas($this->playerTable, [
                'id' => $player->id,
                'tag' => $player->tag,
                'club_id' => $club->id,
                'club_role' => $player->club_role,
            ]);
            $this->assertNotNull($player->club_role);
            $this->assertEquals($club->id, $player->club_id);
            $this->assertClubMemberDTOMatchesEloquentModel($newMemberDTOs[$i], $player);
        }
    }
}
