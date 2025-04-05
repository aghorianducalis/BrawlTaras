<?php

declare(strict_types=1);

namespace Database\Factories\Battle;

use App\Models\Battle\Battle;
use App\Models\Battle\BattleResult;
use App\Models\EventMode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BattleResult>
 */
class BattleResultFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<BattleResult>
     */
    protected $model = BattleResult::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mode'           => $this->faker->randomElement(EventMode::MODES),
            'type'           => $this->faker->randomElement(BattleResult::TYPES),
            'result'         => $this->faker->randomElement(BattleResult::RESULTS),
            'duration'       => $this->faker->numberBetween(1, 200),
            'trophy_change'  => $this->faker->numberBetween(-10, +15),
            'rank'           => $this->faker->numberBetween(1, 11),
            'battle_id'      => Battle::factory(),
            'star_player_id' => null,
        ];
    }
}
