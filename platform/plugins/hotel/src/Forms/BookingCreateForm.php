<?php

namespace Botble\Hotel\Forms;

use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\FieldOptions\EmailFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\Fields\EmailField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Hotel\Enums\BookingStatusEnum;
use Botble\Hotel\Facades\HotelHelper;
use Botble\Hotel\Http\Requests\CreateBookingRequest;
use Botble\Hotel\Models\Booking;
use Botble\Hotel\Models\Customer;
use Botble\Hotel\Models\Room;
use Botble\Hotel\Models\Service;

class BookingCreateForm extends FormAbstract
{
    public function setup(): void
    {
        $rooms = Room::query()
            ->wherePublished()
            ->pluck('name', 'id')
            ->toArray();

        $customers = Customer::query()
            ->selectRaw("CONCAT(first_name, ' ', last_name, ' (', email, ')') as full_name, id")
            ->pluck('full_name', 'id')
            ->toArray();

        $services = Service::query()
            ->wherePublished()
            ->pluck('name', 'id')
            ->toArray();

        $this
            ->setupModel(new Booking())
            ->setValidatorClass(CreateBookingRequest::class)
            ->setUrl(route('booking.create.store'))
            ->setMethod('POST')
            ->withCustomFields()
            ->columns()
            ->add('room_id', SelectField::class, SelectFieldOption::make()
                ->label(trans('plugins/hotel::booking.room'))
                ->choices($rooms)
                ->searchable()
                ->required()
                ->colspan(2)
                ->toArray()
            )
            ->add('customer_id', SelectField::class, SelectFieldOption::make()
                ->label(trans('plugins/hotel::booking.customer'))
                ->choices(['' => trans('plugins/hotel::booking.select_customer')] + $customers)
                ->searchable()
                ->helperText(trans('plugins/hotel::booking.customer_helper'))
                ->colspan(2)
                ->toArray()
            )
            ->add('start_date', DatePickerField::class, DatePickerFieldOption::make()
                ->label(trans('plugins/hotel::booking.start_date'))
                ->required()
                ->toArray()
            )
            ->add('end_date', DatePickerField::class, DatePickerFieldOption::make()
                ->label(trans('plugins/hotel::booking.end_date'))
                ->required()
                ->toArray()
            )
            ->add('adults', NumberField::class, NumberFieldOption::make()
                ->label(trans('plugins/hotel::booking.adults'))
                ->required()
                ->defaultValue(1)
                ->min(HotelHelper::getMinimumNumberOfGuests())
                ->max(HotelHelper::getMaximumNumberOfGuests())
                ->toArray()
            )
            ->add('children', NumberField::class, NumberFieldOption::make()
                ->label(trans('plugins/hotel::booking.children'))
                ->defaultValue(0)
                ->min(0)
                ->toArray()
            )
            ->add('rooms', NumberField::class, NumberFieldOption::make()
                ->label(trans('plugins/hotel::booking.number_of_rooms'))
                ->required()
                ->defaultValue(1)
                ->min(1)
                ->colspan(2)
                ->toArray()
            )
            ->add('status', SelectField::class, StatusFieldOption::make()
                ->choices(BookingStatusEnum::labels())
                ->required()
                ->defaultValue(BookingStatusEnum::PENDING)
                ->colspan(2)
                ->toArray()
            )
            ->add('services', SelectField::class, SelectFieldOption::make()
                ->label(trans('plugins/hotel::booking.services'))
                ->choices($services)
                ->searchable()
                ->multiple()
                ->colspan(2)
                ->toArray()
            )
            ->add('arrival_time', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/hotel::booking.arrival_time'))
                ->placeholder('e.g., 14:00')
                ->colspan(2)
                ->toArray()
            )
            ->add('requests', TextareaField::class, TextareaFieldOption::make()
                ->label(trans('plugins/hotel::booking.requests'))
                ->rows(3)
                ->colspan(2)
                ->toArray()
            )
            ->addOpenCollapsible('customer_information', 'customer_info', trans('plugins/hotel::booking.customer_information'))
            ->columns()
            ->add('first_name', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/hotel::booking.first_name'))
                ->required()
                ->toArray()
            )
            ->add('last_name', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/hotel::booking.last_name'))
                ->required()
                ->toArray()
            )
            ->add('email', EmailField::class, EmailFieldOption::make()
                ->label(trans('plugins/hotel::booking.email'))
                ->required()
                ->toArray()
            )
            ->add('phone', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/hotel::booking.phone'))
                ->required()
                ->toArray()
            )
            ->add('address', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/hotel::booking.address'))
                ->colspan(2)
                ->toArray()
            )
            ->add('city', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/hotel::booking.city'))
                ->toArray()
            )
            ->add('state', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/hotel::booking.state'))
                ->toArray()
            )
            ->add('zip', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/hotel::booking.zip_code'))
                ->toArray()
            )
            ->add('country', TextField::class, TextFieldOption::make()
                ->label(trans('plugins/hotel::booking.country'))
                ->toArray()
            )
            ->addCloseCollapsible('customer_information', 'customer_info');
    }
}
