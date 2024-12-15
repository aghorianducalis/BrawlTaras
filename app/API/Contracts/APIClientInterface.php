<?php

declare(strict_types=1);

namespace App\API\Contracts;

use App\API\DTO\Response\BrawlerDTO;

interface APIClientInterface
{
    /**
     * Fetch a single brawler by its external ID from the API.
     *
     * @param int $externalId
     * @return BrawlerDTO
     */
    public function getBrawler(int $externalId): BrawlerDTO;

    /**
     * Fetch all brawlers from the API.
     *
     * @return array<BrawlerDTO>
     */
    public function getBrawlers(): array;
}
