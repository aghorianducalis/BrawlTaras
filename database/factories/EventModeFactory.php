<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EventMode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventMode>
 */
class EventModeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<EventMode>
     */
    protected $model = EventMode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => "Event mode #" . $this->faker->unique()->numerify(),
        ];
    }
}
