<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HitFactory extends Factory
{
    public function definition()
    {
        return [
            'endpoint' => '/api/releases/8.0',
            'user_agent' => $this->faker->userAgent,
        ];
    }
}
