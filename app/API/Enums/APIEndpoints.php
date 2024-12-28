<?php

declare(strict_types=1);

namespace App\API\Enums;

enum APIEndpoints: string
{
    case Brawlers = "/brawlers";

    case BrawlerById = "/brawlers/{brawler_id}";

    case EventRotation = "/events/rotation";

    case ClubByTag = "/clubs/{club_tag}";

    case ClubMembers = "/clubs/{club_tag}/members";

    public function method(): string
    {
        return match($this) {
            APIEndpoints::Brawlers,
            APIEndpoints::BrawlerById,
            APIEndpoints::ClubByTag,
            APIEndpoints::ClubMembers,
            APIEndpoints::EventRotation, => "GET",
        };
    }

    /**
     * Construct the URI for the API request.
     *
     * @param string $apiBaseURI
     * @param array $requestData
     * @return string
     */
    public function constructRequestURI(string $apiBaseURI, array $requestData = []): string
    {
        $uri = $apiBaseURI . $this->value;

        foreach ($requestData as $key => $value) {
            $uri = str_replace("{{$key}}", (string) $value, $uri);
        }

        return $uri;
    }
}
