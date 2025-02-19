<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Accessory;
use App\Models\Brawler;
use App\Models\Gear;
use App\Models\Player;
use App\Models\StarPower;
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
            'name' => "Brawler #" . $this->faker->unique()->numerify(),
        ];
    }

    /**
     * Attach accessories to the brawler.
     *
     * @param int $count
     * @param array|callable $attributes
     * @return self
     */
    public function withAccessories(int $count = 1, array|callable $attributes = []): self
    {
        return $this->afterCreating(
            fn(Brawler $brawler) => Accessory::factory()
                ->hasAttached($brawler)
                ->count($count)
                ->create($attributes)
        );
    }

    /**
     * Attach gears to the brawler.
     *
     * @param int $count
     * @param array|callable $attributes
     * @return self
     */
    public function withGears(int $count = 1, array|callable $attributes = []): self
    {
        return $this->afterCreating(
            fn(Brawler $brawler) => Gear::factory()
                ->hasAttached($brawler)
                ->count($count)
                ->create($attributes)
        );
    }

    /**
     * Attach star powers to the brawler.
     *
     * @param int $count
     * @param array|callable $attributes
     * @return self
     */
    public function withStarPowers(int $count = 1, array|callable $attributes = []): self
    {
        return $this->afterCreating(
            fn(Brawler $brawler) => StarPower::factory()
                ->hasAttached($brawler)
                ->count($count)
                ->create($attributes)
        );
    }

    /**
     * Attach players to the brawler.
     *
     * @param int $count
     * @param array|callable $playerAttributes
     * @param array|callable $playerBrawlerAttributes for pivot
     * @return self
     */
    public function withPlayers(
        int $count = 1,
        array|callable $playerAttributes = [],
        array|callable $playerBrawlerAttributes = [],
    ): self
    {
        $trophies = $this->faker->numberBetween(0, 1200);
        $requiredPlayerBrawlerAttributes = [
            'power'            => $this->faker->numberBetween(1, 11),
            'rank'             => $this->faker->numberBetween(1, 50),
            'trophies'         => $trophies,
            'highest_trophies' => $this->faker->numberBetween($trophies, $trophies + 100),
        ];

        foreach ($requiredPlayerBrawlerAttributes as $key => $value) {
            if (!array_key_exists($key, $playerBrawlerAttributes)) {
                $playerBrawlerAttributes[$key] = $value;
            }
        }

        return $this->afterCreating(
            fn(Brawler $brawler) => Player::factory()
                ->hasAttached($brawler, $playerBrawlerAttributes)
                ->count($count)
                ->create($playerAttributes)
        );
    }
}
