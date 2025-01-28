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
            'trophies' => $this->faker->randomNumber(5),
            'highest_trophies' => $this->faker->randomNumber(5),
            'highest_power_play_points' => $this->faker->randomNumber(5),
            'exp_level' => $this->faker->randomNumber(5),
            'exp_points' => $this->faker->randomNumber(5),
            'is_qualified_from_championship_league' => $this->faker->boolean(),
            'solo_victories' => $this->faker->randomNumber(5),
            'duo_victories' => $this->faker->randomNumber(5),
            'trio_victories' => $this->faker->randomNumber(5),
            'best_time_robo_rumble' => $this->faker->randomNumber(5),
            'best_time_as_big_brawler' => $this->faker->randomNumber(5),
        ];

        if ($belongsToClub = $this->faker->boolean) {
            $state = array_merge($state, [
                'club_id' => Club::factory(),
                'club_role' => $this->faker->randomElement(Player::CLUB_ROLES),
            ]);
        }

        return $state;
    }

    /**
     * Indicate that the player is a club member.
     */
    public function withClub(?Club $club = null): Factory
    {
        $club = $club ?? Club::factory();

        return $this->state(fn (array $attributes) => [
            'club_id' => $club,
            'club_role' => $this->faker->randomElement(Player::CLUB_ROLES),
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
