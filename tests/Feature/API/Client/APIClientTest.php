<?php

declare(strict_types=1);

namespace Tests\Feature\API\Client;

use App\API\Client\APIClient;
use App\API\DTO\Request\BrawlerDTO as BrawlerRequestDTO;
use App\API\DTO\Request\BrawlerListDTO as BrawlerListRequestDTO;
use App\API\DTO\Request\EventRotationListDTO;
use App\API\DTO\Response\BrawlerDTO as BrawlerResponseDTO;
use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\EventRotationDTO;
use App\API\DTO\Response\ClubPlayerDTO;
use App\API\DTO\Response\PlayerBrawlerDTO;
use App\API\DTO\Response\PlayerDTO;
use App\API\Enums\APIEndpoints;
use App\API\Exceptions\InvalidDTOException;
use App\API\Exceptions\ResponseException;
use App\Models\Brawler;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventRotation;
use App\Models\Player;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;
use Tests\Traits\CreatesBrawlers;
use Tests\Traits\CreatesPlayers;

/**
 * Run these tests with PHPUnit to verify the behavior of APIClient.
 *
 * Notes
 *
 * 1. Mocking GuzzleClient:
 * Each request is mocked with specific expectations to match the HTTP method, endpoint, and headers.
 *
 * 2. Error Handling:
 * The test cases ensure exceptions are properly thrown and handled.
 * Ensure ResponseException is thrown when a Guzzle exception occurs.
 * Ensure ResponseException is thrown for invalid JSON responses.
 * Ensure InvalidDTOException is thrown when DTO creation fails.
 *
 * 3. Validation:
 * Assert that the correct DTO objects are returned and API endpoints are constructed as expected.
 */
#[Group('API client')]
#[CoversClass(APIClient::class)]
#[CoversMethod(APIClient::class, 'makeRequest')]
#[CoversMethod(APIClient::class, 'getBrawler')]
#[CoversMethod(APIClient::class, 'getBrawlers')]
#[CoversMethod(APIClient::class, 'getClubByTag')]
#[CoversMethod(APIClient::class, 'getClubMembers')]
#[CoversMethod(APIClient::class, 'getEventsRotation')]
class APIClientTest extends TestCase
{
    use CreatesBrawlers;
    use CreatesPlayers;
    use RefreshDatabase;

    private APIClient $apiClient;

    private MockInterface|HttpClient $httpClientMock;

    private string $apiBaseURI = 'https://api.example.com';

    private string $apiKey = 'test-api-key';

    protected function setUp(): void
    {
        parent::setUp();

        Log::shouldReceive('error')->andReturn();

        $this->httpClientMock = Mockery::mock(HttpClient::class);
        $this->apiClient = new APIClient(
            httpClient: $this->httpClientMock,
            apiBaseURI: $this->apiBaseURI,
            apiKey: $this->apiKey
        );
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /*
     * API endpoints for brawlers.
     */

    /**
     * @return void
     * @throws JsonException|ResponseException
     */
    #[Test]
    public function it_fetches_brawler_by_id_successfully(): void
    {
        $apiEndpoint = APIEndpoints::BrawlerById;
        $brawlerExpected = $this->createBrawlerWithRelations();
        $mockResponse = new Response(200, [], BrawlerRequestDTO::fromBrawlerModel($brawlerExpected)->toJson());
        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['brawler_id' => $brawlerExpected->ext_id]),
                Mockery::on(function ($options) {
                    return $options['headers']['Authorization'] === "Bearer $this->apiKey";
                })
            )
            ->andReturn($mockResponse);

        $brawlerDTOFetched = $this->apiClient->getBrawler($brawlerExpected->ext_id);

