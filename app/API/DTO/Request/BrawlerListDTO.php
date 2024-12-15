<?php

declare(strict_types=1);

namespace App\API\DTO\Request;

use App\Models\Brawler;
use JsonException;
use JsonSerializable;

final readonly class BrawlerListDTO implements JsonSerializable
{
    /**
     * @param Brawler[] $brawlers
     */
    private function __construct(public array $brawlers)
    {}

    /**
     * Converts the DTO to JSON-serializable format.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $brawlers = [];

        foreach ($this->brawlers as $brawler) {
            $brawlers[] = BrawlerDTO::fromBrawlerModel($brawler)->jsonSerialize();
        }

        return [
            'items' => $brawlers,
        ];
    }

    /**
     * Converts the object to a JSON string.
     *
     * @return false|string
     * @throws JsonException
     */
    public function toJson(): false|string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR);
    }

    /**
     * @param Brawler[] $brawlers
     * @return self
     */
    public static function fromListOfBrawlerModels(array $brawlers): self
    {
        return new self($brawlers);
    }
}
