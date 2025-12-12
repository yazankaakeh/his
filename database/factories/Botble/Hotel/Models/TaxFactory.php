<?php

namespace Database\Factories\Botble\Hotel\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Hotel\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(2, true),
            'percentage' => $this->faker->randomFloat(2, 5, 20),
            'priority' => $this->faker->numberBetween(1, 10),
            'status' => BaseStatusEnum::PUBLISHED,
        ];
    }
}
