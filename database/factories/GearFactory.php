<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Gear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gear>
 */
class GearFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Gear>
     */
    protected $model = Gear::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ext_id' => $this->faker->unique()->randomNumber(),
            'name' => "Gear #" . $this->faker->unique()->numerify(),
            'level' => $this->faker->numberBetween(0, 11),
        ];
    }
}
