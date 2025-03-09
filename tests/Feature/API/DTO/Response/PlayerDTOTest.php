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
#[CoversMethod(PlayerDTO::class, 'fromArray')]
#[CoversMethod(PlayerDTO::class, 'fromEloquentModel')]
class PlayerDTOTest extends TestCase
{
    use RefreshDatabase;
    use TestPlayers;

    #[Test]
    #[TestDox('Covers correct instantiation from a valid data array. Ensuring valid input is transformed correctly.')]
    #[DataProvider('providePlayerDTOData')]
    public function test_fromDataArray_creates_valid_dto(array $playerData): void
    {
        $playerDTO = PlayerDTO::fromArray($playerData);

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
    #[TestWith(['highestTrophies'])]
    #[TestWith(['expLevel'])]
    #[TestWith(['expPoints'])]
    #[TestWith(['isQualifiedFromChampionshipChallenge'])]
    #[TestWith(['soloVictories'])]
    #[TestWith(['duoVictories'])]
    #[TestWith(['3vs3Victories'])]
    #[TestWith(['bestRoboRumbleTime'])]
    #[TestWith(['bestTimeAsBigBrawler'])]
    #[TestWith(['club'])]
    #[TestWith(['brawlers'])]
    public function test_fromDataArray_throws_exception_on_missing_required_data(string $property): void
    {
        $playerDataArray = self::providePlayerDTOData();
        $key = array_rand($playerDataArray);
        $playerData = $playerDataArray[$key][0];
        unset($playerData[$property]);

        $this->expectException(InvalidDTOException::class);

        PlayerDTO::fromArray($playerData);
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
    #[TestWith(['club', null])]
    #[TestWith(['club', 'string'])]
    #[TestWith(['club', 123.45])]
    #[TestWith(['brawlers', null])]
    #[TestWith(['brawlers', 'string'])]
    #[TestWith(['brawlers', 123.45])]
    public function test_fromDataArray_throws_exception_on_invalid_data(string $property, mixed $value): void
    {
        $playerData = array_merge(self::providePlayerDTOData(), [$property => $value]);

        $this->expectException(InvalidDTOException::class);

        PlayerDTO::fromArray($playerData);
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
    #[DataProvider('providePlayerDTOData')]
    public function test_toArray_returns_correct_structure(array $playerData): void
    {
        $playerDTO = PlayerDTO::fromArray($playerData);

        $playerDTOArray = $playerDTO->toArray();

        $this->assertIsArray($playerDTOArray);
        $this->assertEquals($playerData, $playerDTOArray);
    }

    #[Test]
    #[TestDox('Serializes DTO to JSON. Ensuring JSON encoding works correctly.')]
    #[DataProvider('providePlayerDTOData')]
    public function test_toJson_encodes_correctly(array $playerData): void
    {
        $playerDTO = PlayerDTO::fromArray($playerData);

        try {
            $json = $playerDTO->toJson();
            $this->assertIsString($json);
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $this->assertEquals($playerData, $decoded);
        } catch (JsonException $e) {
            $this->fail("JSON encoding failed: " . $e->getMessage());
        }
    }
}
