<?php

namespace Tests\Feature\Admin;

use Botble\Hotel\Enums\ServicePriceTypeEnum;
use Botble\Hotel\Models\Booking;
use Botble\Hotel\Models\BookingRoom;
use Botble\Hotel\Models\Customer;
use Botble\Hotel\Models\Room;
use Botble\Hotel\Models\Service;
use Botble\Hotel\Models\Tax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingWithServicesTest extends TestCase
{
    use RefreshDatabase;

    protected Room $room;
    protected Customer $customer;
    protected Tax $tax;
    protected Service $serviceOnce;
    protected Service $servicePerDay;

    protected function setUp(): void
    {
        parent::setUp();

        // Run plugin migrations
        $this->artisan('migrate', ['--path' => 'platform/plugins/hotel/database/migrations', '--realpath' => true]);

        // Create tax
        $this->tax = Tax::factory()->create([
            'title' => 'VAT',
            'percentage' => 10,
            'status' => 'published',
        ]);

        // Create room
        $this->room = Room::factory()->create([
            'name' => 'Deluxe Room',
            'price' => 100,
            'tax_id' => $this->tax->id,
            'status' => 'published',
            'number_of_rooms' => 5,
        ]);

        // Create customer
        $this->customer = Customer::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
        ]);

        // Create services
        $this->serviceOnce = Service::create([
            'name' => 'Airport Pickup',
            'description' => 'One-time airport pickup service',
            'price' => 50,
            'price_type' => ServicePriceTypeEnum::ONCE,
            'status' => 'published',
        ]);

        $this->servicePerDay = Service::create([
            'name' => 'Breakfast',
            'description' => 'Daily breakfast service',
            'price' => 20,
            'price_type' => ServicePriceTypeEnum::PER_DAY,
            'status' => 'published',
        ]);
    }

    /** @test */
    public function booking_can_be_created_without_services()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d');

        $booking = $this->createBooking($this->customer, $startDate, $endDate, 1, []);

        $this->assertDatabaseHas('ht_bookings', [
            'id' => $booking->id,
        ]);

        // No services attached
        $this->assertEquals(0, $booking->services()->count());
    }

    /** @test */
    public function booking_can_include_single_service()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d');

        $booking = $this->createBooking(
            $this->customer,
            $startDate,
            $endDate,
            1,
            [$this->serviceOnce->id]
        );

        // Service should be attached
        $this->assertEquals(1, $booking->services()->count());
        $this->assertTrue($booking->services->contains($this->serviceOnce));

        // Verify service in pivot table
        $this->assertDatabaseHas('ht_booking_services', [
            'booking_id' => $booking->id,
            'service_id' => $this->serviceOnce->id,
        ]);
    }

    /** @test */
    public function booking_can_include_multiple_services()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d');

        $booking = $this->createBooking(
            $this->customer,
            $startDate,
            $endDate,
            1,
            [$this->serviceOnce->id, $this->servicePerDay->id]
        );

        // Both services should be attached
        $this->assertEquals(2, $booking->services()->count());
        $this->assertTrue($booking->services->contains($this->serviceOnce));
        $this->assertTrue($booking->services->contains($this->servicePerDay));
    }

    /** @test */
    public function booking_amount_includes_service_with_once_price_type()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d'); // 2 nights
        $numberOfRooms = 1;

        // Calculate expected amounts
        $roomPrice = $this->room->price * 2 * $numberOfRooms; // 100 * 2 * 1 = 200
        $servicePrice = $this->serviceOnce->price; // 50 (one-time)
        $subTotal = $roomPrice + $servicePrice; // 250
        $taxAmount = $subTotal * ($this->tax->percentage / 100); // 25
        $totalAmount = $subTotal + $taxAmount; // 275

        $booking = $this->createBooking(
            $this->customer,
            $startDate,
            $endDate,
            $numberOfRooms,
            [$this->serviceOnce->id]
        );

        $this->assertEquals($subTotal, $booking->sub_total);
        $this->assertEquals($taxAmount, $booking->tax_amount);
        $this->assertEquals($totalAmount, $booking->amount);
    }

    /** @test */
    public function booking_amount_includes_service_with_per_day_price_type()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d'); // 2 nights
        $numberOfRooms = 1;

        // Calculate expected amounts
        $nights = 2;
        $roomPrice = $this->room->price * $nights * $numberOfRooms; // 100 * 2 * 1 = 200
        $servicePrice = $this->servicePerDay->price * $nights * $numberOfRooms; // 20 * 2 * 1 = 40
        $subTotal = $roomPrice + $servicePrice; // 240
        $taxAmount = $subTotal * ($this->tax->percentage / 100); // 24
        $totalAmount = $subTotal + $taxAmount; // 264

        $booking = $this->createBooking(
            $this->customer,
            $startDate,
            $endDate,
            $numberOfRooms,
            [$this->servicePerDay->id]
        );

        $this->assertEquals($subTotal, $booking->sub_total);
        $this->assertEquals($taxAmount, $booking->tax_amount);
        $this->assertEquals($totalAmount, $booking->amount);
    }

    /** @test */
    public function booking_with_multiple_services_calculates_correctly()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(4)->format('Y-m-d'); // 3 nights
        $numberOfRooms = 2;

        // Calculate expected amounts
        $nights = 3;
        $roomPrice = $this->room->price * $nights * $numberOfRooms; // 100 * 3 * 2 = 600
        $onceServicePrice = $this->serviceOnce->price; // 50 (one-time)
        $perDayServicePrice = $this->servicePerDay->price * $nights * $numberOfRooms; // 20 * 3 * 2 = 120
        $subTotal = $roomPrice + $onceServicePrice + $perDayServicePrice; // 770
        $taxAmount = $subTotal * ($this->tax->percentage / 100); // 77
        $totalAmount = $subTotal + $taxAmount; // 847

        $booking = $this->createBooking(
            $this->customer,
            $startDate,
            $endDate,
            $numberOfRooms,
            [$this->serviceOnce->id, $this->servicePerDay->id]
        );

        $this->assertEquals($subTotal, $booking->sub_total);
        $this->assertEquals($taxAmount, $booking->tax_amount);
        $this->assertEquals($totalAmount, $booking->amount);
        $this->assertEquals(2, $booking->services()->count());
    }

    /** @test */
    public function services_are_deleted_when_booking_is_deleted()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d');

        $booking = $this->createBooking(
            $this->customer,
            $startDate,
            $endDate,
            1,
            [$this->serviceOnce->id, $this->servicePerDay->id]
        );

        $bookingId = $booking->id;

        // Verify services are attached
        $this->assertDatabaseHas('ht_booking_services', [
            'booking_id' => $bookingId,
        ]);

        // Delete the booking
        $booking->delete();

        // Services pivot entries should be removed
        $this->assertDatabaseMissing('ht_booking_services', [
            'booking_id' => $bookingId,
        ]);

        // But the services themselves should still exist
        $this->assertDatabaseHas('ht_services', [
            'id' => $this->serviceOnce->id,
        ]);
    }

    /** @test */
    public function can_retrieve_booking_with_services()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d');

        $booking = $this->createBooking(
            $this->customer,
            $startDate,
            $endDate,
            1,
            [$this->serviceOnce->id, $this->servicePerDay->id]
        );

        // Retrieve booking with services
        $retrievedBooking = Booking::with('services')->find($booking->id);

        $this->assertNotNull($retrievedBooking);
        $this->assertEquals(2, $retrievedBooking->services->count());

        // Check service names
        $serviceNames = $retrievedBooking->services->pluck('name')->toArray();
        $this->assertContains('Airport Pickup', $serviceNames);
        $this->assertContains('Breakfast', $serviceNames);
    }

    /**
     * Helper method to create a booking with services
     */
    protected function createBooking(
        Customer $customer,
        string $startDate,
        string $endDate,
        int $numberOfRooms,
        array $serviceIds = []
    ): Booking {
        $nights = (strtotime($endDate) - strtotime($startDate)) / 86400;
        $roomPrice = $this->room->price * $nights * $numberOfRooms;

        // Calculate service prices
        $servicePrice = 0;
        foreach ($serviceIds as $serviceId) {
            $service = Service::find($serviceId);
            if ($service) {
                if ($service->price_type == ServicePriceTypeEnum::ONCE) {
                    $servicePrice += $service->price;
                } elseif ($service->price_type == ServicePriceTypeEnum::PER_DAY) {
                    $servicePrice += $service->price * $nights * $numberOfRooms;
                }
            }
        }

        $subTotal = $roomPrice + $servicePrice;
        $taxAmount = $subTotal * ($this->tax->percentage / 100);
        $total = $subTotal + $taxAmount;

        $booking = Booking::create([
            'customer_id' => $customer->id,
            'number_of_guests' => 2,
            'number_of_children' => 0,
            'amount' => $total,
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'transaction_id' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(32)),
            'booking_number' => 'BK' . time() . rand(100, 999),
            'status' => 'pending',
        ]);

        // Attach services
        if (!empty($serviceIds)) {
            $booking->services()->attach($serviceIds);
        }

        BookingRoom::create([
            'booking_id' => $booking->id,
            'room_id' => $this->room->id,
            'room_name' => $this->room->name,
            'price' => $roomPrice,
            'number_of_rooms' => $numberOfRooms,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $booking->fresh(['services']);
    }
}
