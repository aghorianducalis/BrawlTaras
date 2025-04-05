<?php

declare(strict_types=1);

namespace Database\Factories\Battle;

use App\Models\Battle\Battle;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Battle>
 */
class BattleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Battle>
     */
    protected $model = Battle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'battle_time' => $this->faker->dateTime(),
            'event_id'    => Event::factory(),
        ];
    }
}
