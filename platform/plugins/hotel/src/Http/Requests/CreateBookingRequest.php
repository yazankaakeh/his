<?php

namespace Botble\Hotel\Http\Requests;

use Botble\Hotel\Enums\BookingStatusEnum;
use Botble\Hotel\Facades\HotelHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends Request
{
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:ht_rooms,id'],
            'customer_id' => ['nullable', 'exists:ht_customers,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'adults' => [
                'required',
                'integer',
                'min:' . HotelHelper::getMinimumNumberOfGuests(),
                'max:' . HotelHelper::getMaximumNumberOfGuests(),
            ],
            'children' => ['nullable', 'integer', 'min:0'],
            'rooms' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(BookingStatusEnum::values())],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:255'],
            'requests' => ['nullable', 'string', 'max:1000'],
            'arrival_time' => ['nullable', 'string', 'max:20'],
            'services' => ['nullable', 'array'],
            'services.*' => ['exists:ht_services,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'room_id' => trans('plugins/hotel::booking.room'),
            'customer_id' => trans('plugins/hotel::booking.customer'),
            'start_date' => trans('plugins/hotel::booking.start_date'),
            'end_date' => trans('plugins/hotel::booking.end_date'),
            'adults' => trans('plugins/hotel::booking.adults'),
            'children' => trans('plugins/hotel::booking.children'),
            'rooms' => trans('plugins/hotel::booking.number_of_rooms'),
            'status' => trans('core/base::tables.status'),
            'first_name' => trans('plugins/hotel::booking.first_name'),
            'last_name' => trans('plugins/hotel::booking.last_name'),
            'email' => trans('plugins/hotel::booking.email'),
            'phone' => trans('plugins/hotel::booking.phone'),
        ];
    }
}
