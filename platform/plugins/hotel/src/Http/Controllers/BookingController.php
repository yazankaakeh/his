<?php

namespace Botble\Hotel\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Hotel\Events\BookingStatusChanged;
use Botble\Hotel\Events\BookingUpdated;
use Botble\Hotel\Forms\BookingForm;
use Botble\Hotel\Forms\BookingCreateForm;
use Botble\Hotel\Http\Requests\CreateBookingRequest;
use Botble\Hotel\Http\Requests\UpdateBookingRequest;
use Botble\Hotel\Models\Booking;
use Botble\Hotel\Models\BookingAddress;
use Botble\Hotel\Models\BookingRoom;
use Botble\Hotel\Models\Room;
use Botble\Hotel\Facades\HotelHelper;
use Botble\Hotel\Tables\BookingTable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BookingController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/hotel::booking.name'), route('booking.index'));
    }

    public function index(BookingTable $table)
    {
        $this->pageTitle(trans('plugins/hotel::booking.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/hotel::booking.create'));

        return BookingCreateForm::create()->renderForm();
    }

    public function store(CreateBookingRequest $request)
    {
        // Validate that either customer_id is provided or customer information is filled
        if (!$request->input('customer_id') && (!$request->filled('first_name') || !$request->filled('last_name') || !$request->filled('email') || !$request->filled('phone'))) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage('Please either select a customer or fill in the customer information (First Name, Last Name, Email, Phone).');
        }

        $room = Room::query()->findOrFail($request->input('room_id'));

        // Parse dates - handle multiple formats
        try {
            $startDate = \Carbon\Carbon::parse($request->input('start_date'));
            $endDate = \Carbon\Carbon::parse($request->input('end_date'));
        } catch (\Exception $e) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage('Invalid date format. Please use a valid date.');
        }
        $numberOfRooms = $request->input('rooms', 1);

        $room->total_price = $room->getRoomTotalPrice($startDate, $endDate, $numberOfRooms);

        $serviceIds = $request->input('services', []);

        $amount = $room->total_price;
        if ($serviceIds) {
            foreach ($serviceIds as $serviceId) {
                $service = \Botble\Hotel\Models\Service::find($serviceId);
                if ($service) {
                    $nights = $startDate->diffInDays($endDate);
                    $amount += $service->price * $nights * $numberOfRooms;
                }
            }
        }

        $taxAmount = $room->tax->percentage * $amount / 100;

        $booking = new Booking();
        $booking->fill($request->input());
        $booking->number_of_guests = $request->input('adults', 1);
        $booking->number_of_children = $request->input('children', 0);
        $booking->amount = $amount + $taxAmount;
        $booking->sub_total = $amount;
        $booking->tax_amount = $taxAmount;
        $booking->transaction_id = Str::upper(Str::random(32));
        $booking->booking_number = Booking::generateUniqueBookingNumber();
        $booking->status = $request->input('status');

        $booking->save();

        if ($serviceIds) {
            $booking->services()->attach($serviceIds);
        }

        BookingRoom::query()->create([
            'room_id' => $room->getKey(),
            'room_name' => $room->name,
            'room_image' => Arr::first($room->images),
            'booking_id' => $booking->getKey(),
            'price' => $room->total_price,
            'currency_id' => $room->currency_id,
            'number_of_rooms' => $numberOfRooms,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]);

        // Create booking address from customer or form data
        $bookingAddress = new BookingAddress();
        $bookingAddress->booking_id = $booking->getKey();

        if ($request->input('customer_id')) {
            // Use customer information
            $customer = \Botble\Hotel\Models\Customer::find($request->input('customer_id'));
            if ($customer) {
                $bookingAddress->first_name = $customer->first_name;
                $bookingAddress->last_name = $customer->last_name;
                $bookingAddress->email = $customer->email;
                $bookingAddress->phone = $customer->phone;
                $bookingAddress->address = $customer->address ?? '';
                $bookingAddress->city = $customer->city ?? '';
                $bookingAddress->state = $customer->state ?? '';
                $bookingAddress->zip = $customer->zip ?? '';
                $bookingAddress->country = $customer->country ?? '';
            }
        } else {
            // Use form data
            $bookingAddress->fill($request->input());
        }

        $bookingAddress->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('booking.index'))
            ->setNextUrl(route('booking.edit', $booking->getKey()))
            ->withCreatedSuccessMessage();
    }

    public function edit(Booking $booking)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $booking->room->room_name]));

        return BookingForm::createFromModel($booking)->renderForm();
    }

    public function update(Booking $booking, UpdateBookingRequest $request)
    {
        $status = $booking->status;

        BookingForm::createFromModel($booking)
            ->setRequest($request)
            ->save();

        BookingUpdated::dispatch($booking);

        if ($booking->status != $status) {
            BookingStatusChanged::dispatch($status, $booking);
        }

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('booking.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(Booking $booking)
    {
        return DeleteResourceAction::make($booking);
    }
}
