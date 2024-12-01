<?php

declare(strict_types=1);

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Accessory;
use App\Models\Brawler;
use App\Models\StarPower;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_brawler_factory(): void
    {
        /** @var Brawler $brawler */
        $brawler = Brawler::factory()
            ->has(Accessory::factory()->count(1))
            ->has(StarPower::factory()->count(2))
            ->create();
        $brawler->load(['accessories', 'starPowers']);

        self::assertTrue($brawler->exists);
        self::assertIsNumeric($brawler->id);
        self::assertNotEmpty($brawler->name);
        self::assertCount(1, $brawler->accessories);
        self::assertCount(2, $brawler->starPowers);
    }
}
