<?php

declare(strict_types=1);

namespace App\API\DTO\Request;

use App\Models\Accessory;
use App\Models\Brawler;
use App\Models\StarPower;
use JsonException;
use JsonSerializable;

final readonly class BrawlerDTO implements JsonSerializable
{
    private function __construct(public Brawler $brawler)
    {}

    /**
     * Converts the DTO to JSON-serializable format.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $accessories = $this->brawler->accessories
            ->transform(fn(Accessory|array $accessory) => ($accessory instanceof Accessory) ?
                [
                    'id' => $accessory->ext_id,
                    'name' => $accessory->name,
                ]
                : $accessory
            )
            ->toArray();
        $starPowers = $this->brawler->starPowers
            ->transform(fn(StarPower|array $starPower) => ($starPower instanceof StarPower) ?
                [
                    'id' => $starPower->ext_id,
                    'name' => $starPower->name,
                ]
                : $starPower
            )
            ->toArray();

        return [
            'id' => $this->brawler->ext_id,
            'name' => $this->brawler->name,
            'gadgets' => $accessories,
            'starPowers' => $starPowers,
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

    public static function fromBrawlerModel(Brawler $brawler): self
    {
        return new self($brawler);
    }
}
