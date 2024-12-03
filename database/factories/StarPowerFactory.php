<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brawler;
use App\Models\StarPower;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StarPower>
 */
class StarPowerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<StarPower>
     */
    protected $model = StarPower::class;

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
