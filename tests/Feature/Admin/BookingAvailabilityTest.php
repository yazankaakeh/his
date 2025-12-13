<?php

namespace Tests\Feature\Admin;

use Botble\Hotel\Models\Booking;
use Botble\Hotel\Models\BookingRoom;
use Botble\Hotel\Models\Customer;
use Botble\Hotel\Models\Room;
use Botble\Hotel\Models\Tax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected Room $room;
    protected Customer $customer;
    protected Tax $tax;

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

        // Create room with limited capacity
        $this->room = Room::factory()->create([
            'name' => 'Deluxe Room',
            'price' => 100,
            'tax_id' => $this->tax->id,
            'status' => 'published',
            'number_of_rooms' => 2, // Only 2 rooms available
        ]);

        // Create customer
        $this->customer = Customer::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
        ]);
    }

    /** @test */
    public function room_can_be_booked_when_available()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d');

        $booking = $this->createBooking($this->customer, $startDate, $endDate, 1);

        $this->assertDatabaseHas('ht_bookings', [
            'id' => $booking->id,
            'customer_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('ht_booking_rooms', [
            'booking_id' => $booking->id,
            'room_id' => $this->room->id,
            'number_of_rooms' => 1,
        ]);
    }

    /** @test */
    public function multiple_bookings_can_be_made_within_room_capacity()
    {
        $customer2 = Customer::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
        ]);

        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d');

        // First booking - 1 room
        $booking1 = $this->createBooking($this->customer, $startDate, $endDate, 1);

        // Second booking - 1 more room (total 2 rooms, which is the capacity)
        $booking2 = $this->createBooking($customer2, $startDate, $endDate, 1);

        // Both bookings should exist
        $this->assertEquals(2, Booking::count());
        $this->assertNotNull($booking1);
        $this->assertNotNull($booking2);
    }

    /** @test */
    public function can_check_available_rooms_for_date_range()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d');

        // Create a booking for 1 room
        $this->createBooking($this->customer, $startDate, $endDate, 1);

        // Check booked rooms in the date range
        $bookedRooms = BookingRoom::query()
            ->active()
            ->inRange($startDate, $endDate)
            ->where('room_id', $this->room->id)
            ->sum('number_of_rooms');

        $this->assertEquals(1, $bookedRooms);

        // Available rooms should be 1 (total 2 - booked 1)
        $availableRooms = $this->room->number_of_rooms - $bookedRooms;
        $this->assertEquals(1, $availableRooms);
    }

    /** @test */
    public function all_rooms_are_booked_when_capacity_is_reached()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d');

        // Create a booking for all 2 rooms
        $this->createBooking($this->customer, $startDate, $endDate, 2);

        // Check booked rooms
        $bookedRooms = BookingRoom::query()
            ->active()
            ->inRange($startDate, $endDate)
            ->where('room_id', $this->room->id)
            ->sum('number_of_rooms');

        $this->assertEquals(2, $bookedRooms);

        // Available rooms should be 0
        $availableRooms = $this->room->number_of_rooms - $bookedRooms;
        $this->assertEquals(0, $availableRooms);
    }

    /** @test */
    public function booking_succeeds_for_non_overlapping_dates()
    {
        // First booking: Days 1-3
        $booking1 = $this->createBooking(
            $this->customer,
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(3)->format('Y-m-d'),
            2
        );

        // Second booking: Days 4-6 (no overlap)
        $customer2 = Customer::factory()->create(['email' => 'jane@example.com']);
        $booking2 = $this->createBooking(
            $customer2,
            now()->addDays(4)->format('Y-m-d'),
            now()->addDays(6)->format('Y-m-d'),
            2
        );

        // Both bookings should exist
        $this->assertEquals(2, Booking::count());
        $this->assertNotNull($booking1);
        $this->assertNotNull($booking2);
    }

    /** @test */
    public function cancelled_booking_is_excluded_from_availability_check()
    {
        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d');

        // Create a booking for all rooms
        $booking = $this->createBooking($this->customer, $startDate, $endDate, 2);

        // Cancel the booking
        $booking->update(['status' => 'cancelled']);

        // Check active booked rooms (should exclude cancelled)
        $bookedRooms = BookingRoom::query()
            ->active()
            ->inRange($startDate, $endDate)
            ->where('room_id', $this->room->id)
            ->sum('number_of_rooms');

        // Should be 0 because cancelled bookings are not counted
        $this->assertEquals(0, $bookedRooms);

        // All rooms should be available
        $availableRooms = $this->room->number_of_rooms - $bookedRooms;
        $this->assertEquals(2, $availableRooms);
    }

    /** @test */
    public function same_day_checkout_and_checkin_does_not_conflict()
    {
        // First booking: Days 1-3 (checkout on day 3)
        $booking1 = $this->createBooking(
            $this->customer,
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(3)->format('Y-m-d'),
            2
        );

        // Second booking: Start on day 3 (same day as first booking's checkout)
        $customer2 = Customer::factory()->create(['email' => 'jane@example.com']);

        $checkoutDate = now()->addDays(3)->format('Y-m-d');
        $bookedRooms = BookingRoom::query()
            ->active()
            ->inRange($checkoutDate, now()->addDays(5)->format('Y-m-d'))
            ->where('room_id', $this->room->id)
            ->sum('number_of_rooms');

        // The inRange query uses end_date > start_date
        // So checkout day should not conflict with new booking's checkin day
        $this->assertEquals(0, $bookedRooms, 'Checkout date should not conflict with new checkin');
    }

    /**
     * Helper method to create a booking with booking room
     */
    protected function createBooking(Customer $customer, string $startDate, string $endDate, int $numberOfRooms): Booking
    {
        $nights = (strtotime($endDate) - strtotime($startDate)) / 86400;
        $subTotal = $this->room->price * $nights;
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

        BookingRoom::create([
            'booking_id' => $booking->id,
            'room_id' => $this->room->id,
            'room_name' => $this->room->name,
            'price' => $this->room->price,
            'number_of_rooms' => $numberOfRooms,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $booking;
    }
}
