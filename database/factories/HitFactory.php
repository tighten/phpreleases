<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HitFactory extends Factory
{
    public function definition()
    {
        return [
            'endpoint' => '/api/releases/' . random_int(5, 9) . '.' . random_int(0, 12) . random_int(0, 25),
            'user_agent' => $this->faker->userAgent,
            'referer' => $this->faker->url,
            'ip' => $this->faker->ipv4,
        ];
    }
}
