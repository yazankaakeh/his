<?php

namespace Database\Factories\Botble\Hotel\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Hotel\Models\Currency;
use Botble\Hotel\Models\Hotel;
use Botble\Hotel\Models\Room;
use Botble\Hotel\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        // Get or create a default currency
        $currency = Currency::query()->first();
        if (!$currency) {
            $currency = Currency::query()->create([
                'title' => 'USD',
                'symbol' => '$',
                'is_prefix_symbol' => true,
                'is_default' => true,
                'order' => 0,
                'decimals' => 2,
                'exchange_rate' => 1,
            ]);
        }

        // Get or create a default hotel
        $hotel = Hotel::query()->first();
        if (!$hotel) {
            $hotel = Hotel::query()->create([
                'name' => 'Test Hotel',
                'description' => 'Test hotel for testing',
                'status' => BaseStatusEnum::PUBLISHED,
            ]);
        }

        // Get or create a default tax
        $tax = Tax::query()->first();
        if (!$tax) {
            $tax = Tax::factory()->create();
        }

        return [
            'name' => $this->faker->words(3, true) . ' Room',
            'description' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'is_featured' => $this->faker->boolean(30),
            'images' => json_encode([]),
            'price' => $this->faker->randomFloat(2, 50, 500),
            'currency_id' => $currency->id,
            'number_of_rooms' => $this->faker->numberBetween(1, 10),
            'number_of_beds' => $this->faker->numberBetween(1, 4),
            'size' => $this->faker->numberBetween(20, 100),
            'max_adults' => $this->faker->numberBetween(1, 6),
            'max_children' => $this->faker->numberBetween(0, 4),
            'hotel_id' => $hotel->id,
            'tax_id' => $tax->id,
            'order' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
        ];
    }
}
