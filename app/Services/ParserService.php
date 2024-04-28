<?php

declare(strict_types=1);

namespace App\Services;

use App\API\Client\APIClient;
use App\Models\Brawler;
use App\Services\Repositories\BrawlerRepository;
use Exception;

readonly class ParserService
{
    public function __construct(
        protected APIClient         $apiClient,
        protected BrawlerRepository $brawlerRepository
    ) {
    }

    /**
     * \App\Services\ParserService::getInstance()->parseBrawlers()
     *
     * @return Brawler[]
     * @throws Exception
     */
    public function parseAllBrawlers(): array
    {
        $brawlerDTOs = $this->apiClient->getBrawlers();
        return $this->brawlerRepository->createOrUpdateBrawlers($brawlerDTOs);
    }

    /**
     * \App\Services\ParserService::getInstance()->parseBrawlerByExtId(16000000)
     *
     * @param int $extId
     * @return Brawler
     * @throws Exception
     */
    public function parseBrawlerByExtId(int $extId): Brawler
    {
        $brawlerDTO = $this->apiClient->getBrawler($extId);
        return $this->brawlerRepository->createOrUpdateBrawler($brawlerDTO);
    }

    public static function getInstance(): self
    {
        return app(ParserService::class);
    }
}
