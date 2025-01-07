<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\ClubPlayerDTO;
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

    private function assertPlayerModelMatchesDTO(Player $player, ClubPlayerDTO $playerDTO): void
    {
        $this->assertSame($player->tag, $playerDTO->tag);
        $this->assertSame($player->name, $playerDTO->name);
        $this->assertSame($player->name_color, $playerDTO->nameColor);
        $this->assertSame($player->role ?? '', $playerDTO->role);//todo
        $this->assertSame($player->trophies, $playerDTO->trophies);
        $this->assertSame($player->icon_id, $playerDTO->icon['id']);//todo
    }
}