        $this->assertInstanceOf(BrawlerResponseDTO::class, $brawlerDTOFetched);
        $this->assertBrawlerModelMatchesDTO($brawlerExpected, $brawlerDTOFetched);
    }

    #[Test]
    #[TestWith([123])]
    public function get_brawler_by_id_throws_response_exception_on_request_failure(int $brawlerExternalId): void
    {
        $apiEndpoint = APIEndpoints::BrawlerById;
        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['brawler_id' => $brawlerExternalId]),
                Mockery::any()
            )
            ->andThrow(new RequestException('Request failed', new Request($apiEndpoint->method(), '')));

        $this->expectException(ResponseException::class);

        $this->apiClient->getBrawler($brawlerExternalId);
    }

    #[Test]
    #[TestWith([123])]
    public function get_brawler_by_id_throws_response_exception_on_invalid_json(int $brawlerExternalId): void
    {
        $apiEndpoint = APIEndpoints::BrawlerById;
        $mockResponse = new Response(200, [], 'invalid-json');

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['brawler_id' => $brawlerExternalId]),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(ResponseException::class);

        $this->apiClient->getBrawler($brawlerExternalId);
    }

    /**
     * @throws ResponseException
     */
    #[Test]
    #[TestWith([123])]
    public function get_brawler_by_id_throws_invalid_dto_exception(int $brawlerExternalId): void
    {
        $apiEndpoint = APIEndpoints::BrawlerById;
        $mockResponse = new Response(200, [], json_encode(['invalid' => 'data']));

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['brawler_id' => $brawlerExternalId]),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(InvalidDTOException::class);

        $this->apiClient->getBrawler($brawlerExternalId);
    }

    /**
     * @throws ResponseException
     * @throws JsonException
     */
    #[Test]
    #[TestDox('Fetch the list of all brawlers with related accessories and star powers successfully')]
    public function it_fetches_all_brawlers_successfully(): void
    {
        $apiEndpoint = APIEndpoints::Brawlers;
        $brawlersExpected = array_map(fn () => $this->createBrawlerWithRelations(), range(1, 2));
        $mockResponse = new Response(200, [], BrawlerListRequestDTO::fromListOfBrawlerModels($brawlersExpected)->toJson());

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI),
                Mockery::on(function ($options) {
                    return $options['headers']['Authorization'] === "Bearer $this->apiKey";
                })
            )
            ->andReturn($mockResponse);

        $brawlerDTOs = $this->apiClient->getBrawlers();

        $this->assertIsArray($brawlerDTOs);
        $this->assertCount(sizeof($brawlersExpected), $brawlerDTOs);

        foreach ($brawlerDTOs as $i => $brawlerDTO) {
            $this->assertInstanceOf(BrawlerResponseDTO::class, $brawlerDTO);
            $this->assertBrawlerModelMatchesDTO($brawlersExpected[$i], $brawlerDTO);
        }
    }

    #[Test]
    public function get_all_brawlers_throws_response_exception_on_request_failure(): void
    {
        $apiEndpoint = APIEndpoints::Brawlers;
        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI),
                Mockery::any()
            )
            ->andThrow(new RequestException('Request failed', new Request($apiEndpoint->method(), '')));

        $this->expectException(ResponseException::class);

        $this->apiClient->getBrawlers();
    }

    #[Test]
    public function get_all_brawlers_throws_response_exception_on_invalid_json(): void
    {
        $apiEndpoint = APIEndpoints::Brawlers;
        $mockResponse = new Response(200, [], 'invalid-json');

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(ResponseException::class);

        $this->apiClient->getBrawlers();
    }

    /**
     * @throws ResponseException
     */
    #[Test]
    public function get_all_brawlers_throws_invalid_dto_exception(): void
    {
        $apiEndpoint = APIEndpoints::Brawlers;
        $mockResponse = new Response(200, [], json_encode(['invalid' => 'data']));

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(InvalidDTOException::class);

        $this->apiClient->getBrawlers();
    }

    /*
     * End of API endpoints for brawlers.
     */

    /*
     * API endpoints for events.
     */

    /**
     * @throws ResponseException
     * @throws JsonException
     */
    #[Test]
    #[TestDox('Fetch the list of all brawlers with related accessories and star powers successfully')]
    public function it_fetches_events_rotation_successfully(): void
    {
        $rotations = collect(range(1, 2))->map(fn () => $this->createEventsRotation());
        $apiEndpoint = APIEndpoints::EventRotation;
        $mockResponse = new Response(200, [], EventRotationListDTO::fromListOfEloquentModels($rotations->all())->toJson());

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI),
                Mockery::on(function ($options) {
                    return $options['headers']['Authorization'] === "Bearer $this->apiKey";
                })
            )
            ->andReturn($mockResponse);

        $rotationDTOs = $this->apiClient->getEventsRotation();

        $this->assertIsArray($rotationDTOs);
        $this->assertCount(sizeof($rotations), $rotationDTOs);

        foreach ($rotationDTOs as $i => $rotationDTO) {
            $this->assertInstanceOf(EventRotationDTO::class, $rotationDTO);
            /** @var EventRotation $rotationExpected */
            $rotationExpected = $rotations->get($i);

            $this->assertSame($rotationExpected->start_time->format('Ymd\THis.u\Z'), $rotationDTO->start_time);
            $this->assertSame($rotationExpected->end_time->format('Ymd\THis.u\Z'), $rotationDTO->end_time);
            $this->assertSame($rotationExpected->slot->position, $rotationDTO->slot);
            $this->assertSame($rotationExpected->event->ext_id, $rotationDTO->event->id);
            $this->assertSame($rotationExpected->event->map->name, $rotationDTO->event->map);
            $this->assertSame($rotationExpected->event->mode->name, $rotationDTO->event->mode);
            $this->assertIsArray($rotationDTO->event->modifiers);
            $this->assertCount($rotationExpected->event->modifiers->count(), $rotationDTO->event->modifiers);
            $this->assertSame($rotationExpected->event->modifiers->all(), $rotationDTO->event->modifiers);
        }
    }

    #[Test]
    public function get_events_rotation_throws_response_exception_on_request_failure(): void
    {
        $apiEndpoint = APIEndpoints::EventRotation;
        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI),
                Mockery::any()
            )
            ->andThrow(new RequestException('Request failed', new Request($apiEndpoint->method(), '')));

        $this->expectException(ResponseException::class);

        $this->apiClient->getEventsRotation();
    }

    #[Test]
    public function get_events_rotation_throws_response_exception_on_invalid_json(): void
    {
        $apiEndpoint = APIEndpoints::EventRotation;
        $mockResponse = new Response(200, [], 'invalid-json');

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(ResponseException::class);

        $this->apiClient->getEventsRotation();
    }

    /**
     * @throws ResponseException
     */
    #[Test]
    public function get_events_rotation_throws_invalid_dto_exception(): void
    {
        $apiEndpoint = APIEndpoints::EventRotation;
        $mockResponse = new Response(200, [], json_encode([['invalid' => 'data']]));

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(InvalidDTOException::class);

        $this->apiClient->getEventsRotation();
    }

    private function createEventsRotation(
        array|callable $attributes = [],
    ) : EventRotation {
        return EventRotation::factory()
            ->for(Event::factory()->withModifiers(count: 3))
            ->create($attributes);
    }

    /*
     * End of API endpoints for events.
     */

    /*
     * API endpoints for clubs.
     */

    /**
     * @return void
     * @throws ResponseException
     * @throws JsonException
     */
    #[Test]
    #[TestDox('Fetch a single club with its members successfully')]
    public function it_fetches_club_by_tag_successfully(): void
    {
        $club = $this->createClubWithMembers();
        $apiEndpoint = APIEndpoints::ClubByTag;
        $mockResponse = new Response(200, [], json_encode(ClubDTO::fromEloquentModel($club), JSON_THROW_ON_ERROR));

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['club_tag' => $club->tag]),
                Mockery::on(function ($options) {
                    return $options['headers']['Authorization'] === "Bearer $this->apiKey";
                })
            )
            ->andReturn($mockResponse);

        $clubDTO = $this->apiClient->getClubByTag($club->tag);

        $this->assertInstanceOf(ClubDTO::class, $clubDTO);
        $this->assertSame($club->tag, $clubDTO->tag);
        $this->assertSame($club->name, $clubDTO->name);
        $this->assertSame($club->description, $clubDTO->description);
        $this->assertSame($club->type, $clubDTO->type);
        $this->assertSame($club->badge_id, $clubDTO->badgeId);
        $this->assertSame($club->required_trophies, $clubDTO->requiredTrophies);
        $this->assertSame($club->trophies, $clubDTO->trophies);
        $this->assertIsArray($clubDTO->members);
        $this->assertCount($club->members->count(), $clubDTO->members);

        foreach ($clubDTO->members as $i => $memberDTO) {
            $this->assertInstanceOf(ClubPlayerDTO::class, $memberDTO);
            /** @var Player $member */
            $member = $club->members->get($i);

            $this->assertPlayerModelMatchesDTO(player: $member, playerDTO: $memberDTO);
        }
    }

    #[Test]
    #[TestWith(['club_tag_123'])]
    public function get_club_by_tag_throws_response_exception_on_request_failure(string $clubTag): void
    {
        $apiEndpoint = APIEndpoints::ClubByTag;
        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['club_tag' => $clubTag]),
                Mockery::any()
            )
            ->andThrow(new RequestException('Request failed', new Request($apiEndpoint->method(), '')));

        $this->expectException(ResponseException::class);

        $this->apiClient->getClubByTag($clubTag);
    }

    #[Test]
    #[TestWith(['club_tag_123'])]
    public function get_club_by_tag_throws_response_exception_on_invalid_json(string $clubTag): void
    {
        $apiEndpoint = APIEndpoints::ClubByTag;
        $mockResponse = new Response(200, [], 'invalid-json');

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['club_tag' => $clubTag]),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(ResponseException::class);

        $this->apiClient->getClubByTag($clubTag);
    }

    /**
     * @throws ResponseException
     */
    #[Test]
    #[TestWith(['club_tag_123'])]
    public function get_club_by_tag_throws_invalid_dto_exception(string $clubTag): void
    {
        $apiEndpoint = APIEndpoints::ClubByTag;
        $mockResponse = new Response(200, [], json_encode([['invalid' => 'data']]));

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['club_tag' => $clubTag]),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(InvalidDTOException::class);

        $this->apiClient->getClubByTag($clubTag);
    }

    /**
     * @throws ResponseException
     * @throws JsonException
     */
    #[Test]
    #[TestDox('Fetch a list of club members successfully.')]
    public function it_fetches_club_members_successfully(): void
    {
        $club = $this->createClubWithMembers();
        $apiEndpoint = APIEndpoints::ClubMembers;
        $mockResponse = new Response(
            200,
            [],
            json_encode(['items' => ClubPlayerDTO::fromEloquentModels($club->members->all())], JSON_THROW_ON_ERROR),
        );

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['club_tag' => $club->tag]),
                Mockery::on(function ($options) {
                    return $options['headers']['Authorization'] === "Bearer $this->apiKey";
                })
            )
            ->andReturn($mockResponse);

        $memberDTOs = $this->apiClient->getClubMembers($club->tag);

        $this->assertIsArray($memberDTOs);
        $this->assertCount($club->members->count(), $memberDTOs);

        foreach ($memberDTOs as $i => $memberDTO) {
            $this->assertInstanceOf(ClubPlayerDTO::class, $memberDTO);
            /** @var Player $member */
            $member = $club->members->get($i);

            $this->assertPlayerModelMatchesDTO(player: $member, playerDTO: $memberDTO);
        }
    }

    #[Test]
    #[TestWith(['club_tag_123'])]
    public function get_club_members_throws_response_exception_on_request_failure(string $clubTag): void
    {
        $apiEndpoint = APIEndpoints::ClubMembers;
        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['club_tag' => $clubTag]),
                Mockery::any()
            )
            ->andThrow(new RequestException('Request failed', new Request($apiEndpoint->method(), '')));

        $this->expectException(ResponseException::class);

        $this->apiClient->getClubMembers($clubTag);
    }

    #[Test]
    #[TestWith(['club_tag_123'])]
    public function get_club_members_throws_response_exception_on_invalid_json(string $clubTag): void
    {
        $apiEndpoint = APIEndpoints::ClubMembers;
        $mockResponse = new Response(200, [], 'invalid-json');

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['club_tag' => $clubTag]),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(ResponseException::class);

        $this->apiClient->getClubMembers($clubTag);
    }

    /**
     * @throws ResponseException
     */
    #[Test]
    #[TestWith(['club_tag_123'])]
    public function get_club_members_throws_invalid_dto_exception(string $clubTag): void
    {
        $apiEndpoint = APIEndpoints::ClubMembers;
        $mockResponse = new Response(200, [], json_encode([['invalid' => 'data']]));

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['club_tag' => $clubTag]),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(InvalidDTOException::class);

        $this->apiClient->getClubMembers($clubTag);
    }

    private function createClubWithMembers(
        array|callable $attributes = [],
        int $memberCount = 10,
    ) : Club {
        return Club::factory()
            ->withMembers($memberCount)
            ->create($attributes);
    }

    /*
     * End of API endpoints for clubs.
     */

    /*
     * API endpoints for players.
     */

    /**
     * @throws ResponseException
     * @throws JsonException
     */
    #[Test]
    #[TestDox('Fetch a single player with its brawlers successfully')]
    public function it_fetches_player_by_tag_successfully(): void
    {
        $player = $this->createPlayerWithBrawlers();
//        dd(
////            $player,
//            $player->toArray(),
////            PlayerDTO::fromEloquentModel($player),
//            PlayerDTO::eloquentModelToArray($player),
//            json_encode(PlayerDTO::eloquentModelToArray($player), JSON_THROW_ON_ERROR),
//        );
        $apiEndpoint = APIEndpoints::PlayerByTag;
        $mockResponse = new Response(200, [], json_encode(PlayerDTO::eloquentModelToArray($player), JSON_THROW_ON_ERROR));

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['player_tag' => $player->tag]),
                Mockery::on(function ($options) {
                    return $options['headers']['Authorization'] === "Bearer $this->apiKey";
                })
            )
            ->andReturn($mockResponse);

        $playerDTO = $this->apiClient->getPlayerByTag($player->tag);
