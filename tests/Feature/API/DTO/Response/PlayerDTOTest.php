<?php

declare(strict_types=1);

namespace Tests\Feature\API\DTO\Response;

use App\API\DTO\Response\PlayerDTO;
use App\API\Exceptions\InvalidDTOException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;
use Tests\Traits\TestPlayers;

#[Group('DTO')]
#[CoversClass(PlayerDTO::class)]
#[CoversMethod(PlayerDTO::class, 'toArray')]
#[CoversMethod(PlayerDTO::class, 'toJson')]
#[CoversMethod(PlayerDTO::class, 'fromDataArray')]
#[CoversMethod(PlayerDTO::class, 'fromEloquentModel')]
class PlayerDTOTest extends TestCase
{
    use RefreshDatabase;
    use TestPlayers;

    #[Test]
    #[TestDox('Covers correct instantiation from a valid data array. Ensuring valid input is transformed correctly.')]
    #[DataProvider('providePlayerData')]
    public function test_fromDataArray_creates_valid_dto(array $playerData): void
    {
        $playerDTO = PlayerDTO::fromDataArray($playerData);

        $this->assertInstanceOf(PlayerDTO::class, $playerDTO);
        $this->assertPlayerDTOMatchesDataArray($playerDTO, $playerData);
    }

    #[Test]
    #[TestDox('Ensuring invalid input without required fields is rejected.')]
    #[TestWith(['tag'])]
    #[TestWith(['name'])]
    #[TestWith(['nameColor'])]
    #[TestWith(['icon'])]
    #[TestWith(['trophies'])]
    public function test_fromDataArray_throws_exception_on_missing_required_data(string $property): void
    {
        $playerData = self::providePlayerData();
        unset($playerData[$property]);

        $this->expectException(InvalidDTOException::class);

        PlayerDTO::fromDataArray($playerData);
    }

    #[Test]
    #[TestDox('Ensuring invalid input is rejected.')]
    #[TestWith(['tag', null])]
    #[TestWith(['tag', ''])]
    #[TestWith(['tag', 123.45])]
    #[TestWith(['name', null])]
    #[TestWith(['name', ''])]
    #[TestWith(['name', 123.45])]
    #[TestWith(['nameColor', null])]
    #[TestWith(['nameColor', ''])]
    #[TestWith(['nameColor', 123.45])]
    #[TestWith(['icon', null])]
    #[TestWith(['icon', 123.45])]
    #[TestWith(['icon', 'string'])]
    #[TestWith(['icon', ['key'=> 'value']])]
    #[TestWith(['trophies', null])]
    #[TestWith(['trophies', 'string'])]
    #[TestWith(['highestTrophies', null])]
    #[TestWith(['highestTrophies', 'string'])]
    #[TestWith(['expLevel', null])]
    #[TestWith(['expLevel', 'string'])]
    #[TestWith(['expPoints', null])]
    #[TestWith(['expPoints', 'string'])]
    #[TestWith(['isQualifiedFromChampionshipChallenge', null])]
    #[TestWith(['isQualifiedFromChampionshipChallenge', 'string'])]
    #[TestWith(['isQualifiedFromChampionshipChallenge', 123.45])]
    #[TestWith(['soloVictories', null])]
    #[TestWith(['soloVictories', 'string'])]
    #[TestWith(['duoVictories', null])]
    #[TestWith(['duoVictories', 'string'])]
    #[TestWith(['3vs3Victories', null])]
    #[TestWith(['3vs3Victories', 'string'])]
    #[TestWith(['bestRoboRumbleTime', null])]
    #[TestWith(['bestRoboRumbleTime', 'string'])]
    #[TestWith(['bestTimeAsBigBrawler', null])]
    #[TestWith(['bestTimeAsBigBrawler', 'string'])]
    public function test_fromDataArray_throws_exception_on_invalid_data(string $property, mixed $value): void
    {
        $playerData = array_merge(self::providePlayerData(), [$property => $value]);

        $this->expectException(InvalidDTOException::class);

        PlayerDTO::fromDataArray($playerData);
    }

    #[Test]
    #[TestDox('Covers correct instantiation from a model.')]
    public function test_fromEloquentModel_creates_valid_dto(): void
    {
        $player = $this->createPlayer();

        $playerDTO = PlayerDTO::fromEloquentModel($player);

        $this->assertInstanceOf(PlayerDTO::class, $playerDTO);
        $this->assertPlayerDTOMatchesEloquentModel($playerDTO, $player);
    }

    #[Test]
    #[TestDox('Converts DTO to an array correctly. Covers proper serialization to array.')]
    #[DataProvider('providePlayerData')]
    public function test_toArray_returns_correct_structure(array $playerData): void
    {
        $playerDTO = PlayerDTO::fromDataArray($playerData);

        $playerDTOArray = $playerDTO->toArray();

        $this->assertIsArray($playerDTOArray);
        $this->assertEquals($playerData, $playerDTOArray);
    }

    #[Test]
    #[TestDox('Serializes DTO to JSON. Ensuring JSON encoding works correctly.')]
    #[DataProvider('providePlayerData')]
    public function test_toJson_encodes_correctly(array $playerData): void
    {
        $playerDTO = PlayerDTO::fromDataArray($playerData);

        try {
            $json = $playerDTO->toJson();
            $this->assertIsString($json);
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $this->assertEquals($playerData, $decoded);
        } catch (JsonException $e) {
            $this->fail("JSON encoding failed: " . $e->getMessage());
        }
    }

    public static function providePlayerData(): array
    {
        return [
            'player' => [
                [
                    'tag' => '#12345',
                    'name' => 'Test Player 1',
                    'nameColor' => '#fff000',
                    'icon' => ['id' => 123],
                    'trophies' => 50000,
                    'highestTrophies' => 50123,
                    'expLevel' => 45,
                    'expPoints' => 1000,
                    'isQualifiedFromChampionshipChallenge' => true,
                    'soloVictories' => 3000,
                    'duoVictories' => 2500,
                    '3vs3Victories' => 3300,
                    'bestRoboRumbleTime' => 99,
                    'bestTimeAsBigBrawler' => 60,
//                    'role' => null,
//                    'club' => null,
//                    'brawlers' => null,
                ],
            ],
//            'player with club' => [
//                [
//                    'tag' => '#12345',
//                    'name' => 'Test Player 2',
//                    'nameColor' => '#fff000',
//                    'icon' => ['id' => 123],
//                    'trophies' => 50000,
//                    'highestTrophies' => 50123,
//                    'expLevel' => 45,
//                    'expPoints' => 1000,
//                    'isQualifiedFromChampionshipChallenge' => true,
//                    'soloVictories' => 3000,
//                    'duoVictories' => 2500,
//                    '3vs3Victories' => 3300,
//                    'bestRoboRumbleTime' => 99,
//                    'bestTimeAsBigBrawler' => 60,
//                    'role' => Club::CLUB_MEMBER_ROLES[1],
//                    'club' => [
//                        'tag' => '#777',
//                        'name' => 'Test Club with 1 member',
//                    ],
//                ],
//            ],
//            'player without brawlers' => [
//            ],
//            'player with 1 brawler' => [
//            ],
        ];
    }
}
