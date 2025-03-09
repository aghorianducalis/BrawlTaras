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
            'ext_id' => $this->faker->unique()->randomNumber(),
            'name' => "Accessory #" . $this->faker->unique()->numerify(),
        ];
    }

    /**
     * Attach brawlers to the accessory.
     *
     * @param int $count
     * @param array|callable $attributes
     * @return self
     */
    public function withBrawlers(int $count = 1, array|callable $attributes = []): self
    {
        return $this->afterCreating(
            fn(Accessory $accessory) => Brawler::factory()
                ->hasAttached($accessory)
                ->count($count)
                ->create($attributes)
        );
    }
}
