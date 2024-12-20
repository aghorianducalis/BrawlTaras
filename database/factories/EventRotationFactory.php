<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EventRotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRotation>
 */
class EventRotationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<EventRotation>
     */
    protected $model = EventRotation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ext_id' => $this->faker->unique()->randomNumber(),
            'name' => "StarPower #" . $this->faker->numerify(),
            'brawler_id' => Brawler::factory(),
        ];
    }
}
