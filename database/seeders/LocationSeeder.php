<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Hotel\Models\Hotel;
use Botble\Hotel\Models\Location;

class LocationSeeder extends BaseSeeder
{
    public function run(): void
    {
        Location::query()->truncate();

        $locations = [
            [
                'name' => 'Asia',
                'description' => 'The largest and most populous continent, featuring diverse cultures, ancient civilizations, and modern metropolises.',
                'status' => 'published',
            ],
            [
                'name' => 'Europe',
                'description' => 'A continent rich in history, art, and architecture, offering charming cities and picturesque landscapes.',
                'status' => 'published',
            ],
            [
                'name' => 'Africa',
                'description' => 'A vibrant continent known for its diverse wildlife, stunning natural beauty, and rich cultural heritage.',
                'status' => 'published',
            ],
            [
                'name' => 'Downtown',
                'description' => 'Prime downtown location with easy access to business districts and entertainment venues.',
                'status' => 'published',
            ],
            [
                'name' => 'Airport Area',
                'description' => 'Conveniently located near the airport with quick access to major transportation hubs.',
                'status' => 'published',
            ],
            [
                'name' => 'Beachfront',
                'description' => 'Scenic beachfront location offering stunning ocean views and beach activities.',
                'status' => 'published',
            ],
            [
                'name' => 'Historic District',
                'description' => 'Located in the charming historic district surrounded by cultural landmarks and museums.',
                'status' => 'published',
            ],
            [
                'name' => 'Suburban',
                'description' => 'Peaceful suburban setting perfect for families and extended stays.',
                'status' => 'published',
            ],
        ];

        $createdLocations = [];
        foreach ($locations as $location) {
            $createdLocations[] = Location::query()->create($location);
        }

        // Link existing hotels with locations
        $hotels = Hotel::query()->get();

        if ($hotels->isNotEmpty() && !empty($createdLocations)) {
            foreach ($hotels as $index => $hotel) {
                // Distribute hotels evenly across locations (cycling through)
                $locationIndex = $index % count($createdLocations);
                $hotel->update(['location_id' => $createdLocations[$locationIndex]->id]);
            }
        }
    }
}
