<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Accessory;
use App\Models\Brawler;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Accessory>
 */
class AccessoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Accessory>
     */
    protected $model = Accessory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ext_id' => fake()->unique()->randomNumber(),
            'name' => "Accessory #" . fake()->numerify(),
            'brawler_id' => Brawler::factory(),
        ];
    }
}
