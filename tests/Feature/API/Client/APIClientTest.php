<?php

declare(strict_types=1);

namespace Tests\Feature\API\Client;

use App\API\Client\APIClient;
use App\API\DTO\Request\BrawlerDTO as BrawlerRequestDTO;
use App\API\DTO\Request\BrawlerListDTO as BrawlerListRequestDTO;
use App\API\DTO\Request\EventRotationListDTO;
use App\API\DTO\Response\BrawlerDTO as BrawlerResponseDTO;
use App\API\DTO\Response\EventRotationDTO;
use App\API\Enums\APIEndpoints;
use App\API\Exceptions\InvalidDTOException;
use App\API\Exceptions\ResponseException;
use App\Models\Event;
use App\Models\EventRotation;
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
use Tests\TestCase;
use Tests\Traits\CreatesBrawlers;

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
#[CoversMethod(APIClient::class, 'getEventsRotation')]
class APIClientTest extends TestCase
{
    use CreatesBrawlers;
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
    public function get_brawler_by_id_throws_response_exception_on_request_failure(): void
    {
        $apiEndpoint = APIEndpoints::BrawlerById;
        $brawlerId = 123;
        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['brawler_id' => $brawlerId]),
                Mockery::any()
            )
            ->andThrow(new RequestException('Request failed', new Request($apiEndpoint->method(), '')));

        $this->expectException(ResponseException::class);

        $this->apiClient->getBrawler($brawlerId);
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
    public function get_brawler_by_id_throws_response_exception_on_invalid_json(): void
    {
        $apiEndpoint = APIEndpoints::BrawlerById;
        $brawlerId = 123;
        $mockResponse = new Response(200, [], 'invalid-json');

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['brawler_id' => $brawlerId]),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(ResponseException::class);

        $this->apiClient->getBrawler($brawlerId);
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
    public function get_brawler_by_id_throws_invalid_dto_exception(): void
    {
        $apiEndpoint = APIEndpoints::BrawlerById;
        $brawlerId = 123;
        $mockResponse = new Response(200, [], json_encode(['invalid' => 'data']));

        $this->httpClientMock
            ->shouldReceive('request')
            ->once()
            ->with(
                $apiEndpoint->method(),
                $apiEndpoint->constructRequestURI($this->apiBaseURI, ['brawler_id' => $brawlerId]),
                Mockery::any()
            )
            ->andReturn($mockResponse);

        $this->expectException(InvalidDTOException::class);

        $this->apiClient->getBrawler($brawlerId);
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
}
