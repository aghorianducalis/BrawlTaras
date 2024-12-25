<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EventMap;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventMap>
 */
class EventMapFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<EventMap>
     */
    protected $model = EventMap::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => "Event map #" . $this->faker->unique()->numerify(),
        ];
    }
}
