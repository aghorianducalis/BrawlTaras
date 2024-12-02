<?php

declare(strict_types=1);

namespace Tests\Unit\API\Client;

use App\API\Client\APIClient;
use App\API\DTO\Request\BrawlerDTO as BrawlerRequestDTO;
use App\API\DTO\Request\BrawlerListDTO as BrawlerListRequestDTO;
use App\API\DTO\Response\BrawlerDTO as BrawlerResponseDTO;
use App\API\Enums\APIEndpoints;
use App\API\Exceptions\InvalidDTOException;
use App\API\Exceptions\ResponseException;
use Attribute;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;
use JsonException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
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
class APIClientTest extends TestCase
{
    use CreatesBrawlers;
    use RefreshDatabase;

    private MockInterface $httpClientMock;

    private string $apiBaseURI = 'https://api.example.com';

    private string $apiKey = 'test-api-key';

    private APIClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();

        Log::shouldReceive('error')->andReturn();

        $this->httpClientMock = Mockery::mock(GuzzleClient::class);
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
     * @throws ResponseException
     * @throws JsonException
     */
    #[Test]
    #[TestDox('Fetch all brawlers successfully')]
    #[NoReturn]
//    #[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
//    #[CoversMethod(APIClient::class, "getBrawlers")]
//    #[CoversFunction("getBrawlers")]
    public function it_fetches_all_brawlers_successfully(): void
    {
        $apiEndpoint = APIEndpoints::Brawlers;
        $brawlersExpected = array_map(fn () => $this->createBrawlerModelWithRelations(), range(2, 2));
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

        $brawlers = $this->apiClient->getBrawlers();

        $this->assertIsArray($brawlers);
        $this->assertCount(sizeof($brawlersExpected), $brawlers);

        foreach ($brawlers as $i => $brawler) {
            $this->assertInstanceOf(BrawlerResponseDTO::class, $brawler);
            $this->compareStructureOfBrawlerModelAndDTO($brawlersExpected[$i], $brawler);
        }
    }

    /**
     * @return void
     * @throws JsonException|ResponseException
     */
    #[Test]
    #[NoReturn]
    public function it_fetches_brawler_by_id_successfully(): void
    {
        $apiEndpoint = APIEndpoints::BrawlerById;
        $brawlerExpected = $this->createBrawlerModelWithRelations();
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
        $this->compareStructureOfBrawlerModelAndDTO($brawlerExpected, $brawlerDTOFetched);
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
}
