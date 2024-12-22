<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventMap;
use App\Models\EventMode;
use App\Models\EventModifier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Event>
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ext_id' => $this->faker->unique()->randomNumber(),
            'map_id' => EventMap::factory(),
            'mode_id' => EventMode::factory(),
        ];
    }

    /**
     * Attach EventModifier to the Event.
     *
     * @param int $count
     * @param array|callable $attributes
     * @return EventFactory
     */
    public function withModifiers(int $count = 1, array|callable $attributes = []): self
    {
        return $this->afterCreating(fn(Event $event) => EventModifier::factory()
            ->hasAttached($event)
            ->count($count)
            ->create($attributes));
    }
}
