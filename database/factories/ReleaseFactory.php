<?php

namespace Database\Factories;

use App\Models\Release;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReleaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Release::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $taggedAt = CarbonImmutable::now()
            ->subMonths($this->faker->randomNumber(1))
            ->subDays($this->faker->randomNumber(2));

        return [
            'major' => (string) $this->faker->numberBetween(5, 8),
            'minor' => (string) $this->faker->numberBetween(0, 6),
            'release' => (string) $this->faker->numberBetween(0, 40),
            'tagged_at' => $taggedAt->toDate(),
            'active_support_until' => $taggedAt->addYears(2)->toDate(),
            'security_support_until' => $taggedAt->addYears(3)->toDate(),
        ];
    }
}
