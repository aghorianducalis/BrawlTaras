<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Club;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Club>
 */
class ClubFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Club>
     */
    protected $model = Club::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tag = '#' . strtoupper($this->faker->unique()->bothify('#########?'));

        return [
            'tag' => $tag,
            'name' => "Club $tag",
            'description' => $this->faker->text(),
            'type' => $this->faker->randomElement(Club::CLUB_TYPES),
            'badge_id' => $this->faker->randomNumber(5),
            'required_trophies' => $this->faker->numberBetween(0, 100000),
            'trophies' => fn(array $attributes) => $this->faker->numberBetween($attributes['required_trophies'], $attributes['required_trophies'] * 10),
        ];
    }

    /**
     * Attach players to the club.
     *
     * @param int $count
     * @param array|callable $attributes
     * @return self
     */
    public function withMembers(int $count = 10, array|callable $attributes = []): self
    {
        return $this->afterCreating(
            fn(Club $club) => Player::factory()->withClub($club)->count($count)->create($attributes)
        );
    }
}
