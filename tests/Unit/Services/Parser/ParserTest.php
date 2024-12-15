<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Parser;

use App\API\Client\APIClient;
use App\API\Contracts\APIClientInterface;
use App\API\DTO\Response\BrawlerDTO;
use App\API\Exceptions\ResponseException;
use App\Models\Brawler;
use App\Services\Parser\Contracts\ParserInterface;
use App\Services\Parser\Exceptions\ParsingException;
use App\Services\Parser\Parser;
use App\Services\Repositories\BrawlerRepository;
use App\Services\Repositories\Contracts\BrawlerRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;
use Tests\Traits\CreatesBrawlers;

#[Group('Parser')]
#[CoversClass(Parser::class)]
#[CoversMethod(Parser::class, 'parseBrawlerByExternalId')]
#[CoversMethod(Parser::class, 'parseAllBrawlers')]
#[UsesClass(APIClient::class)]
#[UsesClass(BrawlerRepository::class)]
#[UsesClass(ParsingException::class)]
class ParserTest extends TestCase
{
    use CreatesBrawlers;
    use RefreshDatabase;

    private ParserInterface $parser;
    private MockInterface|APIClientInterface $apiClient;
    private MockInterface|BrawlerRepositoryInterface $brawlerRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = Mockery::mock(APIClientInterface::class);
        $this->brawlerRepository = Mockery::mock(BrawlerRepositoryInterface::class);
        $this->parser = new Parser(
            apiClient: $this->apiClient,
            brawlerRepository: $this->brawlerRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    #[TestDox('Parses a specific brawler by external ID successfully')]
    public function test_parses_a_specific_brawler_by_external_id_successfully(): void
    {
        $brawler = $this->createBrawlerWithRelations();
        $brawlerDTO = BrawlerDTO::fromBrawlerModel($brawler);

        $this->apiClient->shouldReceive('getBrawler')
            ->once()
            ->with($brawler->ext_id)
            ->andReturn($brawlerDTO);

        $this->brawlerRepository->shouldReceive('createOrUpdateBrawler')
            ->once()
            ->with($brawlerDTO)
            ->andReturn($brawler);

        // nice todo assert that ParsingException was not thrown (thus suppressing IDE warning)
        $result = $this->parser->parseBrawlerByExternalId($brawler->ext_id);

        $this->assertEquals($brawler, $result);
    }

    #[Test]
    #[TestDox('Throws an exception when the API client fails while parsing the brawler by external ID.')]
    public function test_throws_an_exception_on_API_client_failure_while_parsing_the_brawler_by_external_id(): void
    {
        $externalId = 123;

        // Verify that logging happens
        Log::shouldReceive('error')->once();

        $this->apiClient->shouldReceive('getBrawler')
            ->once()
            ->with($externalId)
            ->andThrow(ResponseException::fromMessage('API failure'));

        $this->brawlerRepository->shouldNotReceive('createOrUpdateBrawler');

        $this->expectException(ParsingException::class);
        $this->parser->parseBrawlerByExternalId($externalId);
    }

    #[Test]
    #[TestDox('Parses all brawlers successfully.')]
    public function test_parses_all_brawlers_successfully(): void
    {
        $brawlers = array_map(fn () => $this->createBrawlerWithRelations(), range(1, 2));
        $brawlerDTOs = array_map(fn(Brawler $brawler) => BrawlerDTO::fromBrawlerModel($brawler), $brawlers);

        $this->apiClient->shouldReceive('getBrawlers')
            ->once()
            ->andReturn($brawlerDTOs);

        $this->brawlerRepository->shouldReceive('createOrUpdateBrawlers')
            ->once()
            ->with($brawlerDTOs)
            ->andReturn($brawlers);

        // nice todo assert that ParsingException was not thrown (thus suppressing IDE warning)
        $result = $this->parser->parseAllBrawlers();

        $this->assertEquals($brawlers, $result);
    }

    #[Test]
    #[TestDox('Throws an exception for empty brawlers while parsing all the brawlers..')]
    public function test_throws_an_exception_for_empty_brawlers_while_parsing_all_the_brawlers(): void
    {
        $this->apiClient->shouldReceive('getBrawlers')
            ->once()
            ->andReturn([]);

        $this->brawlerRepository->shouldNotReceive('createOrUpdateBrawlers');

        $this->expectException(ParsingException::class);
        $this->parser->parseAllBrawlers();
    }

    #[Test]
    #[TestDox('Throws an exception when the API client fails while parsing all the brawlers.')]
    public function test_throws_an_exception_on_API_client_failure_while_parsing_all_the_brawlers(): void
    {
        // Verify that logging happens
        Log::shouldReceive('error')->once();

        $this->apiClient->shouldReceive('getBrawlers')
            ->once()
            ->andThrow(ResponseException::fromMessage('API failure'));

        $this->brawlerRepository->shouldNotReceive('createOrUpdateBrawlers');

        $this->expectException(ParsingException::class);
        $this->parser->parseAllBrawlers();
    }
}
