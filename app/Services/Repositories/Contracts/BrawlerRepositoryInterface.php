<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts;

use App\API\DTO\Response\BrawlerDTO;
use App\Models\Brawler;

interface BrawlerRepositoryInterface
{
    /**
     * Find a brawler based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return Brawler|null
     */
    public function findBrawler(array $searchCriteria): ?Brawler;

    /**
     * Create or update a single brawler in the database and sync related entities.
     *
     * @param BrawlerDTO $brawlerDTO
     * @return Brawler
     */
    public function createOrUpdateBrawler(BrawlerDTO $brawlerDTO): Brawler;

    /**
     * Bulk create or update brawlers in the database.
     *
     * @param array<BrawlerDTO> $brawlerDTOs
     * @return array<Brawler>
     */
    public function createOrUpdateBrawlers(array $brawlerDTOs): array;
}
