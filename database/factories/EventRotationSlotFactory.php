<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EventRotationSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRotationSlot>
 */
class EventRotationSlotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<EventRotationSlot>
     */
    protected $model = EventRotationSlot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position' => $this->faker->unique()->randomNumber(),
        ];
    }
}
