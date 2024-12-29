<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Repositories;

use App\API\DTO\Response\ClubDTO;
use App\Models\Club;
use App\Models\Player;
use App\Services\Repositories\ClubRepository;
use App\Services\Repositories\Contracts\ClubRepositoryInterface;
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
#[CoversClass(ClubRepository::class)]
#[CoversMethod(ClubRepository::class, 'findClub')]
#[CoversMethod(ClubRepository::class, 'createOrUpdateClub')]
#[UsesClass(Club::class)]
#[UsesClass(ClubFactory::class)]
#[UsesClass(Player::class)]
#[UsesClass(PlayerFactory::class)]
#[UsesClass(PlayerRepository::class)]
class ClubRepositoryTest extends TestCase
{
    use CreatesPlayers;
    use RefreshDatabase;

    private ClubRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ClubRepositoryInterface::class);
    }

    #[Test]
    #[TestDox('Fetch the club with relations successfully.')]
    #[TestWith(['tag', '#abcd1234'])]
    #[TestWith(['name', 'Ukraina Vavilon'])]
    public function test_find_club_by_criteria(string $property, int|string $value): void
    {
        $this->assertDatabaseMissing((new Club())->getTable(), [$property => $value]);

        /** @var Club $clubCreated */
        $clubCreated = Club::factory()->create(attributes: [$property => $value]);

        $this->assertDatabaseHas($clubCreated->getTable(), [
            'id' => $clubCreated->id,
            $property => $value,
        ]);

        $clubFound = $this->repository->findClub([$property => $value]);

        $this->assertEqualClubModels($clubCreated, $clubFound);
    }

    #[Test]
    #[TestDox('Create successfully the club with related entities.')]
    public function test_create_club_with_relations(): void
    {
        $clubToCreate = Club::factory()->afterMaking(fn(Club $club) => $club->setRelation('members', Player::factory()->for($club)->count(2)->make()))->make();
        $clubDTO = ClubDTO::fromEloquentModel($clubToCreate);

        $this->assertDatabaseMissing($clubToCreate->getTable(), [
            'tag' => $clubDTO->tag,
        ]);

        foreach ($clubToCreate->members as $player) {
            $this->assertDatabaseMissing($player->getTable(), [
                'tag' => $player->tag,
            ]);
        }

        $club = $this->repository->createOrUpdateClub($clubDTO);

        $this->assertDatabaseHas($club->getTable(), [
            'id' => $club->id,
            'tag' => $clubDTO->tag,
        ]);

        $this->assertClubModelMatchesDTO($club, $clubDTO);

        // assert that all members belong to club (DB relation check)
        foreach ($club->members as $player) {
            $this->assertDatabaseHas($player->getTable(), [
                'tag' => $player->tag,
                'club_id' => $club->id,
            ]);
        }
    }

    #[Test]
    #[TestDox('Update successfully the club with related entities.')]
    public function test_update_existing_club(): void
    {
        $club = Club::factory()->withMembers()->create();
        // list of all old members of that club
        $oldMembers = $club->members;
        // assert that all members belong to club (DB relation check)
        foreach ($oldMembers as $player) {
            $this->assertDatabaseHas($player->getTable(), [
                'id' => $player->id,
                'tag' => $player->tag,
                'club_id' => $club->id,
            ]);
        }

        // create DTO to store the new data for club with the same tag
        $clubDTO = ClubDTO::fromEloquentModel(Club::factory()->withMembers()->make(attributes: [
            'tag' => $club->tag,
        ]));

        $clubUpdated = $this->repository->createOrUpdateClub($clubDTO);

        $this->assertDatabaseHas($clubUpdated->getTable(), [
            'id' => $clubUpdated->id,
            'tag' => $clubDTO->tag,
        ]);

        $this->assertClubModelMatchesDTO($clubUpdated, $clubDTO);


        $club->load(['members']);

        // assert that all new members belong to club (DB relation check)
        foreach ($club->members as $player) {
            $this->assertDatabaseHas($player->getTable(), [
                'id' => $player->id,
                'tag' => $player->tag,
                'club_id' => $club->id,
            ]);
        }
        // assert that all old members have been detached from club
        foreach ($oldMembers as $player) {
            $this->assertDatabaseHas($player->getTable(), [
                'id' => $player->id,
                'tag' => $player->tag,
                'club_id' => null,
            ]);
        }
    }

    private function assertEqualClubModels(Club $clubExpected, ?Club $clubActual): void
    {
        $this->assertNotNull($clubActual);
        $this->assertInstanceOf(Club::class, $clubActual);
        $this->assertSame($clubExpected->id, $clubActual->id);
        $this->assertSame($clubExpected->tag, $clubActual->tag);
        $this->assertSame($clubExpected->name, $clubActual->name);
        $this->assertSame($clubExpected->description, $clubActual->description);
        $this->assertSame($clubExpected->type, $clubActual->type);
        $this->assertSame($clubExpected->badge_id, $clubActual->badge_id);
        $this->assertSame($clubExpected->required_trophies, $clubActual->required_trophies);
        $this->assertSame($clubExpected->trophies, $clubActual->trophies);
        $this->assertTrue($clubExpected->created_at->equalTo($clubActual->created_at));

        // compare the club's relations
        $clubExpected->load([
            'members',
        ]);
        $clubActual->load([
            'members',
        ]);

        $this->assertEquals(
            $clubExpected->members->toArray(),
            $clubActual->members->toArray()
        );
    }

    private function assertClubModelMatchesDTO(Club $club, ClubDTO $clubDTO): void
    {
        $this->assertSame($club->tag, $clubDTO->tag);
        $this->assertSame($club->name, $clubDTO->name);
        $this->assertSame($club->description, $clubDTO->description);
        $this->assertSame($club->type, $clubDTO->type);
        $this->assertSame($club->badge_id, $clubDTO->badgeId);
        $this->assertSame($club->required_trophies, $clubDTO->requiredTrophies);
        $this->assertSame($club->trophies, $clubDTO->trophies);

        $club->load(['members']);

        foreach ($clubDTO->members as $i => $playerDTO) {
            $this->assertPlayerModelMatchesDTO($club->members->get($i), $playerDTO);
        }
    }
}
