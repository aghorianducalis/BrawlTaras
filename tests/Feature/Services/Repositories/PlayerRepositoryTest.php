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
#[CoversMethod(PlayerRepository::class, 'createOrUpdatePlayer')]
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
    }

    #[Test]
    #[TestDox('Fetch the club member with relations successfully.')]
    #[TestWith(['tag', '#abcd1234'])]
    #[TestWith(['name', 'Taras Shevchenko'])]
    public function test_find_club_member_by_criteria(string $property, int|string $value): void
    {
        $this->assertDatabaseMissing((new Player())->getTable(), [$property => $value]);

        /** @var Player $memberCreated */
        $memberCreated = Player::factory()->create(attributes: [$property => $value]);

        $this->assertDatabaseHas($memberCreated->getTable(), [
            'id' => $memberCreated->id,
            $property => $value,
        ]);

        $memberFound = $this->repository->findPlayer([$property => $value]);

        $this->assertNotNull($memberFound);
        $this->assertInstanceOf(Player::class, $memberFound);
        $this->assertEqualPlayerModels($memberCreated, $memberFound);
    }

    #[Test]
    #[TestDox('Create successfully the club member with related entities.')]
    public function test_create_club_member_with_relations(): void
    {
        $memberDTO = PlayerDTO::fromEloquentModel(Player::factory()->make());

        $this->assertDatabaseMissing((new Player())->getTable(), [
            'tag' => $memberDTO->tag,
        ]);

        $member = $this->repository->createOrUpdatePlayer($memberDTO);

        $this->assertDatabaseHas($member->getTable(), [
            'id' => $member->id,
            'tag' => $memberDTO->tag,
        ]);

        $this->assertPlayerDTOMatchesEloquentModel($memberDTO, $member);
    }

    #[Test]
    #[TestDox('Update successfully the member of the club with related entities.')]
    public function test_update_existing_member_of_club(): void
    {
        $member = Player::factory()->create();
        // create DTO to store the new data for club member with the same tag
        $memberDTO = PlayerDTO::fromEloquentModel(Player::factory()->make(attributes: [
            'tag' => $member->tag,
        ]));

        $memberUpdated = $this->repository->createOrUpdatePlayer($memberDTO);

        $this->assertDatabaseHas($memberUpdated->getTable(), [
            'id' => $memberUpdated->id,
            'tag' => $memberDTO->tag,
        ]);
        $this->assertDatabaseCount($memberUpdated->getTable(), 1);

        $this->assertPlayerDTOMatchesEloquentModel($memberDTO, $memberUpdated);
    }
}
