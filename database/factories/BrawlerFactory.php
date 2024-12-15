<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brawler;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brawler>
 */
class BrawlerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Brawler>
     */
    protected $model = Brawler::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ext_id' => $this->faker->unique()->randomNumber(),
            'name' => "Brawler #" . $this->faker->numerify(),
        ];
    }
}
