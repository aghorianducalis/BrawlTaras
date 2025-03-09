<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brawler;
use App\Models\Club;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Player>
     */
    protected $model = Player::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tag = '#' . strtoupper($this->faker->unique()->bothify('#########?'));

        $state = [
            'tag' => $tag,
            'name' => "Player $tag",
            'name_color' => $this->faker->colorName(),
            'icon_id' => $this->faker->randomNumber(5),
            'trophies' => $this->faker->numberBetween(0, 150000),
            'highest_trophies' => fn(array $attributes) => $this->faker->numberBetween($attributes['trophies'], $attributes['trophies'] + 1000),
            'highest_power_play_points' => $this->faker->randomNumber(5),
            'exp_level' => $this->faker->randomNumber(5),
            'exp_points' => $this->faker->randomNumber(5),
            'is_qualified_from_championship_league' => $this->faker->boolean(),
            'solo_victories' => $this->faker->numberBetween(0, 10000),
            'duo_victories' => $this->faker->numberBetween(0, 10000),
            'trio_victories' => $this->faker->numberBetween(0, 10000),
            'best_time_robo_rumble' => $this->faker->numberBetween(0, 300),
            'best_time_as_big_brawler' => $this->faker->numberBetween(0, 300),
        ];

        return $state;
    }

    /**
     * Indicate that the player is a club member.
     *
     * @param Club|null $club
     * @return self
     */
    public function withClub(Club|null $club = null): self
    {
        $club = $club ?? Club::factory()->create();

        return $this->state(fn (array $attributes) => [
            'club_id'   => $club->id,
            'club_role' => $this->faker->randomElement(Club::CLUB_MEMBER_ROLES),
        ]);
    }

    /**
     * Attach brawlers to the player.
     *
     * @param int $count
     * @param array|callable $attributes
     * @return self
     */
    public function withBrawlers(int $count = 10, array|callable $attributes = []): self
    {
        return $this->afterCreating(fn(Player $player) => Brawler::factory()
            ->hasAttached($player)
            ->count($count)
            ->create($attributes)
        );
    }
}
