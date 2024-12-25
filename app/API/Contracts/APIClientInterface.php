<?php

declare(strict_types=1);

namespace App\API\Contracts;

use App\API\DTO\Response\BrawlerDTO;
use App\API\DTO\Response\EventRotationDTO;
use App\API\Exceptions\InvalidDTOException;
use App\API\Exceptions\ResponseException;

interface APIClientInterface
{
    /**
     * Fetch a single brawler by its external ID.
     *
     * @param int $externalId
     * @return BrawlerDTO
     */
    public function getBrawler(int $externalId): BrawlerDTO;

    /**
     * Fetch all brawlers.
     *
     * @return array<BrawlerDTO>
     */
    public function getBrawlers(): array;

    /**
     * Fetch events rotation.
     *
     * @throws ResponseException
     * @throws InvalidDTOException
     * @return array<EventRotationDTO>
     */
    public function getEventsRotation(): array;
}
