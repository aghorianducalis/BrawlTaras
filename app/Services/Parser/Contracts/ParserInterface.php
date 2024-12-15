<?php

declare(strict_types=1);

namespace App\Services\Parser\Contracts;

use App\Models\Brawler;
use App\Services\Parser\Exceptions\ParsingException;

/**
 * Interface for the top-level service that
 * orchestrates fetching data from the API and saving it into the DB.
 */
interface ParserInterface
{
    /**
     * Parses a specific brawler by its external ID from the API and store/update it in the local database.
     *
     * @param int $externalId The external ID of the brawler.
     * @return Brawler The parsed and saved brawler.
     * @throws ParsingException If an unrecoverable error occurs during parsing.
     */
    public function parseBrawlerByExternalId(int $externalId): Brawler;

    /**
     * Parse all brawlers from the API and store/update them in the local database.
     *
     * @return array<Brawler> The list of parsed and saved brawlers.
     * @throws ParsingException If an unrecoverable error occurs during parsing.
     */
    public function parseAllBrawlers(): array;
}
