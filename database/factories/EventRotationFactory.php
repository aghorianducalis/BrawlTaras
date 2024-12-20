<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventRotation;
use App\Models\EventRotationSlot;
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
            'start_time' => $this->faker->dateTime(),
            'end_time' => fn() => $this->faker->dateTime(),
            'event_id' => Event::factory(),
            'slot_id' => EventRotationSlot::factory(),
        ];
    }
}
