<?php

declare(strict_types=1);

namespace Tests\Feature\API\DTO\Response;

use App\API\DTO\Response\ClubDTO;
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
use Tests\Traits\TestClubs;

#[Group('DTO')]
#[CoversClass(ClubDTO::class)]
#[CoversMethod(ClubDTO::class, 'toArray')]
#[CoversMethod(ClubDTO::class, 'toJson')]
#[CoversMethod(ClubDTO::class, 'fromDataArray')]
#[CoversMethod(ClubDTO::class, 'fromEloquentModel')]
class ClubDTOTest extends TestCase
{
    use RefreshDatabase;
    use TestClubs;

    #[Test]
    #[TestDox('Covers correct instantiation from a valid data array. Ensuring valid input is transformed correctly.')]
    #[DataProvider('provideClubData')]
    public function test_fromDataArray_creates_valid_dto(array $clubData): void
    {
        $clubDTO = ClubDTO::fromDataArray($clubData);

        $this->assertInstanceOf(ClubDTO::class, $clubDTO);
        $this->assertClubDTOMatchesDataArray($clubDTO, $clubData);
    }

    #[Test]
    #[TestDox('Ensuring invalid input without required fields is rejected.')]
    #[TestWith(['tag'])]
    public function test_fromDataArray_throws_exception_on_missing_required_data(string $property): void
    {
        $clubData = self::provideClubData();
        unset($clubData[$property]);

        $this->expectException(InvalidDTOException::class);

        ClubDTO::fromDataArray($clubData);
    }

    #[Test]
    #[TestDox('Ensuring invalid input is rejected.')]
    #[TestWith(['tag', null])]
    #[TestWith(['tag', ''])]
    #[TestWith(['tag', 123.45])]
    #[TestWith(['name', null])]
    #[TestWith(['name', ''])]
    #[TestWith(['name', 123.45])]
    #[TestWith(['description', null])]
    #[TestWith(['description', ''])]
    #[TestWith(['description', 123.45])]
    #[TestWith(['type', null])]
    #[TestWith(['type', ''])]
    #[TestWith(['type', 123.45])]
    #[TestWith(['badgeId', null])]
    #[TestWith(['badgeId', 'string'])]
    #[TestWith(['requiredTrophies', null])]
    #[TestWith(['requiredTrophies', 'string'])]
    #[TestWith(['trophies', null])]
    #[TestWith(['trophies', 'string'])]
    #[TestWith(['members', 123.45])]
    #[TestWith(['members', 'string'])]
    public function test_fromDataArray_throws_exception_on_invalid_data(string $property, mixed $value): void
    {
        $clubData = array_merge(self::provideClubData(), [$property => $value]);

        $this->expectException(InvalidDTOException::class);

        ClubDTO::fromDataArray($clubData);
    }

    #[Test]
    #[TestDox('Covers correct instantiation from a model.')]
    public function test_fromEloquentModel_creates_valid_dto(): void
    {
        $club = $this->createClubWithMembers();

        $clubDTO = ClubDTO::fromEloquentModel($club);

        $this->assertInstanceOf(ClubDTO::class, $clubDTO);
        $this->assertClubDTOMatchesEloquentModel($clubDTO, $club);
    }

    #[Test]
    #[TestDox('Converts DTO to an array correctly. Covers proper serialization to array.')]
    #[DataProvider('provideClubData')]
    public function test_toArray_returns_correct_structure(array $clubData): void
    {
        $clubDTO = ClubDTO::fromDataArray($clubData);

        $clubDTOArray = $clubDTO->toArray();

        $this->assertIsArray($clubDTOArray);
        $this->assertEquals($clubData, $clubDTOArray);
    }

    #[Test]
    #[TestDox('Serializes DTO to JSON. Ensuring JSON encoding works correctly.')]
    #[DataProvider('provideClubData')]
    public function test_toJson_encodes_correctly(array $clubData): void
    {
        $clubDTO = ClubDTO::fromDataArray($clubData);

        try {
            $json = $clubDTO->toJson();
            $this->assertIsString($json);
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $this->assertEquals($clubData, $decoded);
        } catch (JsonException $e) {
            $this->fail("JSON encoding failed: " . $e->getMessage());
        }
    }
}
