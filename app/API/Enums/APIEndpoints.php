<?php

declare(strict_types=1);

namespace App\API\Enums;

enum APIEndpoints: string
{
    case Brawlers = "/brawlers";

    case BrawlerById = "/brawlers/{brawler_id}";

    public function method(): string
    {
        return match($this) {
            APIEndpoints::Brawlers, APIEndpoints::BrawlerById => "GET",
        };
    }
}
