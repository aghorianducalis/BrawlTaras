<?php

declare(strict_types=1);

namespace Tests\Unit\Factory;

use Tests\TestCase;
use Tests\Traits\CreatesBrawlers;

class BrawlerFactoryTest extends TestCase
{
    use CreatesBrawlers;

    public function test_brawler_factory(): void
    {
        $brawler = $this->createBrawlerWithRelations(2, 2);

        $this->assertTrue($brawler->exists);
        $this->assertIsNumeric($brawler->id);
        $this->assertNotEmpty($brawler->name);
        $this->assertCount(2, $brawler->accessories);
        $this->assertCount(2, $brawler->starPowers);
    }
}
