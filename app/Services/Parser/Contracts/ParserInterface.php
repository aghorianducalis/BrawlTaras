<?php

declare(strict_types=1);

namespace App\Services\Parser\Contracts;

use App\Models\Brawler;
use App\Models\Club;
use App\Models\EventRotation;
use App\Models\Player;
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

    /**
     * Get information about a single clan by club tag and store/update them in the local database.
     *
     * @param string $clubTag
     * @return Club The parsed and saved club with its members (players).
     * @throws ParsingException If an unrecoverable error occurs during parsing.
     */
    public function parseClubByTag(string $clubTag): Club;

    /**
     * List club members.
     *
     * @param string $clubTag
     * @return Club
     * @throws ParsingException If an unrecoverable error occurs during parsing.
     */
    public function parseClubMembers(string $clubTag): Club;

    /**
     * Parse all event rotations from the API and store/update them in the local database.
     *
     * @return array<EventRotation> The list of parsed and saved event rotations.
     * @throws ParsingException If an unrecoverable error occurs during parsing.
     */
    public function parseEventsRotation(): array;
}
