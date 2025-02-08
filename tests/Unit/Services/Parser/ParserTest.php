<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Parser;

use App\API\Client\APIClient;
use App\API\Contracts\APIClientInterface;
use App\API\DTO\Response\BrawlerDTO;
use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\EventRotationDTO;
use App\API\Exceptions\ResponseException;
use App\Models\Brawler;
use App\Models\Club;
use App\Models\EventRotation;
use App\Services\Parser\Contracts\ParserInterface;
use App\Services\Parser\Exceptions\ParsingException;
use App\Services\Parser\Parser;
use App\Services\Repositories\BrawlerRepository;
use App\Services\Repositories\Contracts\BrawlerRepositoryInterface;
use App\Services\Repositories\Contracts\ClubRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventRotationRepositoryInterface;
use App\Services\Repositories\Contracts\PlayerRepositoryInterface;
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
use Tests\Traits\CreatesEventRotations;
use Tests\Traits\TestClubs;

#[Group('Parser')]
#[CoversClass(Parser::class)]
#[CoversMethod(Parser::class, 'parseBrawlerByExternalId')]
#[CoversMethod(Parser::class, 'parseAllBrawlers')]
#[CoversMethod(Parser::class, 'parseClubByTag')]
#[CoversMethod(Parser::class, 'parseClubMembers')]
#[CoversMethod(Parser::class, 'parseEventsRotation')]
#[UsesClass(APIClient::class)]
#[UsesClass(BrawlerRepository::class)]
#[UsesClass(ParsingException::class)]
class ParserTest extends TestCase
{
    use RefreshDatabase;
    use CreatesBrawlers;
    use CreatesEventRotations;
    use TestClubs;

    private ParserInterface $parser;
    private MockInterface|APIClientInterface $apiClient;
    private MockInterface|BrawlerRepositoryInterface $brawlerRepository;
    private MockInterface|ClubRepositoryInterface $clubRepository;
    private MockInterface|PlayerRepositoryInterface $playerRepository;
    private MockInterface|EventRotationRepositoryInterface $eventRotationRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = Mockery::mock(APIClientInterface::class);
        $this->brawlerRepository = Mockery::mock(BrawlerRepositoryInterface::class);
        $this->clubRepository = Mockery::mock(ClubRepositoryInterface::class);
        $this->playerRepository = Mockery::mock(PlayerRepositoryInterface::class);
        $this->eventRotationRepository = Mockery::mock(EventRotationRepositoryInterface::class);
        $this->parser = new Parser(
            apiClient: $this->apiClient,
            brawlerRepository: $this->brawlerRepository,
            clubRepository: $this->clubRepository,
            playerRepository: $this->playerRepository,
            eventRotationRepository: $this->eventRotationRepository,
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

        try {
            $result = $this->parser->parseBrawlerByExternalId($brawler->ext_id);

            $this->assertEquals($brawler, $result);
        } catch (ParsingException $e) {
            $this->fail('ParsingException was thrown: ' . $e->getMessage());
        }
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

        try {
            $result = $this->parser->parseAllBrawlers();

            $this->assertEquals($brawlers, $result);
        } catch (ParsingException $e) {
            $this->fail('ParsingException was thrown: ' . $e->getMessage());
        }
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

    #[Test]
    #[TestDox('Parses events rotation successfully.')]
    public function test_parses_events_rotation_successfully(): void
    {
        $rotations = array_map(fn () => $this->createEventRotationWithRelations(), range(1, 2));
        $rotationDTOs = array_map(fn(EventRotation $rotation) => EventRotationDTO::fromEloquentModel($rotation), $rotations);

        $this->apiClient->shouldReceive('getEventsRotation')
            ->once()
            ->andReturn($rotationDTOs);

        $this->eventRotationRepository->shouldReceive('createOrUpdateEventRotations')
            ->once()
            ->with($rotationDTOs)
            ->andReturn($rotations);

        try {
            $result = $this->parser->parseEventsRotation();

            $this->assertEquals($rotations, $result);
        } catch (ParsingException $e) {
            $this->fail('ParsingException was thrown: ' . $e->getMessage());
        }
    }

    #[Test]
    #[TestDox('Parses club successfully.')]
    public function test_parses_club_successfully(): void
    {
        $club = Club::factory()->withMembers()->create();
        $clubDTO = ClubDTO::fromEloquentModel($club);

        $this->apiClient->shouldReceive('getClubByTag')
            ->once()
            ->with($club->tag)
            ->andReturn($clubDTO);

        $this->clubRepository->shouldReceive('createOrUpdateClub')
            ->once()
            ->with($clubDTO)
            ->andReturn($club);

        try {
            $result = $this->parser->parseClubByTag($club->tag);

            $this->assertEquals($club, $result);
            $this->assertEquals($club->members, $result->members);
        } catch (ParsingException $e) {
            $this->fail('ParsingException was thrown: ' . $e->getMessage());
        }
    }

    #[Test]
    #[TestDox('Parses club members successfully.')]
    public function test_parses_club_members_successfully(): void
    {
        $club = Club::factory()->withMembers()->create();
        $clubDTO = ClubDTO::fromEloquentModel($club);

        $this->apiClient->shouldReceive('getClubMembers')
            ->once()
            ->with($club->tag)
            ->andReturn($clubDTO->members);

        $this->clubRepository->shouldReceive('syncClubMembersByTag')
            ->once()
            ->with($club->tag, $clubDTO->members)
            ->andReturn($club);

        try {
            $result = $this->parser->parseClubMembers($club->tag);

            $this->assertEqualClubModels($club, $result);
            $this->assertEqualsCanonicalizing(
                $club->members->pluck('tag')->toArray(),
                $result->members->pluck('tag')->toArray(),
                'Club members do not match expected values'
            );
        } catch (ParsingException $e) {
            $this->fail('ParsingException was thrown: ' . $e->getMessage());
        }
    }
}