//        dd(
//            111,
//            $player->toArray(),
//            $playerDTO,
//        );

        $this->assertInstanceOf(PlayerDTO::class, $playerDTO);
        $this->assertSame($player->tag, $playerDTO->tag);
        $this->assertSame($player->name, $playerDTO->name);
        $this->assertSame($player->name_color, $playerDTO->nameColor);
        $this->assertSame($player->icon_id, $playerDTO->icon['id']);
        $this->assertSame($player->trophies, $playerDTO->trophies);
        $this->assertSame($player->highest_trophies, $playerDTO->highestTrophies);
        $this->assertSame($player->exp_level, $playerDTO->expLevel);
        $this->assertSame($player->exp_points, $playerDTO->expPoints);
        $this->assertSame($player->is_qualified_from_championship_league, $playerDTO->isQualifiedFromChampionshipChallenge);
        $this->assertSame($player->solo_victories, $playerDTO->victoriesSolo);
        $this->assertSame($player->duo_victories, $playerDTO->victoriesDuo);
        $this->assertSame($player->trio_victories, $playerDTO->victories3vs3);
        $this->assertSame($player->best_time_robo_rumble, $playerDTO->bestRoboRumbleTime);
        $this->assertSame($player->best_time_as_big_brawler, $playerDTO->bestTimeAsBigBrawler);

        $this->assertIsArray($playerDTO->club);
        $this->assertSame($player->club->tag, $playerDTO->club['tag']);
        $this->assertSame($player->club->name, $playerDTO->club['name']);

        $this->assertIsArray($playerDTO->brawlers);
        $this->assertCount($player->playerBrawlers()->count(), $playerDTO->brawlers);

        foreach ($playerDTO->brawlers as $i => $playerBrawlerDTO) {
            $this->assertInstanceOf(PlayerBrawlerDTO::class, $playerBrawlerDTO);
            /** @var Brawler $playerBrawler */
            $playerBrawler = $player->playerBrawlers->get($i);

//            $this->assertPlayerModelMatchesDTO(player: $member, playerDTO: $playerBrawlerDTO);
        }
    }

    #[Test]
    #[TestWith(['player_tag_123'])]
    public function get_player_by_tag_throws_response_exception_on_request_failure(string $playerTag): void
    {
        $apiEndpoint = APIEndpoints::PlayerByTag;
        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['player_tag' => $playerTag]),
                Mockery::any()
            )
            ->andThrow(new RequestException('Request failed', new Request($apiEndpoint->method(), '')));

        $this->expectException(ResponseException::class);

        $this->apiClient->getPlayerByTag($playerTag);
    }

    #[Test]
    #[TestWith(['player_tag_123'])]
    public function get_player_by_tag_throws_response_exception_on_invalid_json(string $playerTag): void
    {
        $apiEndpoint = APIEndpoints::PlayerByTag;
        $mockResponse = new Response(200, [], 'invalid-json');

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['player_tag' => $playerTag]),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(ResponseException::class);

        $this->apiClient->getPlayerByTag($playerTag);
    }

    /**
     * @throws ResponseException
     */
    #[Test]
    #[TestWith(['player_tag_123'])]
    public function get_player_by_tag_throws_invalid_dto_exception(string $playerTag): void
    {
        $apiEndpoint = APIEndpoints::PlayerByTag;
        $mockResponse = new Response(200, [], json_encode([['invalid' => 'data']]));

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['player_tag' => $playerTag]),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(InvalidDTOException::class);

        $this->apiClient->getPlayerByTag($playerTag);
    }

    private function createPlayerWithBrawlers(
        array|callable $attributes = [],
        int            $brawlerCount = 10,
    ) : Player {
        return Player::factory()
//            ->withBrawlers($brawlerCount)
            ->create($attributes);
    }

    /*
     * End of API endpoints for players.
     */
}
