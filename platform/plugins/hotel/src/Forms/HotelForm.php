<?php

namespace Botble\Hotel\Forms;

use Botble\Base\Forms\FieldOptions\ContentFieldOption;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Hotel\Http\Requests\HotelRequest;
use Botble\Hotel\Models\Hotel;
use Botble\Hotel\Models\Location;

class HotelForm extends FormAbstract
{
    public function setup(): void
    {
        $locations = Location::query()->pluck('name', 'id')->all();

        $this
            ->setupModel(new Hotel())
            ->setValidatorClass(HotelRequest::class)
            ->withCustomFields()
            ->add('name', TextField::class, NameFieldOption::make()->required()->toArray())
            ->add('description', TextareaField::class, DescriptionFieldOption::make()->toArray())
            ->add('content', EditorField::class, ContentFieldOption::make()->allowedShortcodes()->toArray())
            ->add('address', 'text', [
                'label' => trans('plugins/hotel::hotel.form.address'),
                'attr' => [
                    'placeholder' => trans('plugins/hotel::hotel.form.address_placeholder'),
                    'data-counter' => 255,
                ],
            ])
            ->add('phone', 'text', [
                'label' => trans('plugins/hotel::hotel.form.phone'),
                'attr' => [
                    'placeholder' => trans('plugins/hotel::hotel.form.phone_placeholder'),
                    'data-counter' => 25,
                ],
            ])
            ->add('email', 'email', [
                'label' => trans('plugins/hotel::hotel.form.email'),
                'attr' => [
                    'placeholder' => trans('plugins/hotel::hotel.form.email_placeholder'),
                ],
            ])
            ->add('location_id', 'customSelect', [
                'label' => trans('plugins/hotel::hotel.form.location'),
                'attr' => [
                    'class' => 'form-control select-full',
                ],
                'choices' => ['' => trans('plugins/hotel::hotel.form.select_location')] + $locations,
            ])
            ->add('status', SelectField::class, StatusFieldOption::make()->toArray())
            ->add('image', MediaImageField::class)
            ->setBreakFieldPoint('status');
    }
}
