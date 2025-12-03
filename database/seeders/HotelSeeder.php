<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Hotel\Models\Hotel;
use Illuminate\Support\Facades\File;

class HotelSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('hotels');

        Hotel::query()->truncate();

        $hotels = [
            [
                'name' => 'Marriott',
                'description' => 'Marriott Hotels & Resorts is known for exceptional service and comfortable accommodations worldwide.',
                'address' => '123 Marriott Boulevard, City Center',
                'phone' => '+1-800-MARRIOTT',
                'email' => 'info@marriott.com',
                'image' => 'hotels/marriott.jpg',
                'status' => 'published',
            ],
            [
                'name' => 'Hilton',
                'description' => 'Hilton Hotels & Resorts offers modern amenities and warm hospitality in prime locations globally.',
                'address' => '456 Hilton Avenue, Downtown District',
                'phone' => '+1-800-HILTONS',
                'email' => 'contact@hilton.com',
                'image' => 'hotels/hilton.jpg',
                'status' => 'published',
            ],
            [
                'name' => 'Hyatt',
                'description' => 'Hyatt Hotels provide contemporary luxury with personalized service and world-class facilities.',
                'address' => '789 Hyatt Plaza, Business Quarter',
                'phone' => '+1-800-HYATT00',
                'email' => 'reservations@hyatt.com',
                'image' => 'hotels/hyatt.jpg',
                'status' => 'published',
            ],
            [
                'name' => 'Four Seasons',
                'description' => 'Four Seasons Hotels and Resorts delivers unparalleled luxury and refined elegance in every detail.',
                'address' => '321 Four Seasons Drive, Luxury District',
                'phone' => '+1-800-SEASONS',
                'email' => 'concierge@fourseasons.com',
                'image' => 'hotels/four-seasons.jpg',
                'status' => 'published',
            ],
        ];

        foreach ($hotels as $hotel) {
            $hotel['content'] = File::get(database_path('seeders/contents/hotel-content.html'));
            Hotel::query()->create($hotel);
        }
    }
}
