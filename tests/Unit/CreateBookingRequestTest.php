<?php

namespace Tests\Unit;

use Botble\Hotel\Enums\BookingStatusEnum;
use Botble\Hotel\Http\Requests\CreateBookingRequest;
use Botble\Hotel\Models\Customer;
use Botble\Hotel\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateBookingRequestTest extends TestCase
{
    use RefreshDatabase;

    protected CreateBookingRequest $request;
    protected Room $room;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new CreateBookingRequest();

        // Create test room and customer
        $this->room = Room::factory()->create();
        $this->customer = Customer::factory()->create();
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $validator = Validator::make([], $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('room_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('start_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('end_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('adults', $validator->errors()->toArray());
        $this->assertArrayHasKey('rooms', $validator->errors()->toArray());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_room_id_exists()
    {
        $data = [
            'room_id' => 99999,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'rooms' => 1,
            'status' => BookingStatusEnum::PENDING,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('room_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_customer_id_exists_when_provided()
    {
        $data = [
            'room_id' => $this->room->id,
            'customer_id' => 99999,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'rooms' => 1,
            'status' => BookingStatusEnum::PENDING,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('customer_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_allows_nullable_customer_id()
    {
        $data = [
            'room_id' => $this->room->id,
            'customer_id' => null,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'rooms' => 1,
            'status' => BookingStatusEnum::PENDING,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_validates_date_format()
    {
        $data = [
            'room_id' => $this->room->id,
            'start_date' => 'invalid-date',
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'rooms' => 1,
            'status' => BookingStatusEnum::PENDING,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('start_date', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_end_date_after_or_equal_start_date()
    {
        $data = [
            'room_id' => $this->room->id,
            'start_date' => now()->addDays(3)->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'adults' => 1,
            'rooms' => 1,
            'status' => BookingStatusEnum::PENDING,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('end_date', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_adults_minimum()
    {
        $data = [
            'room_id' => $this->room->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 0,
            'rooms' => 1,
            'status' => BookingStatusEnum::PENDING,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('adults', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_children_minimum()
    {
        $data = [
            'room_id' => $this->room->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'children' => -1,
            'rooms' => 1,
            'status' => BookingStatusEnum::PENDING,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('children', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_rooms_minimum()
    {
        $data = [
            'room_id' => $this->room->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'rooms' => 0,
            'status' => BookingStatusEnum::PENDING,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('rooms', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_status_is_valid_enum_value()
    {
        $data = [
            'room_id' => $this->room->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'rooms' => 1,
            'status' => 'invalid-status',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_email_format_when_provided()
    {
        $data = [
            'room_id' => $this->room->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'rooms' => 1,
            'status' => BookingStatusEnum::PENDING,
            'email' => 'invalid-email',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_string_max_lengths()
    {
        $data = [
            'room_id' => $this->room->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'rooms' => 1,
            'status' => BookingStatusEnum::PENDING,
            'first_name' => str_repeat('a', 256), // Max is 255
            'phone' => str_repeat('1', 21), // Max is 20
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('first_name', $validator->errors()->toArray());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_services_array()
    {
        $data = [
            'room_id' => $this->room->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 1,
            'rooms' => 1,
            'status' => BookingStatusEnum::PENDING,
            'services' => 'not-an-array',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('services', $validator->errors()->toArray());
    }

    /** @test */
    public function it_passes_validation_with_valid_data()
    {
        $data = [
            'room_id' => $this->room->id,
            'customer_id' => $this->customer->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'adults' => 2,
            'children' => 1,
            'rooms' => 1,
            'status' => BookingStatusEnum::PENDING,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'zip' => '10001',
            'country' => 'USA',
            'requests' => 'Late check-in please',
            'arrival_time' => '14:00',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->fails());
    }
}
