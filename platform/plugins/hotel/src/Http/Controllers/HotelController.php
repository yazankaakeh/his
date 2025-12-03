<?php

namespace Botble\Hotel\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Hotel\Forms\HotelForm;
use Botble\Hotel\Http\Requests\HotelRequest;
use Botble\Hotel\Models\Hotel;
use Botble\Hotel\Tables\HotelTable;

class HotelController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/hotel::hotel.name'))
            ->add(trans('plugins/hotel::hotel.hotels'), route('hotel.index'));
    }

    public function index(HotelTable $table)
    {
        $this->pageTitle(trans('plugins/hotel::hotel.hotels'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/hotel::hotel.create'));

        return HotelForm::create()->renderForm();
    }

    public function store(HotelRequest $request)
    {
        $form = HotelForm::create();
        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('hotel.index'))
            ->setNextUrl(route('hotel.edit', $form->getModel()->getKey()))
            ->withCreatedSuccessMessage();
    }

    public function edit(Hotel $hotel)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $hotel->name]));

        return HotelForm::createFromModel($hotel)->renderForm();
    }

    public function update(Hotel $hotel, HotelRequest $request)
    {
        HotelForm::createFromModel($hotel)
            ->setRequest($request)
            ->save();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('hotel.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(Hotel $hotel)
    {
        return DeleteResourceAction::make($hotel);
    }
}
