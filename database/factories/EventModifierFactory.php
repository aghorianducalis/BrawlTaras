<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EventModifier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventModifier>
 */
class EventModifierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<EventModifier>
     */
    protected $model = EventModifier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => "Event modifier #" . $this->faker->unique()->numerify(),
        ];
    }
}
