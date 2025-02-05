<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\PlayerDTO;
use App\Models\Player;

trait CreatesPlayers
{
    private function assertEqualPlayerModels(Player $playerExpected, ?Player $playerActual): void
    {
        $this->assertNotNull($playerActual);
        $this->assertInstanceOf(Player::class, $playerActual);
        $this->assertSame($playerExpected->id, $playerActual->id);
        $this->assertSame($playerExpected->tag, $playerActual->tag);
        $this->assertSame($playerExpected->name, $playerActual->name);
        $this->assertSame($playerExpected->club_id, $playerActual->club_id);
        $this->assertTrue($playerExpected->created_at->equalTo($playerActual->created_at));

        // compare the player's relations
        $playerExpected->load([
            'club',
        ]);
        $playerActual->load([
            'club',
        ]);

        $this->assertEquals(
            $playerExpected->club->toArray(),
            $playerActual->club->toArray()
        );
    }

    private function assertPlayerModelMatchesDTO(Player $player, PlayerDTO $playerDTO): void
    {
        $this->assertEquals($player->tag, $playerDTO->tag);
        $this->assertEquals($player->name, $playerDTO->name);
        $this->assertEquals($player->name_color, $playerDTO->nameColor);
        $this->assertEquals($player->club_role, $playerDTO->clubRole);
        $this->assertEquals($player->trophies, $playerDTO->trophies);
        $this->assertEquals($player->icon_id, $playerDTO->icon['id']);
    }
}
